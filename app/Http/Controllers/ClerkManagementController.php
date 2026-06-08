<?php
// app/Http/Controllers/ClerkManagementController.php

namespace App\Http\Controllers;

use App\Models\ClerkManagement;
use App\Models\User;
use App\Models\PdfUpload;
use App\Models\PdfPage;
use App\Models\Category;
use App\Models\County;
use App\Models\Marriage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClerkManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all data clerks (role = data_clerk) - FIXED: Properly filter by role
        $allDataClerks = User::where('role_id', 4)
            // Exclude those already assigned in clerk_management
            ->whereNotIn('id', function($query) {
                $query->select('data_clerk_id')->from('clerk_management');
            })
            ->get();
        
        // Get clerk IDs that are already assigned to ANY teller
        $assignedClerkIds = ClerkManagement::pluck('data_clerk_id')->toArray();
        
        // Filter: Only show clerks NOT assigned to any teller
        $availableDataClerks = $allDataClerks->filter(function($clerk) use ($assignedClerkIds) {
            return !in_array($clerk->id, $assignedClerkIds);
        })->values();
        
        // Get current assignments for this marriage teller with eager loading
        $assignedClerks = ClerkManagement::with('dataClerk')
            ->where('marriage_teller_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all assigned PDF pages (for tab 3)
        $allAssignedPages = PdfPage::where('status', 'assigned')
            ->whereNotNull('assigned_to')
            ->with([
                'pdfUpload.uploader',
                'pdfUpload.county',
                'pdfUpload.marriageType',
                'assignedUser'
            ])
            ->latest()
            ->limit(1000)
            ->get();
        
        // Get marriage types for filters
        $marriageTypes = Category::where('type', 'marriage_type')
            ->orderBy('name')
            ->get(['id', 'name']);

        $years = PdfUpload::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            $months[$monthNum] = date('F', mktime(0, 0, 0, $i, 1));
        }
        
        $counties = County::select('name', 'county_code')
            ->whereNotNull('name')
            ->whereNotNull('county_code')
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();
        
            // Get all pending PDF pages with their PDF upload names
        $pendingPages = PdfPage::where('status', 'pending')
            ->with(['pdfUpload:id,name']) // eager load pdf_upload with only id and name
            ->get()
            ->groupBy('pdf_upload_id');
                
        return view('clerk-management.index', compact(
            'availableDataClerks', 
            'assignedClerks', 
            'allAssignedPages',
            'marriageTypes',
            'years',
            'months',
            'counties',
            'pendingPages'
        ));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'data_clerk_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500'
        ]);
        
        $user = Auth::user();
        
        // Check if clerk is already assigned to ANY teller
        $existingAssignment = ClerkManagement::where('data_clerk_id', $request->data_clerk_id)
            ->first();
        
        if ($existingAssignment) {
            return response()->json([
                'success' => false,
                'message' => 'This clerk is already assigned to a teller.'
            ], 422);
        }
        
        // Create clerk assignment
        $clerkManagement = ClerkManagement::create([
            'data_clerk_id' => $request->data_clerk_id,
            'marriage_teller_id' => $user->id,
            'filled_count' => 0,
            'target_count' => 0,
            'notes' => $request->notes,
            'status' => 'active',
            'rating' => null
        ]);
        
        // Load the relationship for the response
        $clerkManagement->load('dataClerk');
        
        return response()->json([
            'success' => true,
            'message' => 'Clerk assigned successfully.',
            'clerk' => $clerkManagement
        ]);
    }
    
    public function update(Request $request, ClerkManagement $clerkManagement)
    {
        // Verify the marriage teller owns this assignment
        if ($clerkManagement->marriage_teller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        $request->validate([
            'rating' => 'nullable|integer|between:1,5',
            'notes' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive'
        ]);
        
        $updateData = [];
        
        if ($request->has('rating')) {
            $updateData['rating'] = $request->rating;
        }
        
        if ($request->has('notes')) {
            $updateData['notes'] = $request->notes;
        }
        
        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }
        
        $clerkManagement->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Assignment updated successfully.',
            'clerk' => $clerkManagement->fresh('dataClerk')
        ]);
    }
    
    public function destroy(ClerkManagement $clerkManagement)
    {
        // Verify the marriage teller owns this assignment
        if ($clerkManagement->marriage_teller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        // Check if clerk has any pages assigned
        $hasAssignedPages = PdfPage::where('assigned_to', $clerkManagement->data_clerk_id)
            ->exists();
        
        if ($hasAssignedPages) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove clerk because they have assigned pages.'
            ], 422);
        }
        
        $clerkManagement->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Clerk removed successfully.'
        ]);
    }
    
    public function show(ClerkManagement $clerkManagement)
    {
        // Verify the marriage teller owns this assignment
        if ($clerkManagement->marriage_teller_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Get all PDF pages assigned to this clerk
        $pdfPages = PdfPage::where('assigned_to', $clerkManagement->data_clerk_id)
            ->with(['pdfUpload.uploader', 'pdfUpload.county', 'pdfUpload.marriageType'])
            ->latest()
            ->get();
        
        // Get data for the partial table
        $years = PdfUpload::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $counties = County::select('county_code', 'name')
            ->whereNotNull('county_code')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get()
            ->unique('county_code')
            ->values();
        
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            $months[$monthNum] = date('F', mktime(0, 0, 0, $i, 1));
        }
        
        $marriageTypes = Category::where('type', 'marriage_type')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $statuses = ['uploaded', 'assigned', 'completed', 'published'];

        return view('clerk-management.show', compact(
            'clerkManagement',
            'pdfPages',
            'years',
            'counties',
            'months',
            'marriageTypes',
            'statuses'
        ));
    }

    public function getAvailablePdfs(Request $request)
    {
        $query = PdfUpload::where('status', 'uploaded')
            ->whereDoesntHave('pages', function($query) {
                $query->whereNotNull('assigned_to');
            })
            ->with(['marriageType', 'uploader']);
        
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        
        if ($request->filled('marriage_type_id')) {
            $query->where('marriage_type_id', $request->marriage_type_id);
        }
        
        if ($request->filled('county_code')) {
            $query->where('county_code', $request->county_code);
        }
        
        $pdfs = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        foreach ($pdfs as $pdf) {
            $pdf->month_name = $pdf->getMonthNameAttribute();
        }
        
        return response()->json($pdfs);
    }


public function assignPdfPages(Request $request)
{
    $request->validate([
        'data_clerk_id' => 'required|exists:users,id',
        'pdf_upload_id' => 'required|exists:pdf_uploads,id',
        'page_numbers' => 'required|array',
        'page_numbers.*' => 'integer'
    ]);
    
    $user = Auth::user();
    $pdfUpload = PdfUpload::findOrFail($request->pdf_upload_id);
    
    // Verify clerk is assigned to this teller
    $clerkAssignment = ClerkManagement::where('marriage_teller_id', $user->id)
        ->where('data_clerk_id', $request->data_clerk_id)
        ->where('status', 'active')
        ->first();
    
    if (!$clerkAssignment) {
        return response()->json(['message' => 'Clerk not assigned or inactive'], 422);
    }
    
    DB::beginTransaction();
    
    try {
        $assignedCount = 0;
        
        foreach ($request->page_numbers as $pageNumber) {
            $page = PdfPage::where('pdf_upload_id', $request->pdf_upload_id)
                ->where('page_number', $pageNumber)
                ->whereNull('assigned_to')
                ->first();
            
            if ($page) {
                $page->assigned_to = $request->data_clerk_id;
                $page->assigned_by = $user->id;  // Track who assigned it
                $page->assigned_at = now();
                $page->status = 'assigned';  // Set to assigned, not pending
                $page->save();
                $assignedCount++;
            }
        }
        
        // Update clerk counts
        $clerkAssignment->updateCounts();
        
        // Update PDF status
        $pdfUpload->updateStatusFromPages();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => "{$assignedCount} pages assigned successfully"
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed: ' . $e->getMessage()], 500);
    }
}

/**
 * Get pending PDFs for assignment (used by assign records modal)
 * This method does NOT require a clerk parameter
 */
public function getPendingPdfs(Request $request)
{
    try {
        // Build query for PDF Uploads that have pending pages
        $query = PdfUpload::whereHas('pages', function($q) {
            $q->where('status', 'pending')
              ->whereNull('assigned_to');
        })->with(['marriageType', 'uploader', 'county']);
        
        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        
        if ($request->filled('county_code')) {
            $query->where('county_code', $request->county_code);
        }
        
        if ($request->filled('marriage_type_id')) {
            $query->where('marriage_type_id', $request->marriage_type_id);
        }
        
        // Get PDF Uploads with their pending pages count
        $pdfUploads = $query->get();
        
        $pdfUploads->each(function($pdf) {
            // Get pending pages count for this PDF
            $pdf->pending_pages_count = PdfPage::where('pdf_upload_id', $pdf->id)
                ->where('status', 'pending')
                ->whereNull('assigned_to')
                ->count();
            
            $pdf->month_name = $pdf->getMonthNameAttribute();
        });
        
        // Filter out PDFs with no pending pages
        $pdfUploads = $pdfUploads->filter(function($pdf) {
            return $pdf->pending_pages_count > 0;
        })->values();
        
        // Get filter options based on pending records
        $availableYears = PdfUpload::whereHas('pages', function($q) {
            $q->where('status', 'pending')->whereNull('assigned_to');
        })->distinct()->pluck('year')->sort()->values();
        
        $availableMonths = PdfUpload::whereHas('pages', function($q) {
            $q->where('status', 'pending')->whereNull('assigned_to');
        })->distinct()->pluck('month')->sort()->values();
        
        $availableCounties = PdfUpload::whereHas('pages', function($q) {
            $q->where('status', 'pending')->whereNull('assigned_to');
        })->whereNotNull('county_code')
          ->distinct()
          ->pluck('county_code')
          ->sort()
          ->values();
        
        $availableMarriageTypes = Category::where('type', 'marriage_type')
            ->whereHas('pdfUploads', function($q) {
                $q->whereHas('pages', function($sq) {
                    $sq->where('status', 'pending')->whereNull('assigned_to');
                });
            })
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'pdf_uploads' => $pdfUploads,
            'filters' => [
                'years' => $availableYears,
                'months' => $availableMonths,
                'counties' => $availableCounties,
                'marriage_types' => $availableMarriageTypes
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error loading pending PDFs: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to load available PDFs: ' . $e->getMessage(),
            'pdf_uploads' => [],
            'filters' => [
                'years' => [],
                'months' => [],
                'counties' => [],
                'marriage_types' => []
            ]
        ], 500);
    }
}

// app/Http/Controllers/ClerkManagementController.php

public function assignMultiplePdfs(Request $request)
{
    $request->validate([
        'clerk_management_id' => 'required|exists:clerk_management,id',
        'pdf_upload_ids' => 'required|array',
        'pdf_upload_ids.*' => 'exists:pdf_uploads,id'
    ]);
    
    $clerkManagement = ClerkManagement::with('dataClerk')->findOrFail($request->clerk_management_id);
    
    if ($clerkManagement->marriage_teller_id !== Auth::id()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized action.'
        ], 403);
    }
    
    DB::beginTransaction();
    
    try {
        $assignedCount = 0;
        
        foreach ($request->pdf_upload_ids as $pdfUploadId) {
            // Get all pending pages for this PDF upload
            $pendingPages = PdfPage::where('pdf_upload_id', $pdfUploadId)
                ->where('status', 'pending')
                ->whereNull('assigned_to')
                ->get();
            
            foreach ($pendingPages as $page) {
                // Update the page - status becomes 'assigned' when assigned to clerk
                $page->assigned_to = $clerkManagement->data_clerk_id;
                $page->assigned_by = auth()->id();  // Track who assigned it
                $page->assigned_at = now();
                $page->status = 'assigned';  // Important: Set status to 'assigned'
                $page->save();
                $assignedCount++;
            }
            
            // Update PDF status
            try {
                $pdfUpload = PdfUpload::find($pdfUploadId);
                if ($pdfUpload) {
                    $pdfUpload->updateStatusFromPages();
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to update PDF status: ' . $e->getMessage(), [
                    'pdf_upload_id' => $pdfUploadId
                ]);
            }
        }
        
        // Update the clerk management counts
        $clerkManagement->updateCounts();
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => "Successfully assigned {$assignedCount} pages to {$clerkManagement->dataClerk->name}",
            'assigned_count' => $assignedCount,
            'updated_clerk' => $clerkManagement->fresh('dataClerk')
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to assign records: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to assign records: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Get pending PDFs for a specific clerk (AJAX endpoint)
     */
    public function getPendingPdfsForClerk(ClerkManagement $clerkManagement, Request $request)
    {
        if ($clerkManagement->marriage_teller_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $query = PdfUpload::whereHas('pages', function($q) {
            $q->where('status', 'pending')
              ->whereNull('assigned_to');
        })->with(['marriageType', 'uploader', 'county']);
        
        // Apply filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        
        if ($request->filled('county_code')) {
            $query->where('county_code', $request->county_code);
        }
        
        if ($request->filled('marriage_type_id')) {
            $query->where('marriage_type_id', $request->marriage_type_id);
        }
        
        $pdfUploads = $query->get();
        
        $pdfUploads->each(function($pdf) {
            $pdf->pending_pages_count = PdfPage::where('pdf_upload_id', $pdf->id)
                ->where('status', 'pending')
                ->whereNull('assigned_to')
                ->count();
            $pdf->month_name = $pdf->getMonthNameAttribute();
        });
        
        $pdfUploads = $pdfUploads->filter(function($pdf) {
            return $pdf->pending_pages_count > 0;
        })->values();
        
        // Get filter options
        $availableYears = PdfUpload::whereHas('pages', function($q) {
            $q->where('status', 'pending')->whereNull('assigned_to');
        })->distinct()->pluck('year')->sort()->values();
        
        $availableMonths = PdfUpload::whereHas('pages', function($q) {
            $q->where('status', 'pending')->whereNull('assigned_to');
        })->distinct()->pluck('month')->sort()->values();
        
        $availableCounties = PdfUpload::whereHas('pages', function($q) {
            $q->where('status', 'pending')->whereNull('assigned_to');
        })->whereNotNull('county_code')
          ->distinct()
          ->pluck('county_code')
          ->sort()
          ->values();
        
        $availableMarriageTypes = Category::where('type', 'marriage_type')
            ->whereHas('pdfUploads', function($q) {
                $q->whereHas('pages', function($sq) {
                    $sq->where('status', 'pending')->whereNull('assigned_to');
                });
            })
            ->get(['id', 'name']);
        
        return response()->json([
            'pdf_uploads' => $pdfUploads,
            'filters' => [
                'years' => $availableYears,
                'months' => $availableMonths,
                'counties' => $availableCounties,
                'marriage_types' => $availableMarriageTypes
            ]
        ]);
    }
    
    /**
     * Show progress for a specific clerk
     */
    public function showProgress(ClerkManagement $clerkManagement)
    {
        if ($clerkManagement->marriage_teller_id !== Auth::id()) {
            abort(403);
        }
        
        $pdfPages = PdfPage::where('assigned_to', $clerkManagement->data_clerk_id)
            ->with(['pdfUpload.uploader', 'pdfUpload.county', 'pdfUpload.marriageType'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('clerk-management.progress', compact('clerkManagement', 'pdfPages'));
    }
    
    /**
     * Assign PDF to clerk (existing route method)
     */
    public function assignPdf(PdfUpload $pdfUpload)
    {
        // This method is called when viewing the assign PDF page
        // We'll use it to return JSON for pending PDFs when requested via AJAX
        if (request()->ajax()) {
            return $this->getPendingPdfs(request());
        }
        
        // Non-AJAX request - show the assign PDF view
        $assignedClerks = ClerkManagement::with('dataClerk')
            ->where('marriage_teller_id', auth()->id())
            ->where('status', 'active')
            ->get();
        
        return view('clerk-management.assign-pdf', compact('pdfUpload', 'assignedClerks'));
    }
    
    public function storePdfAssignment(Request $request, PdfUpload $pdfUpload)
    {
        $request->validate([
            'data_clerk_id' => 'required|exists:users,id',
            'page_numbers' => 'required|array',
            'page_numbers.*' => 'integer'
        ]);
        
        $user = Auth::user();
        
        // Verify clerk is assigned to this teller
        $clerkAssignment = ClerkManagement::where('marriage_teller_id', $user->id)
            ->where('data_clerk_id', $request->data_clerk_id)
            ->where('status', 'active')
            ->first();
        
        if (!$clerkAssignment) {
            return redirect()->back()->with('error', 'Clerk not assigned or inactive');
        }
        
        DB::beginTransaction();
        
        try {
            $assignedCount = 0;
            
            foreach ($request->page_numbers as $pageNumber) {
                $page = PdfPage::where('pdf_upload_id', $pdfUpload->id)
                    ->where('page_number', $pageNumber)
                    ->whereNull('assigned_to')
                    ->first();
                
                if ($page) {
                    $page->assigned_to = $request->data_clerk_id;
                    $page->completed_by = $user->id;
                    $page->assigned_at = now();
                    $page->status = 'assigned';
                    $page->save();
                    $assignedCount++;
                }
            }
            
            // Update clerk counts
            $clerkAssignment->updateCounts();
            
            // Update PDF status
            $pdfUpload->updateStatusFromPages();
            
            DB::commit();
            
            return redirect()->route('clerk-management.index')
                ->with('success', "{$assignedCount} pages assigned successfully");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to assign pages: ' . $e->getMessage());
        }
    }
}
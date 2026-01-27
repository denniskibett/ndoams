<?php

namespace App\Http\Controllers;

use App\Models\ClerkManagement;
use App\Models\User;
use App\Models\PdfUpload;
use App\Models\PdfPage;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClerkManagementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get all data clerks for assignment
        $dataClerks = User::whereHas('role', function($query) {
            $query->where('name', 'data_clerk');
        })->get();

        // Get current assignments for this marriage teller
        $assignments = ClerkManagement::where('marriage_teller_id', $user->id)
            ->with('dataClerk')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('clerk-management.index', compact('dataClerks', 'assignments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'data_clerk_id' => 'required|exists:users,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|between:1,12',
            'target_count' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        // Check if assignment already exists
        $existingAssignment = ClerkManagement::where('data_clerk_id', $request->data_clerk_id)
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->first();

        if ($existingAssignment) {
            return redirect()->back()->with('error', 'This clerk already has an assignment for the selected period.');
        }

        ClerkManagement::create([
            'data_clerk_id' => $request->data_clerk_id,
            'marriage_teller_id' => Auth::id(),
            'year' => $request->year,
            'month' => $request->month,
            'target_count' => $request->target_count,
            'notes' => $request->notes,
            'status' => 'active'
        ]);

        return redirect()->route('clerk-management.index')->with('success', 'Clerk assignment created successfully.');
    }

    public function update(Request $request, ClerkManagement $clerkManagement)
    {
        // Verify the marriage teller owns this assignment
        if ($clerkManagement->marriage_teller_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'target_count' => 'required|integer|min:1',
            'status' => 'required|in:active,completed,locked',
            'notes' => 'nullable|string|max:500'
        ]);

        $clerkManagement->update([
            'target_count' => $request->target_count,
            'status' => $request->status,
            'notes' => $request->notes
        ]);

        return redirect()->route('clerk-management.index')->with('success', 'Assignment updated successfully.');
    }

    public function showProgress(ClerkManagement $clerkManagement)
    {
        // Verify the marriage teller owns this assignment
        if ($clerkManagement->marriage_teller_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $marriages = $clerkManagement->marriages()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('clerk-management.progress', compact('clerkManagement', 'marriages'));
    }

    // Add these methods to ClerkManagementController

    public function assignPdf(PdfUpload $pdfUpload)
    {
        $dataClerks = User::dataClerks()->get();
        $availablePages = $pdfUpload->pages()->where('status', 'pending')->get();
        
        return view('clerk-management.assign-pdf', compact('pdfUpload', 'dataClerks', 'availablePages'));
    }

    public function storePdfAssignment(Request $request, PdfUpload $pdfUpload)
    {
        $request->validate([
            'data_clerk_id' => 'required|exists:users,id',
            'page_numbers' => 'required|array',
            'page_numbers.*' => 'integer|min:1|max:' . $pdfUpload->total_pages,
            'target_count' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        // Find or create clerk management record
        $clerkManagement = ClerkManagement::firstOrCreate(
            [
                'data_clerk_id' => $request->data_clerk_id,
                'year' => $pdfUpload->year,
                'month' => $pdfUpload->month
            ],
            [
                'marriage_teller_id' => Auth::id(),
                'target_count' => $request->target_count,
                'notes' => $request->notes,
                'pdf_upload_id' => $pdfUpload->id,
                'assignment_type' => 'pdf_bulk'
            ]
        );

        // Update assigned pages
        $assignedPages = array_merge(
            $clerkManagement->assigned_pages ?? [],
            $request->page_numbers
        );
        $clerkManagement->update(['assigned_pages' => array_unique($assignedPages)]);

        // Assign pages to clerk in PdfService
        $pdfService = app(PdfService::class);
        $pdfService->assignPages(
            $pdfUpload,
            $request->page_numbers,
            $request->data_clerk_id,
            Auth::id()
        );

        return redirect()->route('clerk-management.index')
            ->with('success', 'PDF pages assigned to clerk successfully');
    }
}
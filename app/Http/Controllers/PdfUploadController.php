<?php
namespace App\Http\Controllers;

use App\Models\PdfUpload;
use App\Models\PdfPage;
use App\Models\County;
use App\Models\User;
use App\Models\Marriage;
use App\Models\Category;
use App\Models\ClerkManagement;
use App\Services\MarriageCreationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;
use Imagick;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\SystemHelper;

class PdfUploadController extends Controller
{
    protected $marriageService;

    public function __construct(MarriageCreationService $marriageService)
    {
        $this->marriageService = $marriageService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $roleName = $user->role->name ?? 'user';
        
        // ===== BUILD ROLE-BASED QUERY FOR PDF PAGES =====
        $query = PdfPage::with(['pdfUpload.uploader', 'pdfUpload.county', 'pdfUpload.marriageType']);
        
        switch ($roleName) {
            case 'data_clerk':
                // Data clerk sees ONLY pages assigned to them that are NOT completed
                // They work on: pending, assigned, in_progress
                $query->where('assigned_to', $user->id)
                    ->whereIn('status', ['pending', 'assigned', 'in_progress']);
                break;
                
            case 'marriage_teller':
                // Marriage teller sees pages that need review (review_needed)
                // Get clerk IDs assigned to this teller
                $clerkIds = ClerkManagement::where('marriage_teller_id', $user->id)
                    ->pluck('data_clerk_id');

                // Show ONLY their clerks' pages
                $query->whereIn('assigned_to', $clerkIds)
                    ->where('status', 'review_needed');
                break;
                
            case 'marriage_registrar':
                // Marriage registrar sees COMPLETED pages (ready for final approval)
                // Note: 'under_review' is not a standard status, using 'completed' instead
                $query->where('status', 'completed');
                break;
                
            case 'ag':
            case 'attorney_general':
                // Attorney General sees ONLY PUBLISHED pages
                $query->where('status', 'completed')
                    ->whereHas('pdfUpload', function($q) {
                        $q->where('status', 'published');
                    });
                break;
                
            case 'admin':
                // Admin sees everything - no filter
                break;
                
            default:
                // Regular users see only published
                $query->where('status', 'completed')
                    ->whereHas('pdfUpload', function($q) {
                        $q->where('status', 'published');
                    });
                break;
        }
        
        // Determine route for conditional limiting
        $currentRoute = $request->route()->getName();
        
        // Cap PDF pages if on pdf-uploads index and user is not admin
        if ($currentRoute === 'pdf-uploads.index' && $roleName == 'admin') {
            $pdfPages = $query->orderBy('created_at', 'desc')->limit(2000)->get();
        } else {
            // For dashboard or admin, no limit
            $pdfPages = $query->orderBy('created_at', 'desc')->get();
        }
        
        // Get years for filter (from all PDFs - can be role-filtered if needed)
        $years = PdfUpload::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Get counties for filter
        $counties = County::select('county_code', 'name')
            ->whereNotNull('county_code')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get()
            ->unique('county_code')
            ->values();

        // Months array for filter
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            $months[$monthNum] = date('F', mktime(0, 0, 0, $i, 1));
        }

        // ONLY pdf_pages statuses (not mixing with pdf_uploads)
        $statuses = [
            'pending',
            'assigned',
            'in_progress',
            'completed',
            'review_needed',
            'skipped'
        ];
        
        $cardData = $this->getOverviewCardData();

        $marriageTypes = Category::where('type', 'marriage_type')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        // Pass role to view for debugging (optional)
        $userRole = $roleName;
        
        return view('pdf-uploads.index', compact(
            'pdfPages',
            'years', 
            'counties', 
            'statuses',
            'months',
            'cardData', 
            'marriageTypes',
            'userRole'
        ));
    }


    public function reviewMarriage(Request $request, Marriage $marriage)
    {
        $user = Auth::user();
        $userRole = $user->role->name ?? '';
        
        // Allow marriage_teller and marriage_registrar
        if (!in_array($userRole, ['marriage_teller', 'marriage_registrar', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        
        $action = $request->input('action');
        $notes = $request->input('notes', '');
        
        if (!in_array($action, ['approve', 'reject'])) {
            return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
        }
        
        DB::beginTransaction();
        
        try {
            // Update marriage basic data
            $marriageData = $request->only([
                'certificate_serial', 'marriage_date', 'reg_date', 'venue', 
                'sub_county', 'ward_id'
            ]);
            
            if (!empty($marriageData)) {
                // Format data
                if (isset($marriageData['certificate_serial'])) {
                    $marriageData['certificate_serial'] = strtoupper($marriageData['certificate_serial']);
                }
                if (isset($marriageData['venue'])) {
                    $marriageData['venue'] = strtoupper($marriageData['venue']);
                }
                if (isset($marriageData['sub_county'])) {
                    $marriageData['sub_county'] = strtoupper($marriageData['sub_county']);
                }
                
                $marriage->update($marriageData);
            }
            
            // Update husband
            if ($request->has('husband')) {
                $husbandData = $request->input('husband');
                $husband = $marriage->spouses()->where('spouse_type', 'husband')->first();
                if ($husband) {
                    // Format data
                    if (isset($husbandData['name'])) $husbandData['name'] = strtoupper($husbandData['name']);
                    if (isset($husbandData['occupation'])) $husbandData['occupation'] = strtoupper($husbandData['occupation']);
                    if (isset($husbandData['residence'])) $husbandData['residence'] = strtoupper($husbandData['residence']);
                    if (isset($husbandData['father_name'])) $husbandData['father_name'] = strtoupper($husbandData['father_name']);
                    if (isset($husbandData['mother_name'])) $husbandData['mother_name'] = strtoupper($husbandData['mother_name']);
                    
                    $husband->update($husbandData);
                }
            }
            
            // Update wife
            if ($request->has('wife')) {
                $wifeData = $request->input('wife');
                $wife = $marriage->spouses()->where('spouse_type', 'wife')->first();
                if ($wife) {
                    // Format data
                    if (isset($wifeData['name'])) $wifeData['name'] = strtoupper($wifeData['name']);
                    if (isset($wifeData['occupation'])) $wifeData['occupation'] = strtoupper($wifeData['occupation']);
                    if (isset($wifeData['residence'])) $wifeData['residence'] = strtoupper($wifeData['residence']);
                    if (isset($wifeData['father_name'])) $wifeData['father_name'] = strtoupper($wifeData['father_name']);
                    if (isset($wifeData['mother_name'])) $wifeData['mother_name'] = strtoupper($wifeData['mother_name']);
                    
                    $wife->update($wifeData);
                }
            }
            
            // Update witnesses
            if ($request->has('witnesses')) {
                $witnessesData = $request->input('witnesses');
                $witnessList = $marriage->witnesses()->get();
                
                if ($witnessList->count() >= 1 && isset($witnessesData['witness1'])) {
                    $witness1Data = [
                        'name' => strtoupper($witnessesData['witness1']['name']),
                        'spouse_side' => $witnessesData['witness1']['side']
                    ];
                    $witnessList[0]->update($witness1Data);
                }
                
                if ($witnessList->count() >= 2 && isset($witnessesData['witness2'])) {
                    $witness2Data = [
                        'name' => strtoupper($witnessesData['witness2']['name']),
                        'spouse_side' => $witnessesData['witness2']['side']
                    ];
                    $witnessList[1]->update($witness2Data);
                }
            }
            
            // Update statuses based on action
            $pdfPage = $marriage->pdfPage;
            
            if ($action === 'approve') {
                // Approve: mark as completed
                $pdfPage->update([
                    'status' => 'completed',
                    'completed_by' => $user->id,
                    'completed_at' => now(),
                ]);
                
                $marriage->update([
                    'system_status' => 'Completed',
                    'verified_by' => $user->id,
                    'reviewed_at' => now(),
                    'has_errors' => false,
                ]);
                
                $message = 'Marriage approved and marked as completed.';
            } else {
                // Reject: send back to clerk as skipped
                $pdfPage->update([
                    'status' => 'skipped',
                    'completed_by' => null,
                    'completed_at' => null,
                ]);
                
                $marriage->update([
                    'system_status' => 'Skipped',
                    'has_errors' => true,
                ]);
                
                $message = 'Marriage rejected and sent back to clerk for review.';
            }
            
            // Add to notes history
            $notesData = is_string($pdfPage->notes) ? json_decode($pdfPage->notes, true) : ($pdfPage->notes ?? []);
            if (!is_array($notesData)) {
                $notesData = [];
            }
            
            $notesData['review_history'][] = [
                'action' => $action,
                'message' => $message,
                'notes' => $notes,
                'user' => $user->name,
                'user_role' => $userRole,
                'timestamp' => now()->toISOString(),
            ];
            
            $pdfPage->update(['notes' => json_encode($notesData)]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_page_status' => $pdfPage->fresh()->status,
                'new_marriage_status' => $marriage->fresh()->system_status
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Marriage review failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process review: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updatePageStatus(Request $request, PdfPage $page)
    {
        $user = Auth::user();
        $roleName = $user->role->name ?? 'user';
        $action = $request->input('action');
        $notes = $request->input('notes', '');
        
        $validActions = [
            'data_clerk' => ['submit_for_review', 'skip_page'],
            'marriage_teller' => ['approve_completed', 'reject_to_clerk'],
            'marriage_registrar' => ['publish', 'send_back_to_teller'],
        ];
        
        if (!in_array($action, $validActions[$roleName] ?? [])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid action for your role.'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $newStatus = match($action) {
                'submit_for_review' => 'review_needed',
                'skip_page' => 'skipped',
                'approve_completed' => 'completed',
                'reject_to_clerk' => 'skipped', // Send back to clerk as skipped
                'publish' => 'published',
                'send_back_to_teller' => 'review_needed',
                default => null
            };
            
            if (!$newStatus) {
                throw new \Exception('Invalid action');
            }
            
            // Update notes as JSON with action history
            $notesData = is_string($page->notes) ? json_decode($page->notes, true) : ($page->notes ?? []);
            if (!is_array($notesData)) {
                $notesData = [];
            }
            
            $actionMessages = [
                'submit_for_review' => 'Submitted for review by ' . $user->name,
                'skip_page' => 'Skipped by ' . $user->name . ($notes ? ': ' . $notes : ''),
                'approve_completed' => 'Approved as completed by ' . $user->name,
                'reject_to_clerk' => 'Rejected back to clerk by ' . $user->name . ($notes ? ': ' . $notes : ''),
                'publish' => 'Published by ' . $user->name,
                'send_back_to_teller' => 'Sent back to teller for review by ' . $user->name . ($notes ? ': ' . $notes : ''),
            ];
            
            $notesData['history'][] = [
                'action' => $action,
                'message' => $actionMessages[$action],
                'user' => $user->name,
                'user_id' => $user->id,
                'timestamp' => now()->toISOString(),
            ];
            
            if ($notes) {
                $notesData['latest_note'] = $notes;
                $notesData['latest_note_by'] = $user->name;
                $notesData['latest_note_at'] = now()->toISOString();
            }
            
            $page->update([
                'status' => $newStatus,
                'notes' => json_encode($notesData),
            ]);
            
            // If publishing, update the PDF upload status
            if ($action === 'publish') {
                $page->pdfUpload->update(['status' => 'published']);
            }
            
            DB::commit();
            
            // Return JSON response for AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $actionMessages[$action],
                    'new_status' => $newStatus,
                    'page_status' => $page->fresh()->status,
                    'redirect_url' => $action === 'publish' ? route('pdf-uploads.index') : null
                ]);
            }
            
            // For non-AJAX requests, redirect back
            return redirect()->back()->with('success', $actionMessages[$action]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function getNewStatusByAction($action, $roleName, $page)
    {
        switch ($action) {
            case 'submit_for_review':
                return $page->status === 'in_progress' ? 'review_needed' : null;
                
            case 'skip_page':
                return 'skipped';
                
            case 'approve_completed':
                return 'completed';
                
            case 'reject_to_clerk':
                return 'in_progress';
                
            case 'publish':
                return 'completed'; // Status remains completed, PDF upload status changes
                
            case 'send_back_to_teller':
                return 'review_needed';
                
            default:
                return null;
        }
    }


    private function updateNotesWithAction($currentNotes, $action, $notes, $user)
    {
        $notesData = is_string($currentNotes) ? json_decode($currentNotes, true) : ($currentNotes ?? []);
        if (!is_array($notesData)) {
            $notesData = [];
        }
        
        $actionMessages = [
            'submit_for_review' => 'Submitted for review by ' . $user->name,
            'skip_page' => 'Skipped by ' . $user->name . ($notes ? ': ' . $notes : ''),
            'approve_completed' => 'Approved as completed by ' . $user->name,
            'reject_to_clerk' => 'Rejected back to clerk by ' . $user->name . ($notes ? ': ' . $notes : ''),
            'publish' => 'Published by ' . $user->name,
            'send_back_to_teller' => 'Sent back to teller for review by ' . $user->name . ($notes ? ': ' . $notes : ''),
        ];
        
        $notesData['history'][] = [
            'action' => $action,
            'message' => $actionMessages[$action] ?? $action,
            'user' => $user->name,
            'user_id' => $user->id,
            'timestamp' => now()->toISOString(),
        ];
        
        if ($notes) {
            $notesData['latest_note'] = $notes;
            $notesData['latest_note_by'] = $user->name;
            $notesData['latest_note_at'] = now()->toISOString();
        }
        
        return json_encode($notesData);
    }

    private function getSuccessMessage($action)
    {
        $messages = [
            'submit_for_review' => 'Page submitted for review successfully!',
            'skip_page' => 'Page has been skipped.',
            'approve_completed' => 'Page marked as completed!',
            'reject_to_clerk' => 'Page sent back to clerk for corrections.',
            'publish' => 'Page published successfully!',
            'send_back_to_teller' => 'Page sent back to teller for review.',
        ];
        
        return $messages[$action] ?? 'Status updated successfully!';
    }


    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->isTeller() && !$user->isDataClerk() && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $counties = County::select('county_code', 'name')
                            ->distinct('county_code')
                            ->orderBy('name')
                            ->get();

        $rawMonths = cal_info(0)['months'];

        $months = [];
        foreach ($rawMonths as $num => $label) {
            $months[str_pad($num, 2, '0', STR_PAD_LEFT)] = $label;
        }

        return view('pdf-uploads.create', compact('counties', 'months'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|digits:4|integer|min:1960|max:' . date('Y'),
            'month' => 'required|string|max:20',
            'county_code' => 'required|string|max:10',
            'pdf_file' => 'required|file|mimes:pdf|max:102400',
            'marriage_type_id' => 'nullable|exists:categories,id',
        ]);

        try {
            $file = $request->file('pdf_file');

            // Check for duplicate files
            $fileHash = hash_file('sha256', $file->getRealPath());
            $existing = PdfUpload::where('file_hash', $fileHash)->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'This PDF has already been uploaded. Duplicate files are not allowed.',
                ], 422);
            }

            // Generate random 40-character filename + .pdf extension
            $randomFilename = Str::random(40) . '.pdf';

            // Create directory structure
            $year = $request->year;
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
            $yearMonthDir = 'pdf-uploads/' . $year . '/' . $month;
            
            // Ensure directory exists
            if (!Storage::disk('public')->exists($yearMonthDir)) {
                Storage::disk('public')->makeDirectory($yearMonthDir, 0755, true);
            }

            $path = $yearMonthDir . '/' . $randomFilename;

            // Store the file
            Storage::disk('public')->put(
                $path,
                file_get_contents($file->getRealPath())
            );

            // Verify file was stored correctly
            if (!Storage::disk('public')->exists($path)) {
                throw new \Exception('Failed to store file');
            }

            // Get page count
            try {
                $pageCount = $this->getPdfPageCount($file);
            } catch (\Exception $e) {
                Log::warning('Failed to get page count for PDF: ' . $e->getMessage());
                $pageCount = 1;
            }

            // Create PDF record
            $pdfUpload = PdfUpload::create([
                'year' => $year,
                'month' => $month,
                'county_code' => $request->county_code,
                'filename' => $randomFilename,
                'file_size' => $file->getSize(),
                'name' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'total_pages' => $pageCount,
                'file_hash' => $fileHash,
                'uploaded_by' => auth()->id(),
                'marriage_type_id' => $request->marriage_type_id,
                'status' => 'uploaded',
                'uuid' => Str::uuid()->toString(),
            ]);

            // Create PDF pages
            $this->createPdfPages($pdfUpload, $pageCount);
            
            // Generate thumbnail (non-blocking - run in background)
            try {
                $this->generatePdfThumbnail($pdfUpload);
            } catch (\Exception $e) {
                Log::warning('Thumbnail generation failed: ' . $e->getMessage());
            }

            Log::info('PDF uploaded successfully via regular upload', [
                'id' => $pdfUpload->id,
                'name' => $pdfUpload->name,
                'size' => $file->getSize(),
                'pages' => $pageCount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PDF uploaded successfully',
                'data' => [
                    'id' => $pdfUpload->id,
                    'name' => $pdfUpload->name,
                    'page_count' => $pageCount,
                    'file_size' => $file->getSize()
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('PDF Upload Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadBase64Chunk(Request $request)
    {
        try {
            Log::info('Base64 chunk upload started', [
                'upload_id' => $request->input('upload_id'),
                'chunk_index' => $request->input('chunk_index'),
                'total_chunks' => $request->input('total_chunks'),
                'has_chunk_data' => $request->has('chunk_data'),
                'is_last_chunk' => $request->input('is_last_chunk', false),
            ]);

            $validator = Validator::make($request->all(), [
                'upload_id' => 'required|string',
                'chunk_index' => 'required|integer|min:0',
                'total_chunks' => 'required|integer|min:1',
                'chunk_data' => 'required|string',
                'original_name' => 'required|string',
                'is_last_chunk' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadId = $request->upload_id;
            $chunkIndex = $request->chunk_index;
            $totalChunks = $request->total_chunks;
            $chunkData = $request->chunk_data;
            $originalName = $request->original_name;
            $isLastChunk = $request->is_last_chunk;

            // Validate base64 data
            if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $chunkData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid base64 data format'
                ], 400);
            }

            // Decode base64 data
            $binaryData = base64_decode($chunkData, true);
            if ($binaryData === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to decode base64 data'
                ], 400);
            }

            // Create temporary directory for chunks
            $tempDir = storage_path('app/temp/chunks/' . $uploadId);
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0777, true)) {
                    Log::error('Failed to create directory', ['path' => $tempDir]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create temporary directory.'
                    ], 500);
                }
            }

            // Save chunk as binary file
            $chunkFilename = 'chunk_' . str_pad($chunkIndex, 5, '0', STR_PAD_LEFT);
            $chunkPath = $tempDir . '/' . $chunkFilename;
            
            // Save as binary file
            file_put_contents($chunkPath, $binaryData);

            Log::info('Chunk saved', [
                'chunk_index' => $chunkIndex,
                'chunk_size' => strlen($binaryData),
                'chunk_path' => $chunkPath,
            ]);

            // If this is the last chunk, assemble the file
            if ($isLastChunk) {
                return $this->assembleBase64Chunks($request, $uploadId, $originalName);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chunk uploaded successfully',
                'chunk_index' => $chunkIndex,
                'total_chunks' => $totalChunks,
            ]);

        } catch (\Exception $e) {
            Log::error('Base64 Chunk Upload Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Chunk upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function assembleBase64Chunks($request, $uploadId, $originalName)
    {
        try {
            $tempDir = storage_path('app/temp/chunks/' . $uploadId);
            
            if (!file_exists($tempDir)) {
                throw new \Exception('No chunks found for upload ID: ' . $uploadId);
            }

            $chunkFiles = glob($tempDir . '/chunk_*');
            if (empty($chunkFiles)) {
                throw new \Exception('No chunk files found');
            }

            // Sort chunks by index
            sort($chunkFiles);

            // Generate random filename like the regular uploads
            $randomFilename = Str::random(40) . '.pdf';

            // Create directory structure
            $year = $request->year;
            $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
            $yearMonthDir = 'pdf-uploads/' . $year . '/' . $month;
            
            // Create directory if it doesn't exist
            if (!Storage::disk('public')->exists($yearMonthDir)) {
                Storage::disk('public')->makeDirectory($yearMonthDir, 0755, true);
            }

            $finalPath = $yearMonthDir . '/' . $randomFilename;
            $finalFilePath = storage_path('app/public/' . $finalPath);

            Log::info('Assembling chunks to: ' . $finalFilePath);

            // Assemble chunks using binary concatenation
            $finalFile = fopen($finalFilePath, 'wb');
            if (!$finalFile) {
                throw new \Exception('Failed to create final file');
            }

            $totalSize = 0;
            foreach ($chunkFiles as $chunkFile) {
                $chunkData = file_get_contents($chunkFile);
                $bytesWritten = fwrite($finalFile, $chunkData);
                if ($bytesWritten === false) {
                    fclose($finalFile);
                    throw new \Exception('Failed to write chunk: ' . $chunkFile);
                }
                $totalSize += strlen($chunkData);
            }
            fclose($finalFile);

            // Verify the file was created
            if (!file_exists($finalFilePath)) {
                throw new \Exception('Final file was not created');
            }

            // Verify file size
            $actualSize = filesize($finalFilePath);
            if ($actualSize !== $totalSize) {
                Log::warning('File size mismatch', [
                    'expected' => $totalSize,
                    'actual' => $actualSize
                ]);
            }

            // Check if file is a valid PDF
            if (!function_exists('mime_content_type')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $finalFilePath);
                finfo_close($finfo);
            } else {
                $mimeType = mime_content_type($finalFilePath);
            }

            if ($mimeType !== 'application/pdf') {
                Log::error('Invalid MIME type for assembled file: ' . $mimeType);
                $fileContent = file_get_contents($finalFilePath, false, null, 0, 5);
                if (strpos($fileContent, '%PDF-') === 0) {
                    Log::info('File appears to be PDF despite MIME type');
                } else {
                    unlink($finalFilePath);
                    throw new \Exception('The uploaded file is not a valid PDF (MIME: ' . $mimeType . ')');
                }
            }

            // Generate file hash
            $fileHash = hash_file('sha256', $finalFilePath);
            
            // Check for duplicate files
            $existing = PdfUpload::where('file_hash', $fileHash)->first();
            if ($existing) {
                foreach ($chunkFiles as $chunkFile) {
                    unlink($chunkFile);
                }
                if (is_dir($tempDir) && count(scandir($tempDir)) == 2) {
                    rmdir($tempDir);
                }
                if (file_exists($finalFilePath)) {
                    unlink($finalFilePath);
                }
                
                throw new \Exception('This PDF has already been uploaded. Duplicate files are not allowed.');
            }

            // Get page count
            try {
                $pageCount = $this->getPdfPageCountFromPath($finalFilePath);
            } catch (\Exception $e) {
                Log::error('Failed to get page count: ' . $e->getMessage());
                $pageCount = 1;
            }
            
            // Generate UUID
            $uuid = Str::uuid()->toString();
            
            // Create PDF record with random filename
            $pdfUpload = PdfUpload::create([
                'year' => $year,
                'month' => $month,
                'county_code' => $request->county_code,
                'filename' => $randomFilename,
                'file_size' => $totalSize,
                'name' => $originalName,
                'storage_path' => $finalPath,
                'total_pages' => $pageCount,
                'file_hash' => $fileHash,
                'uploaded_by' => auth()->id(),
                'marriage_type_id' => $request->marriage_type_id,
                'status' => 'uploaded',
                'uuid' => $uuid,
            ]);

            // Create PDF pages
            $this->createPdfPages($pdfUpload, $pageCount);
            $this->generatePdfThumbnail($pdfUpload);

            // Clean up chunks
            foreach ($chunkFiles as $chunkFile) {
                unlink($chunkFile);
            }
            if (is_dir($tempDir) && count(scandir($tempDir)) == 2) {
                rmdir($tempDir);
            }

            Log::info('PDF uploaded successfully via base64 chunking', [
                'id' => $pdfUpload->id,
                'path' => $finalPath,
                'size' => $totalSize,
                'pages' => $pageCount,
                'filename' => $randomFilename,
                'original_name' => $originalName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully!',
                'data' => [
                    'id' => $pdfUpload->id,
                    'name' => $pdfUpload->name,
                    'page_count' => $pageCount
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Assemble Base64 Chunks Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if (isset($tempDir) && file_exists($tempDir)) {
                $chunkFiles = glob($tempDir . '/chunk_*');
                foreach ($chunkFiles as $chunkFile) {
                    @unlink($chunkFile);
                }
                @rmdir($tempDir);
            }

            if (isset($finalFilePath) && file_exists($finalFilePath)) {
                @unlink($finalFilePath);
            }

            return response()->json([
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createPdfPages(PdfUpload $pdfUpload, $pageCount)
    {
        $pages = [];
        for ($i = 1; $i <= $pageCount; $i++) {
            $pages[] = [
                'pdf_upload_id' => $pdfUpload->id,
                'page_number' => $i,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('pdf_pages')->insert($pages);
    }

    public function show($id, Request $request)
    {
        $pdfPage = PdfPage::with([
            'pdfUpload', 
            'pdfUpload.uploader', 
            'pdfUpload.county',
            'marriage'
        ])->findOrFail($id);
        
        $pdfUpload = $pdfPage->pdfUpload;
        
        if (!$pdfUpload) {
            abort(404, 'PDF upload not found for this page');
        }
        
        $pageContent = $this->extractPageForDisplay($pdfUpload, $pdfPage->page_number);
        
        $countyName = optional($pdfUpload->county)->name ?? 'Unknown County';
        $countyCode = $pdfUpload->county_code;
        
        $constants = [
            'year' => $pdfUpload->year ?? '',
            'month' => $pdfUpload->month ?? '',
            'county_code' => $countyCode ?? '',
            'county_name' => $countyName,
            'pdf_upload_id' => $pdfUpload->id,
            'pdf_page_id' => $pdfPage->id,
            'page_number' => $pdfPage->page_number,
        ];
        
        $marriage = $pdfPage->marriage;
        $month = Carbon::create()->month((int) $constants['month'])->format('F');

        $allPages = PdfPage::where('pdf_upload_id', $pdfUpload->id)
            ->orderBy('page_number')
            ->get();
        
        $relatedPages = PdfPage::where('pdf_upload_id', $pdfUpload->id)
            ->where('id', '!=', $pdfPage->id)
            ->whereBetween('page_number', [
                max(1, $pdfPage->page_number - 3),
                min($pdfUpload->total_pages, $pdfPage->page_number + 3)
            ])
            ->orderBy('page_number')
            ->get();
        
        $counties = County::select('name', 'county_code')
            ->whereNotNull('name')
            ->whereNotNull('county_code')
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();

        $subCounties = County::select('constituency')
            ->where('county_code', $pdfUpload->county_code)
            ->whereNotNull('constituency')
            ->where('constituency', '!=', '')
            ->distinct('constituency')
            ->orderBy('constituency')
            ->get();
        
        if ($subCounties->isEmpty()) {
            $subCounties = County::select('constituency')
                ->whereNotNull('constituency')
                ->where('constituency', '!=', '')
                ->distinct('constituency')
                ->orderBy('constituency')
                ->get();
        }

        // Get all wards for this county with constituency info
        $wards = County::where('county_code', $pdfUpload->county_code)
            ->whereNotNull('wards')
            ->where('wards', '!=', '')
            ->whereNotNull('constituency')
            ->where('constituency', '!=', '')
            ->select('id', 'wards', 'constituency')
            ->orderBy('constituency')
            ->orderBy('wards')
            ->get(); // Remove ->toArray()

        $marriageStatusId = Category::where('type', 'marriage_status')
            ->where('name', 'Completed')
            ->value('id') ?? null;

        $verificationStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id') ?? null;

        $marriageTypes = Category::where('type', 'marriage_type')
            ->orderBy('name')
            ->get();

        $commonOccupations = [
            'Teacher', 'Doctor', 'Engineer', 'Lawyer', 'Accountant',
            'Businessman', 'Businesswoman', 'Civil Servant', 'Farmer',
            'Mechanic', 'Driver', 'Nurse', 'Police Officer', 'Soldier',
            'Lecturer', 'Journalist', 'Architect', 'Pharmacist', 'Dentist',
            'Shopkeeper', 'Clerk', 'Manager', 'Director', 'Consultant',
            'Secretary', 'Receptionist', 'Cleaner', 'Guard', 'Chef',
            'Waiter', 'Waitress', 'Bartender', 'Househelp', 'Artisan',
            'Carpenter', 'Plumber', 'Electrician', 'Mason', 'Painter',
            'Tailor', 'Designer', 'Photographer', 'Videographer', 'Editor',
            'DECEASED'
        ];
        
        $primaryColor = SystemHelper::primaryColor();
        $secondaryColor = SystemHelper::secondaryColor();
        
        return view('pdf-uploads.show', compact(
            'pdfPage',
            'pdfUpload',
            'pageContent',
            'marriage',
            'constants',
            'allPages',
            'relatedPages',
            'counties',
            'subCounties',
            'wards',
            'marriageStatusId',
            'verificationStatusId',
            'marriageTypes',
            'month',
            'primaryColor',
            'secondaryColor',
            'commonOccupations'
        ));
    }

    public function getPageData(PdfUpload $pdfUpload, $pageNumber)
    {
        $page = $pdfUpload->pages()->where('page_number', $pageNumber)->firstOrFail();
        
        return response()->json([
            'page' => $page->load('assignedUser'),
            'marriage' => $page->marriage ? $page->marriage->load(['husband', 'wife']) : null,
            'pdf_upload' => [
                'id' => $pdfUpload->id,
                'name' => $pdfUpload->name,
                'county_code' => $pdfUpload->county_code,
                'year' => $pdfUpload->year,
                'month' => $pdfUpload->month,
            ]
        ]);
    }

    public function showPage(PdfUpload $pdfUpload, $pageNumber)
    {
        $page = $pdfUpload->pages()->where('page_number', $pageNumber)->firstOrFail();
        
        $pageContent = $this->extractPageForDisplay($pdfUpload, $pageNumber);
        
        $marriage = $page->marriage;
        
        $constants = [
            'year' => $pdfUpload->year,
            'month' => $pdfUpload->month,
            'county_code' => $pdfUpload->county_code,
            'county_name' => $pdfUpload->county->name ?? '',
            'pdf_upload_id' => $pdfUpload->id,
            'pdf_page_id' => $page->id,
            'page_number' => $page->page_number,
        ];
        
        return view('pdf-uploads.page', compact('pdfUpload', 'page', 'pageContent', 'marriage', 'constants'));
    }

    private function extractPageForDisplay(PdfUpload $pdfUpload, $pageNumber)
    {
        try {
            // Always extract as PDF, bypassing Imagick
            return $this->extractPageAsPdf($pdfUpload, $pageNumber);
        } catch (\Exception $e) {
            Log::error('Error extracting page for display: ' . $e->getMessage());
            return null; // or handle gracefully
        }
    }

    private function extractPageAsImage(PdfUpload $pdfUpload, $pageNumber)
    {
        try {
            $pdfPath = Storage::disk('public')->path($pdfUpload->storage_path);
            
            if (!file_exists($pdfPath)) {
                throw new \Exception('PDF file not found at path: ' . $pdfPath);
            }
            
            if (!extension_loaded('imagick') || !class_exists('Imagick')) {
                throw new \Exception('Imagick extension is not available');
            }
            
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($pdfPath . '[' . ($pageNumber - 1) . ']');
            
            $originalWidth = $imagick->getImageWidth();
            $originalHeight = $imagick->getImageHeight();
            
            $maxWidth = 1200;
            $maxHeight = 1600;
            
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                $scale = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
                $newWidth = round($originalWidth * $scale);
                $newHeight = round($originalHeight * $scale);
                $imagick->resizeImage($newWidth, $newHeight, \Imagick::FILTER_LANCZOS, 1);
            }
            
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality(85);
            $imagick->setImageBackgroundColor('white');
            $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            
            $imageBlob = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();
            
            return 'data:image/jpeg;base64,' . base64_encode($imageBlob);
            
        } catch (\Exception $e) {
            Log::error('Error extracting page as image: ' . $e->getMessage(), [
                'pdf_id' => $pdfUpload->id,
                'page' => $pageNumber,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->extractPageAsPdf($pdfUpload, $pageNumber);
        }
    }

    private function extractPageAsPdf(PdfUpload $pdfUpload, $pageNumber)
    {
        try {
            $pdf = new Fpdi();
            $pdfPath = Storage::disk('public')->path($pdfUpload->storage_path);
            
            if (!file_exists($pdfPath)) {
                throw new \Exception('PDF file not found');
            }
            
            $pageCount = $pdf->setSourceFile($pdfPath);
            
            if ($pageNumber > $pageCount) {
                return null;
            }
            
            $templateId = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($templateId);
            
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
            
            $pageContent = $pdf->Output('S');
            
            return 'data:application/pdf;base64,' . base64_encode($pageContent);
            
        } catch (\Exception $e) {
            Log::error('Error extracting page as PDF: ' . $e->getMessage());
            return null;
        }
    }

    public function edit(PdfUpload $pdfUpload)
    {
        $counties = County::select('county_code', 'name')
                            ->distinct('county_code')
                            ->orderBy('name')
                            ->get();
        
        $months = $this->getMonthsList();
        
        return view('pdf-uploads.edit', compact('pdfUpload', 'counties', 'months'));
    }

    public function update(Request $request, PdfUpload $pdfUpload)
    {
        $request->validate([
            'year' => 'required|digits:4|integer|min:1960|max:' . date('Y'),
            'month' => 'required|string|max:20',
            'county_code' => 'required|string|max:10',
            'pdf_upload_status' => 'required|in:uploaded,processing,ready,completed,archived',
            'page_status' => 'nullable|in:pending,assigned,in_progress,completed,review_needed,skipped',
            'current_page_id' => 'nullable|exists:pdf_pages,id',
            'marriage_type_id' => 'nullable|exists:categories,id',
            'pdf_file' => 'nullable|file|mimes:pdf|max:102400',
        ]);

        try {
            if ($request->hasFile('pdf_file')) {
                Storage::disk('public')->delete($pdfUpload->storage_path);
                
                $file = $request->file('pdf_file');
                $fileHash = hash_file('sha256', $file->getRealPath());
                
                $existing = PdfUpload::where('file_hash', $fileHash)
                    ->where('id', '!=', $pdfUpload->id)
                    ->first();

                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This PDF has already been uploaded. Duplicate files are not allowed.',
                    ], 422);
                }
                
                $filename = Str::random(40) . '.pdf';
                $path = 'pdf-uploads/' . $request->year . '/'
                    . str_pad($request->month, 2, '0', STR_PAD_LEFT) . '/'
                    . $filename;
                
                Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
                
                $pageCount = $this->getPdfPageCount($file);
                
                $pdfUpload->update([
                    'filename' => $filename,
                    'name' => $file->getClientOriginalName(),
                    'storage_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_hash' => $fileHash,
                    'total_pages' => $pageCount,
                ]);
                
                $pdfUpload->pages()->delete();
                $this->createPdfPages($pdfUpload, $pageCount);
            }
            
            $updateData = [
                'year' => $request->year,
                'month' => $request->month,
                'county_code' => $request->county_code,
                'status' => $request->pdf_upload_status,
            ];
            
            if ($request->has('marriage_type_id') && $request->marriage_type_id) {
                $updateData['marriage_type_id'] = $request->marriage_type_id;
            } else {
                $updateData['marriage_type_id'] = null;
            }
            
            $pdfUpload->update($updateData);
            
            if ($request->has('page_status') && $request->page_status) {
                $currentPageId = $request->input('current_page_id');
                
                if ($currentPageId) {
                    $page = PdfPage::find($currentPageId);
                    if ($page && $page->pdf_upload_id === $pdfUpload->id) {
                        $page->update(['status' => $request->page_status]);
                    }
                } else {
                    $firstPage = $pdfUpload->pages()->first();
                    if ($firstPage) {
                        $firstPage->update(['status' => $request->page_status]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'PDF details updated successfully.',
                'data' => $pdfUpload->load(['pages', 'county', 'marriageType'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PdfUpload $pdfUpload)
    {
        try {
            Storage::disk('public')->delete($pdfUpload->storage_path);
            
            if ($pdfUpload->thumbnail_path) {
                Storage::disk('public')->delete($pdfUpload->thumbnail_path);
            }
            
            $pdfUpload->pdfPages()->delete();
            $pdfUpload->marriages()->delete();
            $pdfUpload->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'PDF and all related records deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download(PdfUpload $pdfUpload)
    {
        if (!Storage::disk('public')->exists($pdfUpload->storage_path)) {
            return redirect()->back()->with('error', 'File not found.');
        }
        
        return Storage::disk('public')->download($pdfUpload->storage_path, $pdfUpload->name);
    }

    public function preview(PdfUpload $pdfUpload)
    {
        if (!Storage::disk('public')->exists($pdfUpload->storage_path)) {
            abort(404, 'PDF file not found.');
        }
        
        $path = Storage::disk('public')->path($pdfUpload->storage_path);
        
        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdfUpload->name . '.pdf"',
            'Cache-Control' => 'public, max-age=3600'
        ]);
    }

    public function thumbnail(PdfUpload $pdfUpload)
    {
        if ($pdfUpload->thumbnail_path && Storage::disk('public')->exists($pdfUpload->thumbnail_path)) {
            return response()->file(Storage::disk('public')->path($pdfUpload->thumbnail_path));
        }
        
        return response()->file(public_path('images/default-pdf-thumb.png'));
    }

    private function getOverviewCardData()
    {
        $user = Auth::user();
        $roleName = $user->role->name ?? 'user';
        
        // Base counts (global - for admin)
        $totalPDFsAll = PdfUpload::count();
        $totalStorageAll = PdfUpload::sum('file_size');
        $storageUsedMB = round($totalStorageAll / (1024 * 1024), 2);
        
        // Today's counts
        $pdfsToday = PdfUpload::whereDate('created_at', today())->count();
        $marriagesToday = Marriage::whereDate('created_at', today())->count();
        $publishedToday = PdfUpload::whereDate('created_at', today())->where('status', 'published')->count();
        
        // Initialize role-specific stats
        $stats = [
            // Common stats
            'total_pdfs' => $totalPDFsAll,
            'storage_used' => $storageUsedMB,
            'storage_formatted' => $storageUsedMB . ' MB',
            'pdfs_today' => $pdfsToday,
            'marriages_today' => $marriagesToday,
            'published_today' => $publishedToday,
            
            // Role-specific will be filled below
            'my_assigned' => 0,
            'my_assigned_today' => 0,
            'my_completed' => 0,
            'my_completed_today' => 0,
            'my_pending' => 0,
            'my_progress' => 0,
            'team_total' => 0,
            'team_new_today' => 0,
            'team_completed' => 0,
            'team_completed_today' => 0,
            'team_pending' => 0,
            'active_clerks' => 0,
            'active_tellers' => 0,
            'verification_queue' => 0,
            'queue_new_today' => 0,
            'verified_today' => 0,
            'verified_total' => 0,
            'pending_verification' => 0,
            'total_marriages' => 0,
            'published_total' => 0,
            'completion_rate' => 0,
        ];
        
        switch ($roleName) {
            case 'data_clerk':
                // Data clerk sees their own work
                $myAssigned = PdfPage::where('assigned_to', $user->id)->count();
                $myAssignedToday = PdfPage::where('assigned_to', $user->id)
                    ->whereDate('assigned_at', today())
                    ->count();
                $myCompleted = PdfPage::where('assigned_to', $user->id)
                    ->whereIn('status', ['completed', 'review_needed'])
                    ->count();
                $myCompletedToday = PdfPage::where('assigned_to', $user->id)
                    ->whereIn('status', ['completed', 'review_needed'])
                    ->whereDate('updated_at', today())
                    ->count();
                $myPending = PdfPage::where('assigned_to', $user->id)
                    ->whereNotIn('status', ['completed', 'review_needed'])
                    ->count();
                
                $stats['my_assigned'] = $myAssigned;
                $stats['my_assigned_today'] = $myAssignedToday;
                $stats['my_completed'] = $myCompleted;
                $stats['my_completed_today'] = $myCompletedToday;
                $stats['my_pending'] = $myPending;
                $stats['my_progress'] = $myAssigned > 0 
                    ? round(($myCompleted / $myAssigned) * 100, 1) 
                    : 0;
                $stats['total_marriages'] = Marriage::whereHas('pdfPage', function($q) use ($user) {
                    $q->where('assigned_to', $user->id);
                })->count();
                $stats['marriages_today'] = Marriage::whereHas('pdfPage', function($q) use ($user) {
                    $q->where('assigned_to', $user->id);
                })->whereDate('created_at', today())->count();
                break;
                
            case 'marriage_teller':
                // Teller sees their team's work
                $clerkIds = ClerkManagement::where('marriage_teller_id', $user->id)
                    ->pluck('data_clerk_id');
                
                if ($clerkIds->isNotEmpty()) {
                    $teamTotal = PdfPage::whereIn('assigned_to', $clerkIds)->count();
                    $teamNewToday = PdfPage::whereIn('assigned_to', $clerkIds)
                        ->whereDate('assigned_at', today())
                        ->count();
                    $teamCompleted = PdfPage::whereIn('assigned_to', $clerkIds)
                        ->whereIn('status', ['completed', 'review_needed'])
                        ->count();
                    $teamCompletedToday = PdfPage::whereIn('assigned_to', $clerkIds)
                        ->whereIn('status', ['completed', 'review_needed'])
                        ->whereDate('updated_at', today())
                        ->count();
                    $teamPending = PdfPage::whereIn('assigned_to', $clerkIds)
                        ->whereNotIn('status', ['completed', 'review_needed'])
                        ->count();
                    
                    $stats['team_total'] = $teamTotal;
                    $stats['team_new_today'] = $teamNewToday;
                    $stats['team_completed'] = $teamCompleted;
                    $stats['team_completed_today'] = $teamCompletedToday;
                    $stats['team_pending'] = $teamPending;
                    $stats['active_clerks'] = $clerkIds->count();
                    $stats['completion_rate'] = $teamTotal > 0 
                        ? round(($teamCompleted / $teamTotal) * 100, 1) 
                        : 0;
                }
                break;
                
            case 'marriage_registrar':
                // Registrar sees verification stats
                $stats['verification_queue'] = PdfPage::where('status', 'review_needed')->count();
                $stats['queue_new_today'] = PdfPage::where('status', 'review_needed')
                    ->whereDate('updated_at', today())
                    ->count();
                $stats['verified_today'] = Marriage::whereDate('reviewed_at', today())->count();
                $stats['verified_total'] = Marriage::whereNotNull('reviewed_at')->count();
                $stats['pending_verification'] = PdfPage::where('status', 'completed')->count();
                $stats['total_marriages'] = Marriage::count();
                $stats['marriages_today'] = Marriage::whereDate('created_at', today())->count();
                break;
                
            case 'admin':
                // Admin sees everything
                $stats['total_pdfs'] = $totalPDFsAll;
                $stats['pdfs_today'] = $pdfsToday;
                $stats['total_marriages'] = Marriage::count();
                $stats['marriages_today'] = $marriagesToday;
                $stats['published_total'] = PdfUpload::where('status', 'published')->count();
                $stats['published_today'] = $publishedToday;
                $stats['active_clerks'] = User::where('role_id', 4)->count();
                $stats['active_tellers'] = User::where('role_id', 3)->count();
                $stats['verification_queue'] = PdfPage::where('status', 'review_needed')->count();
                $stats['completed_total'] = PdfPage::where('status', 'completed')->count();
                $stats['completion_rate'] = $totalPDFsAll > 0 
                    ? round((PdfPage::where('status', 'completed')->count() / PdfPage::count()) * 100, 1)
                    : 0;
                break;
                
            default:
                // Default user view
                $stats['total_marriages'] = Marriage::where('status', 'published')->count();
                $stats['published_total'] = PdfUpload::where('status', 'published')->count();
                $stats['published_today'] = PdfUpload::whereDate('created_at', today())
                    ->where('status', 'published')
                    ->count();
                break;
        }
        
        return $stats;
    }

    private function getStorageRecorded()
    {
        $totalStorageBytes = PdfUpload::sum('file_size');
        $storageUsedMB = round($totalStorageBytes / (1024 * 1024), 2);
        $storageLimitGB = 1;
        
        return $storageUsedMB . ' MB / ' . $storageLimitGB . ' GB';
    }
    
    private function calculatePdfCompletionRate(PdfUpload $pdfUpload)
    {
        $fields = [
            'year' => !empty($pdfUpload->year),
            'month' => !empty($pdfUpload->month),
            'county_code' => !empty($pdfUpload->county_code),
            'filename' => !empty($pdfUpload->filename),
            'name' => !empty($pdfUpload->name),
            'storage_path' => !empty($pdfUpload->storage_path),
            'status' => !empty($pdfUpload->status),
            'total_pages' => !empty($pdfUpload->total_pages) && $pdfUpload->total_pages > 0,
        ];
        
        $completedFields = count(array_filter($fields));
        return $completedFields > 0 ? round(($completedFields / count($fields)) * 100) : 0;
    }

    private function getPdfPageCount($file)
    {
        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($file->getRealPath());
            return $pageCount;
        } catch (\Exception $e) {
            $content = file_get_contents($file->getRealPath());
            preg_match_all("/\/Page\W/", $content, $matches);
            return count($matches[0]) ?: 1;
        }
    }
    
    private function getPdfPageCountFromPath($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                return 1;
            }
            
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($filePath);
            return $pageCount;
        } catch (\Exception $e) {
            $content = file_get_contents($filePath);
            preg_match_all("/\/Page\W/", $content, $matches);
            return count($matches[0]) ?: 1;
        }
    }
    
  
    private function generatePdfThumbnail($pdfUpload)
    {
        try {
            if (!extension_loaded('imagick') || !class_exists('Imagick')) {
                Log::warning('Imagick not available for thumbnail generation');
                return;
            }
            
            $pdfPath = Storage::disk('public')->path($pdfUpload->storage_path);
            
            if (!file_exists($pdfPath)) {
                Log::error('PDF file not found for thumbnail generation: ' . $pdfPath);
                return;
            }
            
            $thumbnailPath = 'thumbnails/' . $pdfUpload->id . '_' . Str::random(10) . '.png';
            
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($pdfPath . '[0]');
            $imagick->setImageFormat('png');
            $imagick->setImageBackgroundColor('white');
            $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $imagick->thumbnailImage(300, 300, true, true);
            
            Storage::disk('public')->put($thumbnailPath, $imagick->getImageBlob());
            
            $pdfUpload->update(['thumbnail_path' => $thumbnailPath]);
            
            Log::info('Thumbnail generated for PDF: ' . $pdfUpload->id);
            
        } catch (\Exception $e) {
            Log::error('Thumbnail generation failed: ' . $e->getMessage());
            $this->createPlaceholderThumbnail($pdfUpload);
        }
    }

    private function createPlaceholderThumbnail($pdfUpload)
    {
        try {
            $thumbnailPath = 'thumbnails/' . $pdfUpload->id . '_placeholder.png';
            
            $image = imagecreate(300, 300);
            $backgroundColor = imagecolorallocate($image, 240, 240, 240);
            $textColor = imagecolorallocate($image, 150, 150, 150);
            
            imagestring($image, 5, 120, 120, 'PDF', $textColor);
            imagestring($image, 3, 100, 150, 'Page 1', $textColor);
            
            imagepng($image, Storage::disk('public')->path($thumbnailPath));
            imagedestroy($image);
            
            $pdfUpload->update(['thumbnail_path' => $thumbnailPath]);
            
        } catch (\Exception $e) {
            Log::error('Placeholder thumbnail creation failed: ' . $e->getMessage());
        }
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    private function getMonthsList()
    {
        return [
            '01' => 'January', '02' => 'February', '03' => 'March',
            '04' => 'April', '05' => 'May', '06' => 'June',
            '07' => 'July', '08' => 'August', '09' => 'September',
            '10' => 'October', '11' => 'November', '12' => 'December'
        ];
    }
    
    public function getStatistics()
    {
        $cardData = $this->getOverviewCardData();
        
        $pdfsByStatus = PdfUpload::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        $uploadsByMonth = PdfUpload::selectRaw('COUNT(*) as count, DATE_FORMAT(created_at, "%Y-%m") as month')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $uploadsByCounty = County::withCount('pdfUploads')
            ->orderBy('pdf_uploads_count', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'card_data' => $cardData,
            'by_status' => $pdfsByStatus,
            'by_month' => $uploadsByMonth,
            'by_county' => $uploadsByCounty,
            'average_completion' => $cardData['completion_rate'],
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pdf_uploads,id',
            'status' => 'required|in:active,inactive,processing,archived',
        ]);
        
        $count = PdfUpload::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);
        
        return response()->json([
            'success' => true,
            'message' => "Status updated for {$count} PDF(s)",
            'updated_count' => $count,
        ]);
    }

    public function getUserUploads($userId)
    {
        $user = User::findOrFail($userId);
        
        $uploads = PdfUpload::where('uploaded_by', $userId)
            ->with('county')
            ->latest()
            ->paginate(10);
        
        $userStats = [
            'total_uploads' => PdfUpload::where('uploaded_by', $userId)->count(),
            'total_storage' => $this->formatBytes(PdfUpload::where('uploaded_by', $userId)->sum('file_size')),
            'average_completion' => $this->calculateUserAverageCompletion($userId),
            'last_upload' => PdfUpload::where('uploaded_by', $userId)->latest()->first(),
        ];
        
        return view('pdf-uploads.user-uploads', compact('user', 'uploads', 'userStats'));
    }
    
    private function calculateUserAverageCompletion($userId)
    {
        $pdfs = PdfUpload::where('uploaded_by', $userId)->get();
        
        if ($pdfs->isEmpty()) {
            return 0;
        }
        
        $totalCompletion = 0;
        foreach ($pdfs as $pdf) {
            $totalCompletion += $this->calculatePdfCompletionRate($pdf);
        }
        
        return round($totalCompletion / $pdfs->count(), 1);
    }

    public function saveQuickData(Request $request, PdfPage $page)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'certificate_serial' => 'required|string|max:255',
        ]);
        
        $page->update([
            'quick_data' => json_encode($validated),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Quick data saved successfully',
            'data' => $validated
        ]);
    }

    public function showLinkedMarriages(PdfUpload $pdfUpload)
    {
        $marriagesViaPdfId = $pdfUpload->marriages()->with(['spouses', 'witnesses', 'createdBy'])->get();
        
        $marriagesViaPdfPages = Marriage::whereHas('pdfPage', function($query) use ($pdfUpload) {
            $query->where('pdf_upload_id', $pdfUpload->id);
        })->with(['spouses', 'witnesses', 'createdBy', 'pdfPage'])->get();
        
        $allMarriages = $marriagesViaPdfId->merge($marriagesViaPdfPages);
        
        $stats = [
            'total_marriages' => $allMarriages->count(),
            'via_pdf_id' => $marriagesViaPdfId->count(),
            'via_pdf_pages' => $marriagesViaPdfPages->count(),
            'completed_pages' => $pdfUpload->completedPagesCount(),
            'pending_pages' => $pdfUpload->pendingPagesCount(),
            'total_pages' => $pdfUpload->total_pages,
        ];
        
        return view('pdf-uploads.linked-marriages', compact('pdfUpload', 'allMarriages', 'stats'));
    }

    public function showRelationships(PdfUpload $pdfUpload)
    {
        $pdfUpload->load([
            'pages.marriage.spouses',
            'pages.marriage.witnesses',
            'pages.marriage.createdBy',
            'pages.assignedUser',
            'marriages.spouses',
            'marriages.witnesses',
            'marriages.createdBy',
            'county'
        ]);
        
        $stats = [
            'total_pages' => $pdfUpload->total_pages,
            'pages_with_marriages' => $pdfUpload->pages->whereNotNull('marriage')->count(),
            'pages_without_marriages' => $pdfUpload->pages->whereNull('marriage')->count(),
            'pages_completed' => $pdfUpload->pages->where('status', 'Completed')->count(),
            'pages_pending' => $pdfUpload->pages->where('status', 'pending')->count(),
            'pages_processing' => $pdfUpload->pages->where('status', 'processing')->count(),
            'marriages_via_pdf_id' => $pdfUpload->marriages->count(),
            'marriages_via_pdf_pages' => $pdfUpload->pages->filter(fn($page) => $page->marriage)->count(),
            'total_marriages' => $pdfUpload->marriages->count() + $pdfUpload->pages->filter(fn($page) => $page->marriage)->count(),
        ];
        
        $pagesByStatus = $pdfUpload->pages->groupBy('status');
        
        $pagesWithMarriages = $pdfUpload->pages->filter(fn($page) => $page->marriage);
        
        $pagesWithoutMarriages = $pdfUpload->pages->filter(fn($page) => !$page->marriage);
        
        return view('pdf-uploads.relationships', compact(
            'pdfUpload', 
            'stats', 
            'pagesByStatus',
            'pagesWithMarriages',
            'pagesWithoutMarriages'
        ));
    }

    public function showAllRelationships(Request $request)
    {
        $query = PdfUpload::withCount([
            'pages',
            'pages as pages_with_marriages_count' => function($query) {
                $query->whereHas('marriage');
            },
            'marriages',
            'pages as completed_pages_count' => function($query) {
                $query->where('status', 'Completed');
            }
        ]);
        
        if ($request->filled('county_code')) {
            $query->where('county_code', $request->county_code);
        }
        
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('county_code', 'like', "%{$request->search}%")
                  ->orWhereHas('county', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
            });
        }
        
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortColumn, ['marriages_count', 'pages_with_marriages_count', 'completion_rate'])) {
            if ($sortColumn === 'completion_rate') {
                $query->orderByRaw('(completed_pages_count * 100.0 / total_pages) ' . $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $query->orderBy($sortColumn, $sortDirection);
        }
        
        $perPage = $request->get('per_page', 20);
        $pdfUploads = $query->paginate($perPage)->withQueryString();
        
        $pdfUploads->getCollection()->transform(function($pdfUpload) {
            $pdfUpload->completion_rate = $pdfUpload->total_pages > 0 
                ? round(($pdfUpload->completed_pages_count / $pdfUpload->total_pages) * 100, 1)
                : 0;
            $pdfUpload->marriage_link_rate = $pdfUpload->total_pages > 0
                ? round(($pdfUpload->pages_with_marriages_count / $pdfUpload->total_pages) * 100, 1)
                : 0;
            return $pdfUpload;
        });
        
        $counties = County::orderBy('name')->get();
        $years = PdfUpload::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $statuses = ['active', 'processing', 'archived', 'inactive'];
        
        $overallStats = [
            'total_pdfs' => PdfUpload::count(),
            'total_pages' => PdfUpload::sum('total_pages'),
            'total_marriages' => Marriage::count(),
            'marriages_via_pdf_id' => Marriage::whereNotNull('pdf_id')->count(),
            'marriages_via_pdf_pages' => Marriage::whereNotNull('pdf_page_id')->count(),
            'avg_completion_rate' => PdfUpload::where('total_pages', '>', 0)->avg(DB::raw('(
                SELECT COUNT(*) FROM pdf_pages 
                WHERE pdf_pages.pdf_upload_id = pdf_uploads.id 
                AND pdf_pages.status = "completed"
            ) * 100.0 / total_pages')) ?? 0,
        ];
        
        return view('pdf-uploads.all-relationships', compact(
            'pdfUploads', 
            'counties', 
            'years', 
            'statuses',
            'overallStats'
        ));
    }
    
    public function quickCreateFromPage(Request $request)
    {
        // ==================== DEBUG STAGE 1: RAW REQUEST ====================
        Log::info('========== 🔍 QUICK CREATE DEBUG START ==========');
        Log::info('STAGE 1 - RAW REQUEST DATA:', [
            'all_input' => $request->all(),
            'has_ward_id' => $request->has('ward_id'),
            'ward_id_raw_value' => $request->input('ward_id'),
            'ward_id_type' => gettype($request->input('ward_id')),
            'certificate_serial' => $request->input('certificate_serial'),
            'sub_county' => $request->input('sub_county'),
            'marriage_date' => $request->input('marriage_date'),
            'reg_date' => $request->input('reg_date'),
            'venue' => $request->input('venue'),
        ]);
        
        $user = Auth::user();

        // ==================== DEBUG STAGE 2: USER AUTH ====================
        Log::info('STAGE 2 - USER CHECK:', [
            'user_id' => $user->id,
            'user_role' => $user->role->name ?? 'unknown',
            'is_teller' => $user->isTeller(),
            'is_clerk' => $user->isDataClerk(),
            'is_admin' => $user->isAdmin(),
            'is_registrar' => $user->isRegistrar()
        ]);

        if (! $user->isTeller() && ! $user->isDataClerk() && ! $user->isRegistrar() && ! $user->isAdmin()) {
            Log::error('STAGE 2 - AUTHORIZATION FAILED');
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        // ==================== DEBUG STAGE 3: VALIDATION ====================
        try {
            Log::info('STAGE 3 - STARTING VALIDATION');
            
            $validated = $request->validate([
                'pdf_page_id'        => 'required|exists:pdf_pages,id',
                'pdf_id'             => 'required|exists:pdf_uploads,id',
                'certificate_serial' => 'required|string|max:255', 
                'marriage_date'      => 'required|date',
                'reg_date'           => 'required|date',
                'venue'              => 'required|string|max:255',
                'sub_county'         => 'required|string|max:255',
                'ward_id'            => 'nullable|exists:counties,id',
                'county'             => 'required|string|max:255',
                'year'               => 'required|integer',
                'month'              => 'required|integer',
                'marriage_type_id'   => 'required|exists:categories,id',
                'notes'              => 'nullable|string|max:1000',
            ]);
            
            Log::info('STAGE 3 - VALIDATION PASSED', [
                'validated_keys' => array_keys($validated),
                'ward_id_validated' => $validated['ward_id'] ?? 'NULL',
                'ward_id_exists_in_validated' => isset($validated['ward_id']),
                'certificate_serial_validated' => $validated['certificate_serial']
            ]);

            // ==================== DEBUG STAGE 4: WARD ID EXISTS CHECK ====================
            if (isset($validated['ward_id']) && !empty($validated['ward_id'])) {
                $wardExists = County::where('id', $validated['ward_id'])->exists();
                Log::info('STAGE 4 - WARD ID VALIDATION:', [
                    'ward_id' => $validated['ward_id'],
                    'exists_in_counties_table' => $wardExists ? 'YES ✅' : 'NO ❌'
                ]);
                
                if (!$wardExists) {
                    // Get sample counties for debugging
                    $sampleCounties = County::select('id', 'wards', 'constituency')
                        ->whereNotNull('wards')
                        ->limit(5)
                        ->get()
                        ->toArray();
                    Log::info('STAGE 4 - SAMPLE VALID WARD IDs:', $sampleCounties);
                }
            } else {
                Log::info('STAGE 4 - WARD ID NOT PROVIDED OR EMPTY');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ STAGE 3 - VALIDATION FAILED:', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // ==================== DEBUG STAGE 5: FIND PAGE ====================
            $page = PdfPage::with(['pdfUpload'])->lockForUpdate()->findOrFail($validated['pdf_page_id']);
            
            Log::info('STAGE 5 - PAGE FOUND:', [
                'page_id' => $page->id,
                'pdf_upload_id' => $page->pdfUpload->id,
                'year_from_page' => $page->pdfUpload->year,
                'month_from_page' => $page->pdfUpload->month,
                'marriage_type_id_from_page' => $page->pdfUpload->marriage_type_id,
                'page_status' => $page->status,
                'already_has_marriage' => $page->marriage ? 'YES' : 'NO'
            ]);

            if ($page->marriage) {
                Log::error('STAGE 5 - PAGE ALREADY HAS MARRIAGE:', ['marriage_id' => $page->marriage->id]);
                return redirect()->back()->with('error', 'This PDF page is already linked to a marriage record.');
            }

            // ==================== DEBUG STAGE 6: PRE-SERVICE DATA ====================
            Log::info('STAGE 6 - DATA BEING PASSED TO SERVICE:', [
                'pdf_page_id' => $validated['pdf_page_id'],
                'pdf_id' => $validated['pdf_id'],
                'certificate_serial' => $validated['certificate_serial'],
                'marriage_date' => $validated['marriage_date'],
                'reg_date' => $validated['reg_date'],
                'venue' => $validated['venue'],
                'sub_county' => $validated['sub_county'],
                'ward_id' => $validated['ward_id'] ?? 'NULL',
                'county' => $validated['county'],
                'year' => $validated['year'],
                'month' => $validated['month'],
                'marriage_type_id' => $validated['marriage_type_id'],
                'notes' => $validated['notes'] ?? 'NULL',
                'user_id' => $user->id
            ]);

            // ==================== DEBUG STAGE 7: CALL SERVICE ====================
            Log::info('STAGE 7 - CALLING createQuickEntry');
            $marriage = $this->marriageService->createQuickEntry($validated, $page, $user);

            // ==================== DEBUG STAGE 8: VERIFY SAVED DATA ====================
            // Refresh from database to get actual saved values
            $freshMarriage = Marriage::with(['spouses', 'county'])
                ->find($marriage->id);
            
            Log::info('✅ STAGE 8 - MARRIAGE SAVED SUCCESSFULLY:', [
                'marriage_id' => $freshMarriage->id,
                'certificate_serial' => $freshMarriage->certificate_serial,
                'ward_id_saved' => $freshMarriage->ward_id,
                'ward_id_is_null' => is_null($freshMarriage->ward_id) ? 'YES' : 'NO',
                'ward_id_value' => $freshMarriage->ward_id,
                'county_saved' => $freshMarriage->county,
                'sub_county_saved' => $freshMarriage->sub_county,
                'marriage_date' => $freshMarriage->marriage_date,
                'reg_date' => $freshMarriage->reg_date,
                'venue' => $freshMarriage->venue,
                'pdf_page_id' => $freshMarriage->pdf_page_id,
                'pdf_id' => $freshMarriage->pdf_id,
                'year_saved' => $freshMarriage->year,
                'month_saved' => $freshMarriage->month,
                'marriage_type_id' => $freshMarriage->marriage_type_id,
                'marriage_status_id' => $freshMarriage->marriage_status_id,
                'verification_status_id' => $freshMarriage->verification_status_id,
                'system_status' => $freshMarriage->system_status,
                'created_by' => $freshMarriage->created_by,
                'created_at' => $freshMarriage->created_at,
                'all_fillable_fields' => $freshMarriage->only($freshMarriage->getFillable())
            ]);

            // ==================== DEBUG STAGE 9: CHECK ALL DATABASE FIELDS ====================
            $dbRecord = DB::table('marriages')->where('id', $freshMarriage->id)->first();
            Log::info('STAGE 9 - RAW DATABASE RECORD:', (array) $dbRecord);

            DB::commit();

            Log::info('========== 🔍 QUICK CREATE DEBUG END ==========');

            return redirect()->route('pdf-uploads.show', $page->id)
                ->with('success', 'Marriage record created and linked successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('❌ STAGE 8 - EXCEPTION CAUGHT:', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create marriage record: ' . $e->getMessage());
        }
    }

    public function fullCreateFromPage(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role->name, ['data_clerk','marriage_teller', 'admin', 'marriage_registrar'])) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Check if validation should be bypassed
        $skipValidation = $request->input('skip_validation', false);
        
        // Only validate if not skipping validation
        if (!$skipValidation) {
            $validated = $request->validate([
                'certificate_serial' => 'required|string|max:255',
                'venue' => 'required|string|max:255',
                'marriage_date' => 'required|date',
                'sub_county' => 'required|string|max:255',
                'ward_id' => 'required|exists:counties,id',
                'husband_name' => 'required|string|max:255',
                'husband_age' => 'required|integer|min:18|max:120',
                'wife_name' => 'required|string|max:255',
                'wife_age' => 'required|integer|min:18|max:120',
                'witness1_name' => 'required|string|max:255',
                'witness2_name' => 'required|string|max:255',
                'pdf_page_id' => 'required|exists:pdf_pages,id',
                'pdf_id' => 'required|exists:pdf_uploads,id',
                'marriage_type_id' => 'required|exists:categories,id',
            ]);
        } else {
            // When skipping validation, only validate existence of required relationships
            $validated = $request->validate([
                'pdf_page_id' => 'required|exists:pdf_pages,id',
                'pdf_id' => 'required|exists:pdf_uploads,id',
                'marriage_type_id' => 'required|exists:categories,id',
            ]);
        }

        try {
            DB::beginTransaction();
            
            $page = PdfPage::with(['pdfUpload'])->lockForUpdate()->findOrFail($request->pdf_page_id);
            
            if ($page->marriage) {
                return redirect()->back()->with('error', 'This PDF page is already linked to a marriage record.');
            }
            
            // Prepare data for service
            $data = $request->all();
            $data['skip_validation'] = $skipValidation;
            
            // Create marriage using service
            $marriage = $this->marriageService->createFullEntry($data, $page, $user);
            
            DB::commit();
            
            return redirect()->route('pdf-uploads.show', $page->id)
                ->with('success', $skipValidation ? 'Marriage record created with validation bypass. Status: Under Review' : 'Marriage record created successfully with full details.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FullCreateMarriage failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create marriage record: ' . $e->getMessage());
        }
    }

    public function quickUpdate(Request $request, Marriage $marriage)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'marriage_teller') {
            return response()->json(['success' => false, 'message' => 'Only marriage tellers can update marriages.'], 403);
        }

        if ($marriage->created_by !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You can only edit your own records.'], 403);
        }

        $validated = $request->validate([
            'certificate_serial' => 'required|string|max:255|unique:marriages,certificate_serial,' . $marriage->id,
            'marriage_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'husband_name' => 'nullable|string|max:255',
            'wife_name' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $marriage->update([
                'certificate_serial' => strtoupper($validated['certificate_serial']),
                'marriage_date' => $validated['marriage_date'],
                'venue' => strtoupper($validated['venue']),
                'county' => strtoupper($validated['county']),
                'updated_by' => $user->id,
            ]);

            $husband = $marriage->spouses()->where('spouse_type', 'husband')->first();
            if (!empty($validated['husband_name'])) {
                if ($husband) {
                    $husband->update(['name' => strtoupper($validated['husband_name'])]);
                } else {
                    $marriage->spouses()->create([
                        'name' => strtoupper($validated['husband_name']),
                        'spouse_type' => 'husband',
                        'created_by' => $user->id,
                    ]);
                }
            }

            $wife = $marriage->spouses()->where('spouse_type', 'wife')->first();
            if (!empty($validated['wife_name'])) {
                if ($wife) {
                    $wife->update(['name' => strtoupper($validated['wife_name'])]);
                } else {
                    $marriage->spouses()->create([
                        'name' => strtoupper($validated['wife_name']),
                        'spouse_type' => 'wife',
                        'created_by' => $user->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Marriage record updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update marriage record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function assignPage(PdfPage $page)
    {
        if ($page->status !== 'pending') {
            return response()->json(['error' => 'Page is not available for assignment'], 400);
        }
        
        $page->update([
            'assigned_to' => auth()->id(),
            'status' => 'Processing',
            'started_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Page assigned successfully',
            'page' => $page->load('assignedUser')
        ]);
    }

    public function completePage(Request $request, PdfPage $page)
    {
        if ($page->assigned_to !== auth()->id()) {
            return response()->json(['error' => 'You are not assigned to this page'], 400);
        }
        
        $page->update([
            'status' => 'Completed',
            'data_entry_by' => auth()->id(),
            'completed_at' => now(),
            'notes' => $request->notes ?? $page->notes,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Page marked as complete',
            'page' => $page->fresh()
        ]);
    }

    public function getWards(Request $request)
    {
        $constituency = $request->get('constituency');
        $countyCode = $request->get('county_code');
        
        if (!$constituency || !$countyCode) {
            return response()->json([]);
        }
        
        $wards = County::where('county_code', $countyCode)
            ->where('constituency', $constituency)
            ->whereNotNull('wards')
            ->where('wards', '!=', '')
            ->select('id', 'wards', 'constituency')
            ->orderBy('wards')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->wards,  // Change 'wards' to 'name' for clarity
                    'wards' => $item->wards,  // Keep both for compatibility
                    'constituency' => $item->constituency
                ];
            })
            ->toArray();
        
        return response()->json($wards);
    }

    public function getReligiousInstitutions(Request $request)
    {
        $marriageTypeId = $request->get('marriage_type_id');
        $countyCode = $request->get('county_code');
        
        return response()->json([]);
    }


}
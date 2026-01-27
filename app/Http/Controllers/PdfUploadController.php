<?php

namespace App\Http\Controllers;

use App\Models\PdfUpload;
use App\Models\PdfPage;
use App\Models\County;
use App\Models\User;
use App\Models\Marriage;
use App\Models\Category;
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

class PdfUploadController extends Controller
{
    public function index(Request $request)
    {
        $pdfPages = PdfPage::with(['pdfUpload.uploader', 'pdfUpload.county', 'pdfUpload.marriageType'])
            ->get();
        
        $years = PdfUpload::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $counties = County::select('county_code', 'name')
            ->whereNotNull('county_code')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get()
            ->unique('county_code')
            ->values();

        $statuses = ['active', 'processing', 'archived', 'inactive'];

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            $months[$monthNum] = date('F', mktime(0, 0, 0, $i, 1));
        }
        
        $cardData = $this->getOverviewCardData();

        $marriageTypes = Category::where('type', 'marriage_type')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return view('pdf-uploads.index', compact(
            'pdfPages',
            'years', 
            'counties', 
            'statuses',
            'months',
            'cardData', 
            'marriageTypes'
        ));
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
            'year' => 'required|digits:4|integer|min:2000|max:' . date('Y'),
            'month' => 'required|string|max:20',
            'county_code' => 'required|string|max:10',
            'pdf_file' => 'required|file|mimes:pdf|max:102400',
            'marriage_type_id' => 'nullable|exists:categories,id',
        ]);

        try {
            $file = $request->file('pdf_file');

            $fileHash = hash_file('sha256', $file->getRealPath());

            $existing = PdfUpload::where('file_hash', $fileHash)->first();

            if ($existing) {
                return back()->withErrors([
                    'pdf_file' => 'This PDF has already been uploaded. Duplicate files are not allowed.',
                ]);
            }

            // Generate random 40-character filename + .pdf extension
            $randomFilename = Str::random(40) . '.pdf';

            $path = 'pdf-uploads/' . $request->year . '/'
                . str_pad($request->month, 2, '0', STR_PAD_LEFT) . '/'
                . $randomFilename;

            Storage::disk('public')->put(
                $path,
                file_get_contents($file->getRealPath())
            );

            $pageCount = $this->getPdfPageCount($file);

            $pdfUpload = PdfUpload::create([
                'year' => $request->year,
                'month' => str_pad($request->month, 2, '0', STR_PAD_LEFT), // Store as padded
                'county_code' => $request->county_code,
                'filename' => $randomFilename, // Random filename
                'file_size' => $file->getSize(),
                'name' => $file->getClientOriginalName(), // Original name for display
                'storage_path' => $path,
                'total_pages' => $pageCount,
                'file_hash' => $fileHash,
                'uploaded_by' => auth()->id(),
                'marriage_type_id' => $request->marriage_type_id,
                'status' => 'uploaded',
                'uuid' => Str::uuid()->toString(),
            ]);

            $this->createPdfPages($pdfUpload, $pageCount);

            // Generate thumbnail
            $this->generatePdfThumbnail($pdfUpload);

            return redirect()
                ->route('pdf-uploads.show', $pdfUpload)
                ->with('success', "PDF uploaded with {$pageCount} pages. Ready for data entry.");

        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
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

            // Generate random filename like the regular uploads (e.g., "hAK8tFaHhNUBXAEkjjp40u92MTsBhV171hPGwwde.pdf")
            // 40 characters random string + .pdf extension
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
                // Fallback for systems without mime_content_type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $finalFilePath);
                finfo_close($finfo);
            } else {
                $mimeType = mime_content_type($finalFilePath);
            }

            if ($mimeType !== 'application/pdf') {
                Log::error('Invalid MIME type for assembled file: ' . $mimeType);
                // Try to check if it's at least a binary file
                $fileContent = file_get_contents($finalFilePath, false, null, 0, 5);
                if (strpos($fileContent, '%PDF-') === 0) {
                    Log::info('File appears to be PDF despite MIME type');
                } else {
                    // Clean up invalid file
                    unlink($finalFilePath);
                    throw new \Exception('The uploaded file is not a valid PDF (MIME: ' . $mimeType . ')');
                }
            }

            // Generate file hash
            $fileHash = hash_file('sha256', $finalFilePath);
            
            // Check for duplicate files
            $existing = PdfUpload::where('file_hash', $fileHash)->first();
            if ($existing) {
                // Clean up
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
                $pageCount = 1; // Default to 1 if we can't determine
            }
            
            // Generate UUID
            $uuid = Str::uuid()->toString();
            
            // Create PDF record with random filename
            $pdfUpload = PdfUpload::create([
                'year' => $year,
                'month' => $month, // Store as padded number (e.g., "07" not "7")
                'county_code' => $request->county_code,
                'filename' => $randomFilename, // Random filename like other records
                'file_size' => $totalSize,
                'name' => $originalName, // Original name for display
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

            // Generate thumbnail (optional but recommended)
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

            // Clean up on error
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

        $marriageStatusId = Category::where('type', 'marriage_status')
            ->where('name', 'Completed')
            ->value('id') ?? null;

        $verificationStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id') ?? null;

        $marriageTypes = Category::where('type', 'marriage_type')
            ->orderBy('name')
            ->get();
        
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
            'marriageStatusId',
            'verificationStatusId',
            'marriageTypes',
            'month'
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
            if (!extension_loaded('imagick') || !class_exists('Imagick')) {
                Log::warning('Imagick not available for page extraction');
                return $this->extractPageAsPdf($pdfUpload, $pageNumber);
            }
            
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $pdfPath = Storage::disk('public')->path($pdfUpload->storage_path);
            $imagick->readImage($pdfPath . '[' . ($pageNumber - 1) . ']');
            $imagick->setImageFormat('png');
            $imagick->setImageBackgroundColor('white');
            $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            
            return base64_encode($imagick->getImageBlob());
        } catch (\Exception $e) {
            Log::error('Error extracting page for display: ' . $e->getMessage());
            return $this->extractPageAsPdf($pdfUpload, $pageNumber);
        }
    }

    private function extractPageAsPdf(PdfUpload $pdfUpload, $pageNumber)
    {
        try {
            $pdf = new Fpdi();
            $pdfPath = Storage::disk('public')->path($pdfUpload->storage_path);
            
            $pageCount = $pdf->setSourceFile($pdfPath);
            
            if ($pageNumber > $pageCount) {
                return null;
            }
            
            $templateId = $pdf->importPage($pageNumber);
            
            $size = $pdf->getTemplateSize($templateId);
            
            $maxWidth = 210;
            $maxHeight = 297;
            
            $originalWidth = $size['width'];
            $originalHeight = $size['height'];
            
            $scale = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
            
            if ($scale > 1) {
                $scale = 1;
            }
            
            $scaledWidth = $originalWidth * $scale;
            $scaledHeight = $originalHeight * $scale;
            
            $x = ($maxWidth - $scaledWidth) / 2;
            $y = ($maxHeight - $scaledHeight) / 2;
            
            $pdf->AddPage();
            
            $pdf->useTemplate(
                $templateId, 
                $x,
                $y,
                $scaledWidth,
                $scaledHeight,
                true
            );
            
            $pageContent = $pdf->Output('S');
            
            return 'data:application/pdf;base64,' . base64_encode($pageContent);
            
        } catch (\Exception $e) {
            Log::error('Error extracting page as PDF: ' . $e->getMessage());
            return null;
        }
    }

    // Removed uploadLargeFile and processUpload methods

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
            'year' => 'required|digits:4|integer|min:2000|max:' . date('Y'),
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
            // Delete the PDF file from storage
            Storage::disk('public')->delete($pdfUpload->storage_path);
            
            // Delete thumbnail if exists
            if ($pdfUpload->thumbnail_path) {
                Storage::disk('public')->delete($pdfUpload->thumbnail_path);
            }
            
            // Delete all related records using relationships
            $pdfUpload->pdfPages()->delete();
            $pdfUpload->marriages()->delete();
            
            // Delete the pdfUpload record
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
        $totalRecords = PdfPage::count();
        $todayRecords = PdfPage::whereDate('created_at', today())->count();
        
        $totalStorageBytes = PdfUpload::sum('file_size');
        $storageUsedMB = round($totalStorageBytes / (1024 * 1024), 2);
        $storageUsedFormatted = $storageUsedMB . ' MB';
        
        $storageRecorded = $this->getStorageRecorded();
        
        $totalPDFs = PdfUpload::count();
        $linkedPDFs = PdfUpload::whereHas('pages')->count();
        
        $storageLimitMB = 1024;
        $storagePercentage = $totalPDFs > 0 ? round(($storageUsedMB / $storageLimitMB) * 100, 1) : 0;
        
        $dataClerksCount = User::whereHas('role', function($query) {
            $query->where('name', 'data_clerk');
        })->count();
        
        return [
            'total_records' => number_format($totalRecords),
            'today_records' => number_format($todayRecords),
            'storage_used_formatted' => $storageUsedFormatted,
            'storage_used_mb' => $storageUsedMB,
            'storage_percentage' => $storagePercentage,
            'storage_recorded' => $storageRecorded,
            'linked_pdfs' => number_format($linkedPDFs),
            'total_pdfs' => number_format($totalPDFs),
            'data_clerks' => number_format($dataClerksCount),
            'completion_rate' => $totalPDFs > 0 ? round(($linkedPDFs / $totalPDFs) * 100, 1) : 0,
        ];
    }

    private function getStorageRecorded()
    {
        $totalStorageBytes = PdfUpload::sum('file_size');
        $storageUsedMB = round($totalStorageBytes / (1024 * 1024), 2);
        $storageLimitGB = 1;
        
        return $storageUsedMB . ' MB / ' . $storageLimitGB . ' GB';
    }
    
    private function calculateAverageCompletionRate()
    {
        $pdfs = PdfUpload::all();
        
        if ($pdfs->isEmpty()) {
            return 0;
        }
        
        $totalCompletion = 0;
        foreach ($pdfs as $pdf) {
            $totalCompletion += $this->calculatePdfCompletionRate($pdf);
        }
        
        return round($totalCompletion / $pdfs->count(), 1);
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
        Log::info('🎯 QUICK CREATE METHOD CALLED!', [
            'time' => now(),
            'user' => auth()->id(),
            'data' => $request->all()
        ]);
        
        $user = Auth::user();

        if (! $user->isTeller() && ! $user->isDataClerk() && ! $user->isAdmin()) {
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'pdf_page_id'        => 'required|exists:pdf_pages,id',
            'pdf_id'             => 'required|exists:pdf_uploads,id',
            'certificate_serial' => 'required|string|max:255|unique:marriages,certificate_serial',
            'marriage_date'      => 'required|date',
            'reg_date'           => 'required|date',
            'venue'              => 'required|string|max:255',
            'sub_county'         => 'required|string|max:255',
            'county'             => 'required|string|max:255',
            'year'               => 'required|integer',
            'month'              => 'required|integer',
            'marriage_type_id'   => 'required|exists:categories,id',
            'notes'              => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $page = PdfPage::with(['pdfUpload', 'marriage'])->lockForUpdate()->findOrFail($validated['pdf_page_id']);

            if ($page->marriage) {
                return redirect()->back()->with('error', 'This PDF page is already linked to a marriage record.');
            }

            $marriageStatusId = Category::where('type', 'marriage_status')
                ->where('name', 'Completed')
                ->value('id');

            $verificationStatusId = Category::where('type', 'verification_status')
                ->where('name', 'Unverified')
                ->value('id');

            if (!$marriageStatusId || !$verificationStatusId) {
                Log::error('Category IDs not found', [
                    'marriage_status_id' => $marriageStatusId,
                    'verification_status_id' => $verificationStatusId,
                ]);
                
                return redirect()->back()->with('error', 'Required status categories not found in database.');
            }

            $marriage = Marriage::create([
                'certificate_serial'     => strtoupper($validated['certificate_serial']),
                'marriage_date'          => $validated['marriage_date'],
                'reg_date'               => $validated['reg_date'],
                'venue'                  => strtoupper($validated['venue']),
                'county'                 => strtoupper($validated['county']),
                'sub_county'             => strtoupper($validated['sub_county']),
                'pdf_page_id'            => $page->id,
                'pdf_id'                 => $page->pdfUpload->id,
                'year'                   => $validated['year'],
                'month'                  => $validated['month'],
                'marriage_type_id'       => $validated['marriage_type_id'],
                'marriage_status_id'     => $marriageStatusId,
                'verification_status_id' => $verificationStatusId,
                'system_status'          => 'Pending',
                'created_by'             => $user->id,
                'updated_by'             => $user->id,
            ]);

            $page->update([
                'status'        => 'completed',
                'completed_by' => $user->id,
                'completed_at'  => now(),
                'notes'         => $validated['notes'] ?? null,
            ]);

            DB::commit();

            Log::info('Marriage created successfully', [
                'marriage_id' => $marriage->id,
                'user_id' => $user->id,
            ]);

            return redirect()->route('pdf-uploads.show', $page->id)
                ->with('success', 'Marriage record created and linked successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('QuickCreateMarriage failed', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
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

            $husband = $marriage->spouses()->where('gender', 'male')->first();
            if (!empty($validated['husband_name'])) {
                if ($husband) {
                    $husband->update(['name' => strtoupper($validated['husband_name'])]);
                } else {
                    $marriage->spouses()->create([
                        'name' => strtoupper($validated['husband_name']),
                        'gender' => 'male',
                        'created_by' => $user->id,
                    ]);
                }
            }

            $wife = $marriage->spouses()->where('gender', 'female')->first();
            if (!empty($validated['wife_name'])) {
                if ($wife) {
                    $wife->update(['name' => strtoupper($validated['wife_name'])]);
                } else {
                    $marriage->spouses()->create([
                        'name' => strtoupper($validated['wife_name']),
                        'gender' => 'female',
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
}
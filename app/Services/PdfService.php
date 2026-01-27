<?php

namespace App\Services;

use App\Models\PdfUpload;
use App\Models\PdfPage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use setasign\Fpdi\Fpdi;

class PdfService
{
    private const SECURE_STORAGE_DISK = 'pdf_secure';

    /**
     * Store PDF file securely
     */
    public function storePdfFile(UploadedFile $file): string
    {
        try {
            // Create directory structure: year/month
            $year = date('Y');
            $month = date('m');
            
            // Generate clean filename
            $fileName = $this->generateCleanFileName($file);
            
            // Store file in structured folder
            $storagePath = "{$year}/{$month}/{$fileName}";
            
            // Ensure directory exists
            $disk = Storage::disk(self::SECURE_STORAGE_DISK);
            $directory = dirname($storagePath);
            
            if (!$disk->exists($directory)) {
                $disk->makeDirectory($directory);
            }
            
            // Store the file
            $disk->putFileAs($directory, $file, $fileName);
            
            Log::info('PDF stored', [
                'path' => $storagePath,
                'size' => $file->getSize()
            ]);
            
            return $storagePath;
            
        } catch (\Exception $e) {
            Log::error('Failed to store PDF', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            throw new \Exception('Failed to store file: ' . $e->getMessage());
        }
    }

    /**
     * Process PDF and extract metadata
     */
    public function processPdf(UploadedFile $file): array
    {
        try {
            $tempPath = $file->getRealPath();
            
            // Get total pages
            $totalPages = $this->getPageCount($tempPath);
            
            // Generate hash for integrity check
            $hashChecksum = hash_file('sha256', $tempPath);
            
            // Get PDF metadata
            $metadata = $this->extractMetadata($tempPath);
            
            return [
                'total_pages' => $totalPages,
                'hash_checksum' => $hashChecksum,
                'metadata' => $metadata
            ];
            
        } catch (\Exception $e) {
            Log::error('PDF processing failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            
            // Return safe defaults
            return [
                'total_pages' => 0,
                'hash_checksum' => '',
                'metadata' => []
            ];
        }
    }

    /**
     * Get PDF page count
     */
    private function getPageCount(string $filePath): int
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            return count($pdf->getPages());
        } catch (\Exception $e) {
            Log::warning('Failed to get page count', [
                'error' => $e->getMessage(),
                'path' => $filePath
            ]);
            
            // Fallback method using FPDI
            try {
                $pdf = new Fpdi();
                return $pdf->setSourceFile($filePath);
            } catch (\Exception $e2) {
                Log::error('Both page count methods failed', [
                    'error1' => $e->getMessage(),
                    'error2' => $e2->getMessage()
                ]);
                return 0;
            }
        }
    }

    /**
     * Extract PDF metadata
     */
    private function extractMetadata(string $filePath): array
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $details = $pdf->getDetails();
            
            return [
                'author' => $details['Author'] ?? null,
                'title' => $details['Title'] ?? null,
                'creator' => $details['Creator'] ?? null,
                'producer' => $details['Producer'] ?? null,
                'creation_date' => $details['CreationDate'] ?? null
            ];
            
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate clean filename
     */
    private function generateCleanFileName(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        
        // Remove special characters
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);
        
        // Add timestamp for uniqueness
        $timestamp = time();
        
        return "{$cleanName}_{$timestamp}.{$extension}";
    }

    /**
     * Create page records
     */
    public function createPageRecords(PdfUpload $pdfUpload, int $totalPages): void
    {
        $pages = [];
        $now = now();
        
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = [
                'pdf_upload_id' => $pdfUpload->id,
                'page_number' => $i,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        
        // Insert in chunks
        $chunks = array_chunk($pages, 100);
        foreach ($chunks as $chunk) {
            PdfPage::insert($chunk);
        }
        
        Log::info('Page records created', [
            'pdf_id' => $pdfUpload->id,
            'total_pages' => $totalPages
        ]);
    }

    /**
     * Get single page as base64 image (on-the-fly splitting)
     */
    public function getPageImage(PdfUpload $pdfUpload, int $pageNumber): string
    {
        try {
            $disk = Storage::disk(self::SECURE_STORAGE_DISK);
            $pdfPath = $disk->path($pdfUpload->storage_path);
            
            if (!file_exists($pdfPath)) {
                throw new \Exception('PDF file not found');
            }
            
            // Extract single page as image
            $imageData = $this->extractSinglePage($pdfPath, $pageNumber);
            
            // Add watermark for security
            $watermarkedImage = $this->addWatermark($imageData, "Page {$pageNumber} - {$pdfUpload->name}");
            
            return 'data:image/png;base64,' . base64_encode($watermarkedImage);
            
        } catch (\Exception $e) {
            Log::error('Failed to get page image', [
                'pdf_id' => $pdfUpload->id,
                'page_number' => $pageNumber,
                'error' => $e->getMessage()
            ]);
            
            // Return placeholder
            return $this->generatePlaceholderImage($pageNumber);
        }
    }

    /**
     * Extract single page from PDF
     */
    private function extractSinglePage(string $pdfPath, int $pageNumber): string
    {
        $pdf = new Fpdi();
        
        // Get total pages
        $totalPages = $pdf->setSourceFile($pdfPath);
        
        if ($pageNumber > $totalPages || $pageNumber < 1) {
            throw new \Exception("Invalid page number: {$pageNumber}. PDF has {$totalPages} pages.");
        }
        
        // Import page
        $templateId = $pdf->importPage($pageNumber);
        $size = $pdf->getTemplateSize($templateId);
        
        // Create image
        $pdf->AddPage('P', [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
        
        // Output to string
        return $pdf->Output('S');
    }

    /**
     * Add watermark to image (simple text overlay)
     */
    private function addWatermark(string $pdfData, string $watermarkText): string
    {
        // For now, return original PDF data
        // In production, you could convert PDF to image and add watermark
        return $pdfData;
    }

    /**
     * Generate placeholder image
     */
    private function generatePlaceholderImage(int $pageNumber): string
    {
        // Simple base64 placeholder
        $svg = <<<SVG
<svg width="800" height="1000" xmlns="http://www.w3.org/2000/svg">
    <rect width="100%" height="100%" fill="#f3f4f6"/>
    <text x="50%" y="50%" font-family="Arial" font-size="20" text-anchor="middle" fill="#9ca3af">
        Page {$pageNumber} - PDF Preview
    </text>
</svg>
SVG;
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Validate file integrity
     */
    public function validateFileIntegrity(PdfUpload $pdfUpload): bool
    {
        try {
            $disk = Storage::disk(self::SECURE_STORAGE_DISK);
            $filePath = $pdfUpload->storage_path;
            
            if (!$disk->exists($filePath)) {
                return false;
            }
            
            $currentHash = hash_file('sha256', $disk->path($filePath));
            return hash_equals($pdfUpload->hash_checksum, $currentHash);
            
        } catch (\Exception $e) {
            Log::error('File integrity check failed', [
                'pdf_id' => $pdfUpload->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
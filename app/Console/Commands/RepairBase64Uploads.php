<?php

namespace App\Console\Commands;

use App\Models\PdfUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RepairBase64Uploads extends Command
{
    protected $signature = 'repair:base64-uploads';
    protected $description = 'Repair base64 uploaded PDF files that cannot be read';

    public function handle()
    {
        $this->info('Scanning for PDF uploads...');
        
        $uploads = PdfUpload::all();
        
        foreach ($uploads as $upload) {
            $this->line("Checking PDF #{$upload->id}: {$upload->name}");
            
            $path = storage_path('app/public/' . $upload->storage_path);
            
            if (!file_exists($path)) {
                $this->error("  File not found: {$path}");
                continue;
            }
            
            // Check if file is readable
            $size = filesize($path);
            $this->info("  File size: {$size} bytes");
            
            // Try to read first few bytes
            $handle = fopen($path, 'rb');
            if (!$handle) {
                $this->error("  Cannot open file for reading");
                continue;
            }
            
            $header = fread($handle, 5);
            fclose($handle);
            
            if (strpos($header, '%PDF-') === 0) {
                $this->info("  ✓ File appears to be valid PDF");
            } else {
                $this->warn("  ⚠ File may not be valid PDF (header: " . bin2hex($header) . ")");
                
                // Try to fix by re-saving the file
                $this->info("  Attempting to repair...");
                
                $content = file_get_contents($path);
                $tempFile = tempnam(sys_get_temp_dir(), 'pdf_repair_');
                
                // Save as binary
                file_put_contents($tempFile, $content);
                
                // Verify the temp file
                $tempHeader = file_get_contents($tempFile, false, null, 0, 5);
                
                if (strpos($tempHeader, '%PDF-') === 0) {
                    // Replace the original file
                    copy($tempFile, $path);
                    $this->info("  ✓ File repaired successfully");
                } else {
                    $this->error("  ✗ Could not repair file");
                }
                
                unlink($tempFile);
            }
        }
        
        $this->info('Repair process completed.');
    }
}
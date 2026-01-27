<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PdfUpload;
use App\Models\PdfPage;
use Illuminate\Support\Facades\DB;

class RefreshPdfPages extends Command
{
    protected $signature = 'pdf:refresh-pages';
    protected $description = 'Clear and recreate all pdf_pages records from pdf_uploads';

    public function handle()
    {
        $this->info('Starting PDF pages refresh...');
        
        // Confirm with user
        if (!$this->confirm('This will delete ALL existing pdf_pages records and recreate them. Continue?')) {
            $this->info('Operation cancelled.');
            return;
        }
        
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear all existing pages
        $deleted = PdfPage::truncate();
        $this->info('Cleared all existing pdf_pages records.');
        
        // Get all PDF uploads
        $pdfUploads = PdfUpload::all();
        $totalUploads = $pdfUploads->count();
        $totalPagesCreated = 0;
        
        $this->info("Processing {$totalUploads} PDF uploads...");
        
        // Create progress bar
        $bar = $this->output->createProgressBar($totalUploads);
        
        foreach ($pdfUploads as $pdfUpload) {
            // Create page entries for data entry
            $pages = [];
            for ($i = 1; $i <= $pdfUpload->total_pages; $i++) {
                $pages[] = [
                    'pdf_upload_id' => $pdfUpload->id,
                    'page_number' => $i,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $totalPagesCreated++;
            }
            
            // Batch insert for performance
            if (!empty($pages)) {
                DB::table('pdf_pages')->insert($pages);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Verify the counts
        $this->info("Summary:");
        $this->info("- Total PDF uploads processed: {$totalUploads}");
        $this->info("- Total pages created: {$totalPagesCreated}");
        
        // Check for any PDFs that might have incorrect page counts
        $this->info("\nChecking for potential issues...");
        
        // Check PDFs with 0 pages
        $pdfsWithNoPages = PdfUpload::whereDoesntHave('pages')->count();
        if ($pdfsWithNoPages > 0) {
            $this->warn("Found {$pdfsWithNoPages} PDFs with no pages created.");
            $this->table(['ID', 'Name', 'Total Pages'], 
                PdfUpload::whereDoesntHave('pages')
                    ->get(['id', 'name', 'total_pages'])
                    ->toArray()
            );
        }
        
        // Check PDFs where page count doesn't match total_pages
        $pdfs = PdfUpload::withCount('pages')->get();
        $mismatchedCount = 0;
        foreach ($pdfs as $pdf) {
            if ($pdf->pages_count != $pdf->total_pages) {
                $mismatchedCount++;
            }
        }
        
        if ($mismatchedCount > 0) {
            $this->warn("Found {$mismatchedCount} PDFs with mismatched page counts.");
        }
        
        $this->info("\nRefresh completed successfully!");
        
        return Command::SUCCESS;
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marriage;
use App\Models\PdfPage;
use App\Models\PdfUpload;
use App\Models\County;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get PDF pages with their relationships (same as PdfUploadController)
        $pdfPages = PdfPage::with(['pdfUpload.uploader', 'pdfUpload.county', 'pdfUpload.marriageType'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get years for filter
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

        // Months array for filter (01-12 format)
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            $months[$monthNum] = date('F', mktime(0, 0, 0, $i, 1));
        }
        
        // Get recent marriages
        $recentMarriages = Marriage::latest()->take(5)->get();
        
        // Get marriage types from categories
        $marriageTypes = Category::where('type', 'marriage_type')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        // Get dashboard statistics/card data
        $cardData = $this->getDashboardCardData();
        
        return view('dashboard', compact(
            'recentMarriages',
            'pdfPages',
            'years',
            'counties',
            'months',
            'marriageTypes',
            'cardData'
        ));
    }
    
    private function getDashboardCardData()
    {
        $totalPDFs = PdfUpload::count();
        $totalPages = PdfPage::count();
        $totalMarriages = Marriage::count();
        
        // Get today's data
        $todayPDFs = PdfUpload::whereDate('created_at', today())->count();
        $todayPages = PdfPage::whereDate('created_at', today())->count();
        $todayMarriages = Marriage::whereDate('created_at', today())->count();
        
        // Calculate storage usage
        $totalStorageBytes = PdfUpload::sum('file_size');
        $storageUsedMB = round($totalStorageBytes / (1024 * 1024), 2);
        $storageUsedFormatted = $storageUsedMB . ' MB';
        
        // Get data clerks count
        $dataClerksCount = User::whereHas('role', function($query) {
            $query->where('name', 'data_clerk');
        })->count();
        
        // Get active users
        $activeUsers = User::all()->count();
        
        // Calculate completion rate (pages with marriages)
        $pagesWithMarriages = PdfPage::whereHas('marriage')->count();
        $completionRate = $totalPages > 0 ? round(($pagesWithMarriages / $totalPages) * 100, 1) : 0;
        
        // Calculate average PDF size
        $avgPDFSize = $totalPDFs > 0 ? round($totalStorageBytes / $totalPDFs / (1024 * 1024), 2) : 0;
        
        $total_records = Marriage::count();
        $today_records = Marriage::whereDate('created_at', today())->count();
        $storage_used_mb = $storageUsedMB;
        $linked_pdfs = PdfUpload::whereHas('pdfPages.marriage')->count();
        
        return [
            'total_pdfs' => number_format($totalPDFs),
            'total_pages' => number_format($totalPages),
            'total_marriages' => number_format($totalMarriages),
            'today_pdfs' => number_format($todayPDFs),
            'today_pages' => number_format($todayPages),
            'today_marriages' => number_format($todayMarriages),
            'storage_used' => $storageUsedFormatted,
            'storage_mb' => $storageUsedMB,
            'data_clerks' => number_format($dataClerksCount),
            'active_users' => number_format($activeUsers),
            'completion_rate' => $completionRate,
            'avg_pdf_size' => $avgPDFSize,
            'pages_with_marriages' => number_format($pagesWithMarriages),
            'total_records' => number_format($total_records),
            'today_records' => number_format($today_records),
            'storage_used_mb' => $storage_used_mb,
            'linked_pdfs' => number_format($linked_pdfs),
        ];
    }
}
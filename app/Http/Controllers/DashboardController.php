<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marriage;
use App\Models\PdfPage;
use App\Models\PdfUpload;
use App\Models\County;
use App\Models\User;
use App\Models\Category;
use App\Models\ClerkManagement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $roleName = $user->role->name ?? 'user';
        
        // Get interval from request, default to 'daily'
        $interval = $request->get('interval', 'daily');
        
        // ===== BUILD ROLE-BASED QUERY FOR PDF PAGES =====
        $query = PdfPage::with(['pdfUpload.uploader', 'pdfUpload.county', 'pdfUpload.marriageType']);
        
        switch ($roleName) {
            case 'data_clerk':
                // Data clerk sees ONLY pages assigned to them that are NOT completed
                // They work on: pending, assigned, in_progress
                $query->where('assigned_to', $user->id)
                    ->whereIn('status', ['pending', 'assigned', 'in_progress', 'skipped']);
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
                $query->where('status', 'completed');
                break;
                
            case 'attorney_general':
            case 'ag':
                // Attorney General sees ONLY PUBLISHED pages
                $query->where('status', 'published')
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

        // Cap PDF pages if on dashboard and user is not admin
        // For admin, we use server-side pagination so we don't load all at once
        if ($currentRoute === 'dashboard' && $roleName !== 'admin') {
            $pdfPages = $query->orderBy('created_at', 'desc')->limit(2000)->get();
            $totalCount = $pdfPages->count();
        } else {
            // For admin, we only need a count, not all records
            // The actual data will be loaded via AJAX with pagination
            $totalCount = $query->count();
            // Only load first page worth of data for initial display (or empty collection)
            // We'll rely on AJAX for actual data loading
            $pdfPages = collect(); // Empty collection - data loads via AJAX
        }
        
        // Get years for filter (from all PDFs)
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
        
        // Get recent marriages (limited to 5)
        $recentMarriages = Marriage::latest()->take(5)->get();
        
        // Get marriage types from categories
        $marriageTypes = Category::where('type', 'marriage_type')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        // Get dashboard statistics (with role-based filtering)
        $cardData = $this->getOverviewCardData();

        // Get chart data with the selected interval
        $chartData = $this->getSmartChartData($interval);

        // Get role-specific charts data
        $roleCharts = $this->getRoleSpecificCharts($roleName);
        
        // Pass role to view
        $userRole = $roleName;
        
        return view('dashboard', compact(
            'recentMarriages',
            'pdfPages',
            'years',
            'counties',
            'months',
            'marriageTypes',
            'cardData',
            'statuses',
            'chartData',
            'userRole',
            'roleCharts',
            'totalCount'
        ));
    }

    public function getChartData(Request $request)
    {
        $interval = $request->get('interval', 'daily');
        $chartData = $this->getSmartChartData($interval);
        
        return response()->json([
            'success' => true,
            'chartData' => $chartData
        ]);
    }

    public function getDataClerkProductivity(Request $request)
    {
        $user = Auth::user();
        $interval = $request->get('interval', 'daily');
        
        // Get date range based on interval
        switch ($interval) {
            case 'yearly':
                $startDate = now()->subYears(5)->startOfYear();
                $endDate = now()->endOfYear();
                break;
            case 'monthly':
                $startDate = now()->subMonths(12)->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
            case 'weekly':
                $startDate = now()->subWeeks(12)->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            default: // daily
                $startDate = now()->subDays(30)->startOfDay();
                $endDate = now()->endOfDay();
                break;
        }
        
        $productivityData = PdfPage::where('assigned_to', $user->id)
            ->whereIn('status', ['completed', 'review_needed'])
            ->whereBetween('updated_at', [$startDate, $endDate]);
        
        switch ($interval) {
            case 'yearly':
                $data = $productivityData
                    ->select(DB::raw('YEAR(updated_at) as period'), DB::raw('COUNT(*) as total'))
                    ->groupBy('period')
                    ->orderBy('period', 'asc')
                    ->get();
                
                $labels = $data->pluck('period')->map(function($year) { return (string)$year; })->toArray();
                $counts = $data->pluck('total')->toArray();
                break;
                
            case 'monthly':
                $data = $productivityData
                    ->select(DB::raw('DATE_FORMAT(updated_at, "%Y-%m") as period'), DB::raw('COUNT(*) as total'))
                    ->groupBy('period')
                    ->orderBy('period', 'asc')
                    ->get();
                
                $labels = $data->pluck('period')->map(function($month) {
                    return date('M Y', strtotime($month . '-01'));
                })->toArray();
                $counts = $data->pluck('total')->toArray();
                break;
                
            case 'weekly':
                $data = $productivityData
                    ->select(DB::raw('YEARWEEK(updated_at) as period'), DB::raw('COUNT(*) as total'))
                    ->groupBy('period')
                    ->orderBy('period', 'asc')
                    ->get();
                
                $labels = $data->pluck('period')->map(function($week) {
                    return 'Week ' . substr($week, -2);
                })->toArray();
                $counts = $data->pluck('total')->toArray();
                break;
                
            default: // daily
                $data = $productivityData
                    ->select(DB::raw('DATE(updated_at) as period'), DB::raw('COUNT(*) as total'))
                    ->groupBy('period')
                    ->orderBy('period', 'asc')
                    ->get();
                
                $labels = $data->pluck('period')->map(function($date) {
                    return date('M d', strtotime($date));
                })->toArray();
                $counts = $data->pluck('total')->toArray();
                break;
        }
        
        return response()->json([
            'success' => true,
            'labels' => $labels,
            'counts' => $counts,
            'interval' => $interval
        ]);
    }

    public function getDataClerkStatusDistribution(Request $request)
    {
        $user = Auth::user();
        $interval = $request->get('interval', 'all');
        
        $query = PdfPage::where('assigned_to', $user->id);
        
        // Apply date filter if needed
        if ($interval !== 'all') {
            $days = (int) str_replace('days', '', $interval);
            $query->where('updated_at', '>=', now()->subDays($days));
        }
        
        $statusDistribution = [
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'review_needed' => (clone $query)->where('status', 'review_needed')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];
        
        return response()->json([
            'success' => true,
            'statuses' => array_keys($statusDistribution),
            'values' => array_values($statusDistribution)
        ]);
    }

    public function getMarriageTellerWeeklyTrend(Request $request)
    {
        $user = Auth::user();
        $weeks = $request->get('weeks', 4);
        
        $clerkIds = ClerkManagement::where('marriage_teller_id', $user->id)
            ->pluck('data_clerk_id');
        
        if ($clerkIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No clerks assigned'
            ]);
        }
        
        $weeklyTrend = PdfPage::whereIn('assigned_to', $clerkIds)
            ->where('updated_at', '>=', now()->subWeeks($weeks))
            ->whereIn('status', ['completed', 'review_needed'])
            ->select(DB::raw('YEARWEEK(updated_at) as week_num'), DB::raw('COUNT(*) as total'))
            ->groupBy('week_num')
            ->orderBy('week_num', 'asc')
            ->get()
            ->map(function($item) {
                // Extract week number from YEARWEEK format (YYYYWW)
                $weekNumber = (int) substr($item->week_num, -2);
                return [
                    'week' => 'Week ' . $weekNumber,
                    'count' => $item->total
                ];
            });
        
        return response()->json([
            'success' => true,
            'weeks' => $weeklyTrend->pluck('week'),
            'counts' => $weeklyTrend->pluck('count'),
            'trendData' => $weeklyTrend
        ]);
    }

    public function getMarriageRegistrarVerificationTrend(Request $request)
    {
        $days = $request->get('days', 14);
        
        $verificationTrend = Marriage::where('reviewed_at', '>=', now()->subDays($days))
            ->select(DB::raw('DATE(reviewed_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        $trendData = collect(range($days - 1, 0))->map(function($day) use ($verificationTrend) {
            $date = now()->subDays($day)->format('Y-m-d');
            $record = $verificationTrend->firstWhere('date', $date);
            return [
                'date' => $date,
                'label' => now()->subDays($day)->format('M d'),
                'count' => $record ? $record->count : 0
            ];
        });
        
        return response()->json([
            'success' => true,
            'labels' => $trendData->pluck('label'),
            'counts' => $trendData->pluck('count'),
            'trendData' => $trendData
        ]);
    }

    public function getAdminActivityTimeline(Request $request)
    {
        $days = $request->get('days', 14);
        
        $recentActivity = collect([
            'uploads' => PdfUpload::where('created_at', '>=', now()->subDays($days))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy('date'),
            'marriages' => Marriage::where('created_at', '>=', now()->subDays($days))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy('date'),
        ]);
        
        $activityTimeline = collect(range($days - 1, 0))->map(function($day) use ($recentActivity) {
            $date = now()->subDays($day)->format('Y-m-d');
            return [
                'date' => $date,
                'label' => now()->subDays($day)->format('D, M d'),
                'uploads' => $recentActivity['uploads'][$date]['count'] ?? 0,
                'marriages' => $recentActivity['marriages'][$date]['count'] ?? 0,
            ];
        });
        
        return response()->json([
            'success' => true,
            'labels' => $activityTimeline->pluck('label'),
            'uploads' => $activityTimeline->pluck('uploads'),
            'marriages' => $activityTimeline->pluck('marriages'),
            'activityData' => $activityTimeline
        ]);
    }

    public function getAdminUploadTrends(Request $request)
    {
        $months = $request->get('months', 6);
        
        $uploadTrends = PdfUpload::where('created_at', '>=', now()->subMonths($months))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as uploads'),
                DB::raw('SUM(file_size) as total_size')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'month' => date('M Y', strtotime($item->month . '-01')),
                    'uploads' => $item->uploads,
                    'size_mb' => round($item->total_size / (1024 * 1024), 2),
                ];
            });
        
        return response()->json([
            'success' => true,
            'months' => $uploadTrends->pluck('month'),
            'uploads' => $uploadTrends->pluck('uploads'),
            'storage' => $uploadTrends->pluck('size_mb'),
            'trendsData' => $uploadTrends
        ]);
    }

    public function getTableData(Request $request)
    {
        $user = Auth::user();
        $roleName = $user->role->name ?? 'user';
        
        $query = PdfPage::with(['pdfUpload.uploader', 'pdfUpload.county', 'pdfUpload.marriageType']);
        
        // ===== ROLE-BASED FILTERING =====
        switch ($roleName) {
            case 'data_clerk':
                $query->where('assigned_to', $user->id);
                break;
                
            case 'marriage_teller':
                $clerkIds = ClerkManagement::where('marriage_teller_id', $user->id)
                    ->pluck('data_clerk_id');
                if ($clerkIds->isNotEmpty()) {
                    $query->whereIn('assigned_to', $clerkIds);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
                
            case 'marriage_registrar':
                $query->whereIn('status', ['completed', 'review_needed']);
                break;
                
            case 'admin':
                // Admin sees all - no filter
                break;
                
            default:
                $query->whereHas('pdfUpload', function($q) {
                    $q->where('status', 'published');
                });
                break;
        }
        
        // ===== SEARCH =====
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('pdfUpload', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('year', 'like', "%{$search}%");
                })->orWhere('page_number', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }
        
        // ===== FILTERS =====
        if ($request->filled('filterYear')) {
            $query->whereHas('pdfUpload', fn($q) => $q->where('year', $request->filterYear));
        }
        if ($request->filled('filterMonth')) {
            $query->whereHas('pdfUpload', fn($q) => $q->where('month', $request->filterMonth));
        }
        if ($request->filled('filterCounty')) {
            $query->whereHas('pdfUpload', fn($q) => $q->where('county_code', $request->filterCounty));
        }
        if ($request->filled('filterStatus')) {
            $validStatuses = ['pending', 'assigned', 'in_progress', 'completed', 'review_needed', 'skipped'];
            if (in_array($request->filterStatus, $validStatuses)) {
                $query->where('status', $request->filterStatus);
            }
        }
        if ($request->filled('filterMarriageType')) {
            $query->whereHas('pdfUpload', fn($q) => $q->where('marriage_type_id', $request->filterMarriageType));
        }
        
        // ===== SORTING =====
        $sortColumn = $request->get('sortColumn', 'id');
        $sortDirection = $request->get('sortDirection', 'desc');
        
        $columnMap = [
            'id' => 'pdf_pages.id',
            'filename' => 'pdf_uploads.name',
            'year' => 'pdf_uploads.year',
            'month' => 'pdf_uploads.month',
            'county_code' => 'pdf_uploads.county_code',
            'marriage_type' => 'pdf_uploads.marriage_type_id',
            'file_size' => 'pdf_uploads.file_size',
            'total_pages' => 'pdf_uploads.total_pages',
            'status' => 'pdf_pages.status',
            'uploaded_by' => 'pdf_uploads.uploaded_by',
        ];
        
        $orderColumn = $columnMap[$sortColumn] ?? 'pdf_pages.id';
        $query->orderBy($orderColumn, $sortDirection);
        
        // ===== PAGINATION =====
        $perPage = $request->get('perPage', 10);
        $pdfPages = $query->paginate($perPage);
        
        return response()->json([
            'data' => $pdfPages->items(),
            'currentPage' => $pdfPages->currentPage(),
            'totalPages' => $pdfPages->lastPage(),
            'totalItems' => $pdfPages->total(),
            'perPage' => $pdfPages->perPage(),
        ]);
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
                $stats['total_marriages'] = Marriage::where('system_status', 'published')->count();
                $stats['published_total'] = PdfUpload::where('status', 'published')->count();
                $stats['published_today'] = PdfUpload::whereDate('created_at', today())
                    ->where('status', 'published')
                    ->count();
                break;
        }
        
        return $stats;
    }

    private function getSmartChartData($interval = 'daily')
    {
        // Get date range
        $firstRecord = PdfPage::orderBy('created_at', 'asc')->first();
        $lastRecord = PdfPage::orderBy('created_at', 'desc')->first();
        
        if (!$firstRecord || !$lastRecord) {
            return [
                'dates' => [],
                'counts' => [],
                'interval' => 'daily',
                'message' => 'No data available',
            ];
        }
        
        $startDate = $firstRecord->created_at->startOfDay();
        $endDate = $lastRecord->created_at->endOfDay();
        
        switch ($interval) {
            case 'weekly':
                return $this->getWeeklyData($startDate, $endDate);
            case 'monthly':
                return $this->getMonthlyData($startDate, $endDate);
            default:
                return $this->getDailyData($startDate, $endDate);
        }
    }
    
    private function getDailyData($startDate, $endDate)
    {
        $dailyUploads = PdfPage::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');
        
        $dates = [];
        $counts = [];
        
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('M d');
            $counts[] = $dailyUploads[$dateString]->total ?? 0;
            $currentDate->addDay();
        }
        
        return [
            'dates' => $dates,
            'counts' => $counts,
            'interval' => 'daily',
            'total_days' => count($dates),
            'total_records' => array_sum($counts),
        ];
    }

    private function getWeeklyData($startDate, $endDate)
    {
        $startOfWeek = $startDate->copy()->startOfWeek();
        $endOfWeek = $endDate->copy()->endOfWeek();
        
        $weeklyUploads = PdfPage::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('WEEK(created_at) as week'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('year', 'week')
            ->orderBy('year', 'asc')
            ->orderBy('week', 'asc')
            ->get()
            ->mapWithKeys(function($item) {
                $date = \Carbon\Carbon::now()->setISODate($item->year, $item->week)->startOfWeek();
                return [$date->format('Y-W') => [
                    'label' => 'Week ' . $item->week . ', ' . $item->year,
                    'total' => $item->total,
                ]];
            });
        
        $labels = [];
        $counts = [];
        
        $currentDate = clone $startOfWeek;
        while ($currentDate <= $endOfWeek) {
            $weekKey = $currentDate->format('Y-W');
            $labels[] = $currentDate->format('M d') . ' - ' . $currentDate->copy()->endOfWeek()->format('M d');
            $counts[] = $weeklyUploads[$weekKey]['total'] ?? 0;
            $currentDate->addWeek();
        }
        
        return [
            'dates' => $labels,
            'counts' => $counts,
            'interval' => 'weekly',
            'total_weeks' => count($labels),
            'total_records' => array_sum($counts),
        ];
    }

    private function getMonthlyData($startDate, $endDate)
    {
        $monthlyUploads = PdfPage::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy(function($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            });
        
        $labels = [];
        $counts = [];
        
        $currentDate = $startDate->copy()->startOfMonth();
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $labels[] = $currentDate->format('M Y');
            $counts[] = $monthlyUploads[$monthKey]->total ?? 0;
            $currentDate->addMonth();
        }
        
        return [
            'dates' => $labels,
            'counts' => $counts,
            'interval' => 'monthly',
            'total_months' => count($labels),
            'total_records' => array_sum($counts),
        ];
    }

    private function getRoleSpecificCharts($roleName)
    {
        $charts = [];
        
        switch ($roleName) {
            case 'data_clerk':
                $charts = $this->getDataClerkCharts();
                break;
            case 'marriage_teller':
                $charts = $this->getMarriageTellerCharts();
                break;
            case 'marriage_registrar':
                $charts = $this->getMarriageRegistrarCharts();
                break;
            case 'attorney_general':
            case 'ag':
                $charts = $this->getAttorneyGeneralCharts();
                break;
            case 'admin':
                $charts = $this->getAdminCharts();
                break;
            default:
                $charts = [];
                break;
        }
        
        return $charts;
    }

    private function getDataClerkCharts()
    {
        $user = Auth::user();
        
        $assignedPages = PdfPage::where('assigned_to', $user->id)->count();
        $completedPages = PdfPage::where('assigned_to', $user->id)
            ->whereIn('status', ['completed', 'review_needed'])
            ->count();
        $pendingPages = $assignedPages - $completedPages;
        $completionRate = $assignedPages > 0 ? round(($completedPages / $assignedPages) * 100, 1) : 0;
        
        $dailyProductivity = PdfPage::where('assigned_to', $user->id)
            ->where('updated_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(updated_at) as date'), DB::raw('COUNT(*) as count'))
            ->whereIn('status', ['completed', 'review_needed'])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        $last7Days = collect(range(6, 0))->map(function($days) use ($dailyProductivity) {
            $date = now()->subDays($days)->format('Y-m-d');
            $record = $dailyProductivity->firstWhere('date', $date);
            return [
                'date' => $date,
                'label' => now()->subDays($days)->format('D'),
                'count' => $record ? $record->count : 0
            ];
        });
        
        $statusDistribution = [
            'pending' => PdfPage::where('assigned_to', $user->id)->where('status', 'pending')->count(),
            'in_progress' => PdfPage::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'review_needed' => PdfPage::where('assigned_to', $user->id)->where('status', 'review_needed')->count(),
            'completed' => PdfPage::where('assigned_to', $user->id)->where('status', 'completed')->count(),
        ];
        
        return [
            'completion_rate' => $completionRate,
            'completed_pages' => $completedPages,
            'assigned_pages' => $assignedPages,
            'pending_pages' => $pendingPages,
            'daily_productivity' => $last7Days,
            'status_distribution' => $statusDistribution,
        ];
    }

    private function getMarriageTellerCharts()
    {
        $user = Auth::user();
        
        // Get all clerk management records with their associated user
        $clerkManagements = ClerkManagement::where('marriage_teller_id', $user->id)
            ->with(['dataClerk' => function($query) {
                $query->withCount([
                    'assignedPages as total_assigned',
                    'assignedPages as total_completed' => function($q) {
                        $q->whereIn('status', ['completed', 'review_needed']);
                    },
                    'assignedPages as review_needed' => function($q) {
                        $q->where('status', 'review_needed');
                    }
                ]);
            }])
            ->get();
        
        if ($clerkManagements->isEmpty()) {
            return ['no_data' => true];
        }
        
        $clerkPerformance = [];
        $totalAssigned = 0;
        $totalCompleted = 0;
        $activeClerks = 0;
        
        foreach ($clerkManagements as $management) {
            $clerk = $management->dataClerk;
            
            if (!$clerk) {
                continue;
            }
            
            $assigned = $clerk->total_assigned ?? 0;
            $completed = $clerk->total_completed ?? 0;
            $reviewNeeded = $clerk->review_needed ?? 0;
            $pending = $assigned - $completed;
            $completionRate = $assigned > 0 ? round(($completed / $assigned) * 100, 1) : 0;
            
            $totalAssigned += $assigned;
            $totalCompleted += $completed;
            
            if ($assigned > 0) {
                $activeClerks++;
            }
            
            $clerkPerformance[] = [
                'name' => $clerk->name,
                'assigned' => $assigned,
                'completed' => $completed,
                'pending' => $pending,
                'review_needed' => $reviewNeeded,
                'completion_rate' => $completionRate,
            ];
        }
        
        // Sort by completion rate
        usort($clerkPerformance, function($a, $b) {
            return $b['completion_rate'] <=> $a['completion_rate'];
        });
        
        // Get all clerk IDs for weekly trend
        $clerkIds = $clerkManagements->pluck('data_clerk_id')->toArray();
        
        // Weekly trend using raw query for better performance
        $weeklyTrend = PdfPage::whereIn('assigned_to', $clerkIds)
            ->where('updated_at', '>=', now()->subWeeks(4))
            ->whereIn('status', ['completed', 'review_needed'])
            ->select(DB::raw('YEARWEEK(updated_at) as week_num'), DB::raw('COUNT(*) as total'))
            ->groupBy('week_num')
            ->orderBy('week_num', 'asc')
            ->get()
            ->map(function($item) {
                $weekNumber = (int) substr($item->week_num, -2);
                return [
                    'week' => 'Week ' . $weekNumber,
                    'count' => $item->total
                ];
            });
        
        return [
            'clerks' => $clerkPerformance,
            'team_total_assigned' => $totalAssigned,
            'team_total_completed' => $totalCompleted,
            'team_completion_rate' => $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100, 1) : 0,
            'weekly_trend' => $weeklyTrend,
            'active_clerks' => $activeClerks,
        ];
    }

    private function getMarriageRegistrarCharts()
    {
        // Verification queue analysis
        $verificationQueue = [
            'review_needed' => PdfPage::where('status', 'review_needed')->count(),
            'pending_verification' => PdfPage::where('status', 'completed')->count(),
            'verified_this_week' => Marriage::where('reviewed_at', '>=', now()->subDays(7))->count(),
            'verified_this_month' => Marriage::where('reviewed_at', '>=', now()->subDays(30))->count(),
        ];
        
        // Daily verification trend (last 14 days)
        $verificationTrend = Marriage::where('reviewed_at', '>=', now()->subDays(14))
            ->select(DB::raw('DATE(reviewed_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(reviewed_at)'))
            ->orderBy('date', 'asc')
            ->get();
        
        $last14Days = collect(range(13, 0))->map(function($days) use ($verificationTrend) {
            $date = now()->subDays($days)->format('Y-m-d');
            $record = $verificationTrend->firstWhere('date', $date);
            return [
                'date' => $date,
                'label' => now()->subDays($days)->format('M d'),
                'count' => $record ? $record->count : 0
            ];
        });
        
        // Pages by marriage type needing verification
        $pagesByType = PdfPage::where('status', 'review_needed')
            ->with('pdfUpload.marriageType')
            ->get()
            ->groupBy(function($page) {
                return $page->pdfUpload->marriageType->name ?? 'Unknown';
            })
            ->map(fn($group) => $group->count())
            ->toArray();
        
        return [
            'verification_queue' => $verificationQueue,
            'verification_trend' => $last14Days,
            'pages_by_type' => $pagesByType,
            'total_pending_review' => $verificationQueue['review_needed'],
        ];
    }

    private function getAttorneyGeneralCharts()
    {
        // Counties vs PDF Pages (Top 10 counties by published pages)
        $countiesVsPages = PdfUpload::where('status', 'published')
            ->with('county')
            ->select('county_code', DB::raw('COUNT(*) as total_pages'))
            ->groupBy('county_code')
            ->orderBy('total_pages', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'county' => $item->county->name ?? $item->county_code,
                    'pages' => $item->total_pages,
                ];
            });
        
        // Marriage types distribution
        $marriageTypesDistribution = PdfUpload::where('status', 'published')
            ->with('marriageType')
            ->select('marriage_type_id', DB::raw('COUNT(*) as total'))
            ->groupBy('marriage_type_id')
            ->get()
            ->map(function($item) {
                return [
                    'type' => $item->marriageType->name ?? 'Unknown',
                    'count' => $item->total,
                ];
            });
        
        // Years trend (last 5 years)
        $yearsTrend = PdfUpload::where('status', 'published')
            ->where('year', '>=', now()->subYears(5)->year)
            ->select('year', DB::raw('COUNT(*) as total_uploads'), DB::raw('SUM(total_pages) as total_pages'))
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'year' => $item->year,
                    'uploads' => $item->total_uploads,
                    'pages' => $item->total_pages,
                ];
            });
        
        // County-wise marriage statistics (if marriage records exist)
        $countyMarriages = Marriage::where('system_status', 'published')
            ->with('county')
            ->select('county', DB::raw('COUNT(*) as total_marriages'))
            ->groupBy('county')
            ->orderBy('total_marriages', 'desc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'county' => $item->county->name ?? 'Unknown',
                    'marriages' => $item->total_marriages,
                ];
            });
        
        return [
            'counties_vs_pages' => $countiesVsPages,
            'marriage_types_distribution' => $marriageTypesDistribution,
            'years_trend' => $yearsTrend,
            'county_marriages' => $countyMarriages,
            'total_published_pdfs' => PdfUpload::where('status', 'published')->count(),
            'total_published_pages' => PdfPage::whereHas('pdfUpload', fn($q) => $q->where('status', 'published'))->count(),
        ];
    }

    private function getAdminCharts()
    {
        // Get all charts from other roles
        $agCharts = $this->getAttorneyGeneralCharts();
        
        // User activity by role
        $usersByRole = User::with('role')
            ->select('role_id', DB::raw('COUNT(*) as count'))
            ->groupBy('role_id')
            ->get()
            ->map(function($item) {
                return [
                    'role' => $item->role->name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });
        
        // System-wide upload trends (last 6 months)
        $uploadTrends = PdfUpload::where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as uploads'),
                DB::raw('SUM(file_size) as total_size')
            )
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'month' => date('M Y', strtotime($item->month . '-01')),
                    'uploads' => $item->uploads,
                    'size_mb' => round($item->total_size / (1024 * 1024), 2),
                ];
            });
        
        // User performance metrics (top performing clerks)
        $topClerks = User::whereHas('role', fn($q) => $q->where('name', 'data_clerk'))
            ->withCount(['assignedPages as total_assigned'])
            ->withCount(['assignedPages as total_completed' => function($q) {
                $q->whereIn('status', ['completed', 'review_needed', 'skipped', 'published']);
            }])
            ->having('total_assigned', '>', 0)
            ->limit(10)
            ->get()
            ->map(function($clerk) {
                return [
                    'name' => $clerk->name,
                    'assigned' => $clerk->total_assigned,
                    'completed' => $clerk->total_completed,
                    'rate' => round(($clerk->total_completed / $clerk->total_assigned) * 100, 1),
                ];
            })
            ->sortByDesc('assigned')
            ->values();
        
        // System health metrics
        $systemHealth = [
            'total_users' => User::count(),
            'total_uploads' => PdfUpload::count(),
            'total_pages_processed' => PdfPage::count(),
            'total_marriages' => Marriage::count(),
            'storage_used_mb' => round(PdfUpload::sum('file_size') / (1024 * 1024), 2),
            'completion_rate' => PdfPage::count() > 0 
                ? round((PdfPage::where('status', 'completed')->count() / PdfPage::count()) * 100, 1)
                : 0,
        ];
        
        // Recent activity timeline (last 7 days)
        $recentActivity = collect([
            'uploads' => PdfUpload::where('created_at', '>=', now()->subDays(7))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get()
                ->keyBy('date')
                ->map(fn($item) => ['uploads' => $item->count]),
            'marriages' => Marriage::where('created_at', '>=', now()->subDays(7))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->get()
                ->keyBy('date')
                ->map(fn($item) => ['marriages' => $item->count]),
        ]);
        
        $activityTimeline = collect(range(6, 0))->map(function($days) use ($recentActivity) {
            $date = now()->subDays($days)->format('Y-m-d');
            return [
                'date' => $date,
                'label' => now()->subDays($days)->format('D, M d'),
                'uploads' => $recentActivity['uploads'][$date]['uploads'] ?? 0,
                'marriages' => $recentActivity['marriages'][$date]['marriages'] ?? 0,
            ];
        });
        
        return array_merge($agCharts, [
            'users_by_role' => $usersByRole,
            'upload_trends' => $uploadTrends,
            'top_clerks' => $topClerks,
            'system_health' => $systemHealth,
            'activity_timeline' => $activityTimeline,
        ]);
    }
}
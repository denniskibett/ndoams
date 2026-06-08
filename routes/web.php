<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarriageController;
use App\Http\Controllers\SpouseController;
use App\Http\Controllers\WitnessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MarriageTypeExtensionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PdfUploadController;
use App\Http\Controllers\ClerkManagementController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordController;
use App\Models\Marriage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ------------------ PUBLIC ROUTES ------------------
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Google OAuth
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('login.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

require __DIR__.'/auth.php';

// ------------------ AUTHENTICATED ROUTES ------------------
Route::middleware('auth')->group(function () {

    Route::get('/set-password', [PasswordController::class, 'showSetPasswordForm'])->name('password.set-form');
    Route::post('/set-password', [PasswordController::class, 'set'])->name('password.set');

    // -------- PROFILE ROUTES --------
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/address', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::get('/profile/data', [ProfileController::class, 'getUserData'])->name('profile.data');

    // -------- STATIC PAGES --------
    $staticPages = [
        '404','alerts','avatars','badge','bar-chart','basic-tables','blank',
        'buttons','calendar','dash','form-elements','index','invoice','line-chart',
        'messages','profile-page','signin','signup','videos','image'
    ];
    foreach ($staticPages as $page) {
        Route::get("/$page", fn() => view($page))->name($page);
    }

    // -------- IMAGE MANAGEMENT --------
    Route::resource('images', ImageController::class);
    Route::prefix('image')->group(function () {
        Route::get('/', [ImageController::class, 'index'])->name('images.index');
        Route::get('/create', [ImageController::class, 'create'])->name('images.create');
        Route::post('/', [ImageController::class, 'store'])->name('images.store');
        Route::get('/{image}', [ImageController::class, 'show'])->name('images.show');
        Route::get('/{image}/edit', [ImageController::class, 'edit'])->name('images.edit');
        Route::put('/{image}', [ImageController::class, 'update'])->name('images.update');
    });

    // Redirect bulk uploads for data clerks
    Route::get('/marriages/0', function () {
        if (Auth::check() && Auth::user()->role->name === 'data_clerk') {
            return redirect()->route('images.index');
        }
        abort(403, 'Unauthorized access.');
    });

    // -------- MARRIAGE MANAGEMENT --------
    Route::prefix('marriages')->group(function () {
        Route::get('/', [MarriageController::class, 'index'])->name('marriages.index');
        Route::get('/create', [MarriageController::class, 'create'])->name('marriages.create');
        Route::post('/store', [MarriageController::class, 'store'])->name('marriages.store');
        Route::get('/{image}/create-from-image', [MarriageController::class, 'createFromImage'])->name('marriages.create-from-image');
        Route::post('/store-from-image/{image}', [MarriageController::class, 'storeFromImage'])->name('marriages.store-from-image');
        Route::get('/create-from-pdf/{pdf}', [MarriageController::class, 'createFromPdf'])->name('marriages.create-from-pdf');
        Route::post('/store-from-pdf/{pdf}', [MarriageController::class, 'storeFromPdf'])->name('marriages.store-from-pdf');
        Route::get('/{marriage}', [MarriageController::class, 'show'])->name('marriages.show');
        Route::get('/{marriage}/edit', [MarriageController::class, 'edit'])->name('marriages.edit');
        Route::put('/{marriage}', [MarriageController::class, 'update'])->name('marriages.update');
        Route::delete('/{marriage}', [MarriageController::class, 'destroy'])->name('marriages.destroy');

        Route::get('/{marriage}/edit-data', [MarriageController::class, 'editData'])->name('marriages.edit-data');
        Route::get('/by-pdf/{pdfId}', [MarriageController::class, 'getByPdfId'])->name('marriages.by-pdf');
        Route::post('/quick-create', [MarriageController::class, 'quickCreate'])->name('marriages.quick-create');
        Route::post('/{marriage}/quick-update', [MarriageController::class, 'quickUpdate'])->name('marriages.quick-update');
    });

    // -------- USER MANAGEMENT --------
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');

    // -------- CLERK MANAGEMENT --------
    Route::prefix('clerk-management')->name('clerk-management.')->group(function () {
        Route::get('/', [ClerkManagementController::class, 'index'])->name('index');
        Route::post('/', [ClerkManagementController::class, 'store'])->name('store');
        
        // STATIC ROUTES FIRST (no model binding)
        Route::get('/pending-pdfs', [ClerkManagementController::class, 'getPendingPdfs'])->name('pending-pdfs');
        Route::post('/assign-multiple', [ClerkManagementController::class, 'assignMultiplePdfs'])->name('assign-multiple');
        
        // DYNAMIC ROUTES LAST (with model binding)
        Route::get('/{clerkManagement}', [ClerkManagementController::class, 'show'])->name('show');
        Route::put('/{clerkManagement}', [ClerkManagementController::class, 'update'])->name('update');
        Route::delete('/{clerkManagement}', [ClerkManagementController::class, 'destroy'])->name('destroy');
        Route::get('/{clerkManagement}/progress', [ClerkManagementController::class, 'showProgress'])->name('progress');
        Route::get('/{clerkManagement}/pending-pdfs', [ClerkManagementController::class, 'getPendingPdfsForClerk'])->name('pending-pdfs-for-clerk');
        
        // PDF assignment routes
        Route::get('/assign-pdf/{pdfUpload}', [ClerkManagementController::class, 'assignPdf'])->name('assign-pdf');
        Route::post('/assign-pdf/{pdfUpload}', [ClerkManagementController::class, 'storePdfAssignment'])->name('store-pdf-assignment');
    });

    // -------- OTHER RESOURCE ROUTES --------
    Route::resource('spouses', SpouseController::class);
    Route::resource('witnesses', WitnessController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('marriage-extensions', MarriageTypeExtensionController::class);
    Route::resource('pdf-uploads', PdfUploadController::class);

    // -------- ADDITIONAL PDF ROUTES --------
    Route::get('pdf-uploads/get-limits', [PdfUploadController::class, 'getUploadLimits'])->name('pdf-uploads.get-limits');
    Route::get('pdf-uploads/{pdfUpload}/download', [PdfUploadController::class, 'download'])->name('pdf-uploads.download');
    Route::post('pdf-uploads/upload-large-file', [PdfUploadController::class, 'uploadLargeFile'])->name('pdf-uploads.upload-large-file');
    Route::post('pdf-uploads/process-upload', [PdfUploadController::class, 'processUpload'])->name('pdf-uploads.process-upload');
    Route::get('/pdf-uploads/{pdfUpload}/pages/{pageNumber}', [PdfUploadController::class, 'showPage'])->name('pdf-uploads.page.show');
    Route::get('/pdf-uploads/{pdfUpload}', [PdfUploadController::class, 'show'])->name('pdf-uploads.show');
    Route::post('/pdf-uploads/{pdfUpload}/pages/{pageNumber}/start', [PdfUploadController::class, 'startPage'])->name('pdf-uploads.page.start');
    Route::post('/pdf-uploads/{pdfUpload}/pages/{pageNumber}/complete', [PdfUploadController::class, 'completePage'])->name('pdf-uploads.page.complete');
    Route::get('/pdf-uploads/{pdfUpload}/preview', [PdfUploadController::class, 'preview'])->name('pdf-uploads.preview');
    Route::post('/pdf-uploads/preview-upload', [PdfUploadController::class, 'previewUpload'])->middleware('auth')->name('pdf-uploads.preview-upload');
    Route::post('/pdf-uploads/cleanup-temp', [PdfUploadController::class, 'cleanupTempFile'])->middleware('auth')->name('pdf-uploads.cleanup-temp');
    Route::get('/pdf-uploads/{pdfUpload}/pages/{page}/data', [PdfUploadController::class, 'getPageData'])->name('pdf-uploads.page.data');
    Route::post('/pdf-uploads/{pdfUpload}/pages/{page}/assign', [PdfUploadController::class, 'assignPage'])->name('pdf-uploads.page.assign');
    Route::get('/pdf-uploads/{pdfUpload}/pages/{page}/download', [PdfUploadController::class, 'downloadPage'])->name('pdf-uploads.page.download');
    Route::get('/pdf-uploads/{pdfUpload}/pages/{page}/print', [PdfUploadController::class, 'printPage'])->name('pdf-uploads.page.print');
    
    // REMOVE DUPLICATE ROUTES - Keep only these:
    Route::post('/pdf-pages/{page}/assign', [PdfUploadController::class, 'assignPage'])->name('pdf-pages.assign');
    Route::post('/pdf-pages/{page}/complete', [PdfUploadController::class, 'completePage'])->name('pdf-pages.complete');
    Route::post('/pdf-pages/{page}/quick-data', [PdfUploadController::class, 'saveQuickData'])->name('pdf-pages.quick-data');
    Route::get('/pdf-pages/{pdfPage}', [PdfUploadController::class, 'showPageDetail'])->name('pdf-pages.show');
    Route::post('/pdf-pages/{page}/update-status', [PdfUploadController::class, 'updatePageStatus'])->name('pdf-pages.update-status');
    // Dashboard data routes
    Route::get('/dashboard/pdf-pages', [DashboardController::class, 'getTableData'])->name('dashboard.pdf-pages');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    // Dashboard chart data routes
    Route::get('/dashboard/data-clerk/daily-productivity', [DashboardController::class, 'getDataClerkProductivity'])->name('dashboard.data-clerk.productivity');
    Route::get('/dashboard/data-clerk/status-distribution', [DashboardController::class, 'getDataClerkStatusDistribution'])->name('dashboard.data-clerk.status');
    // Dashboard chart data routes for other roles
    Route::get('/dashboard/marriage-registrar/verification-trend', [DashboardController::class, 'getMarriageRegistrarVerificationTrend'])->name('dashboard.marriage-registrar.verification-trend');
    Route::get('/dashboard/admin/activity-timeline', [DashboardController::class, 'getAdminActivityTimeline'])->name('dashboard.admin.activity-timeline');
    Route::get('/dashboard/admin/upload-trends', [DashboardController::class, 'getAdminUploadTrends'])->name('dashboard.admin.upload-trends');

    // Add these routes to your web.php inside the auth middleware group
    Route::get('/dashboard/marriage-teller/weekly-trend', [DashboardController::class, 'getMarriageTellerWeeklyTrend'])->name('dashboard.marriage-teller.weekly-trend');
    Route::get('/dashboard/data-clerk/productivity', [DashboardController::class, 'getDataClerkProductivity'])->name('dashboard.data-clerk.productivity');
    Route::get('/dashboard/data-clerk/status-distribution', [DashboardController::class, 'getDataClerkStatusDistribution'])->name('dashboard.data-clerk.status');

    Route::post('/pdf-uploads/quick-create-from-page', [PdfUploadController::class, 'quickCreateFromPage'])->name('pdf.quick-create-from-page')->middleware('auth');
    Route::post('/pdf-uploads/full-create-from-page', [PdfUploadController::class, 'fullCreateFromPage'])->name('pdf.full-create-from-page')->middleware('auth');
    
    Route::post('/pdf-uploads/upload-base64-chunk', [PdfUploadController::class, 'uploadBase64Chunk'])->name('pdf-uploads.upload-base64-chunk');

    
    // -------- SYSTEM SETTINGS --------
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/', [SystemController::class, 'index'])->name('index');
        Route::put('/update', [SystemController::class, 'update'])->name('update');
        Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
        Route::get('/backup', [SystemController::class, 'backupDatabase'])->name('backup');
        Route::post('/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        Route::post('/debug', [SystemController::class, 'debug'])->name('debug');
    });
});

// ------------------ API ROUTES ------------------
Route::prefix('api')->middleware('auth')->group(function () {
    // Counties API
    Route::get('/counties/{county}/constituencies', function ($county) {
        $constituencies = \App\Models\County::where('name', $county)
            ->distinct('constituency')
            ->pluck('constituency')
            ->filter()
            ->values()
            ->toArray();
        return response()->json($constituencies);
    });

    Route::get('/counties/{county}/constituencies/{constituency}/wards', function ($county, $constituency) {
        $wards = \App\Models\County::where('name', $county)
            ->where('constituency', $constituency)
            ->distinct('ward')
            ->pluck('ward')
            ->filter()
            ->values()
            ->toArray();
        $otherWards = \App\Models\County::where('name', $county)
            ->where('constituency', '!=', $constituency)
            ->distinct('ward')
            ->pluck('ward')
            ->filter()
            ->values()
            ->toArray();
        return response()->json(['wards' => $wards, 'otherWards' => $otherWards]);
    });

    Route::get('/pdf-uploads/wards', [PdfUploadController::class, 'getWards'])->name('pdf-uploads.wards');
    Route::get('/api/wards', [App\Http\Controllers\PdfUploadController::class, 'getWards'])
            ->name('api.wards');
            
    // PDF Uploads API
    Route::get('/pdf-uploads/{pdf}', function ($pdfId) {
        $pdf = \App\Models\PdfUpload::find($pdfId);
        if (!$pdf) return response()->json(['error' => 'PDF not found'], 404);
        return response()->json([
            'certificate_serial' => $pdf->certificate_serial,
            'year' => $pdf->year,
            'month' => $pdf->month,
            'county' => $pdf->county->name ?? $pdf->county_code ?? null,
        ]);
    });

    // Marriage API routes
    Route::put('/marriages/update-from-pdf/{marriage}', [MarriageController::class, 'updateFromPdf'])->name('marriages.update-from-pdf');
    Route::post('/marriages/{marriage}/update-from-pdf', [MarriageController::class, 'updateFromPdf'])->name('marriages.update-from-pdf');
    Route::get('/marriages/by-pdf/{pdfId}', [MarriageController::class, 'getByPdfIdApi']);
    Route::get('/marriages/create/from-pdf-page/{pdfPage}', [MarriageController::class, 'createFromPdfPage'])->name('marriages.create-from-pdf-page');
    Route::post('/marriages/{marriage}/review', [PdfUploadController::class, 'reviewMarriage'])->name('marriages.review')->middleware('auth');
    Route::post('/pdf-pages/{page}/update-status', [PdfUploadController::class, 'updatePageStatus'])->name('pdf-pages.update-status')->middleware('auth');

    Route::post('/marriages/{marriage}/review', [PdfUploadController::class, 'reviewMarriage'])->name('marriages.review')->middleware('auth');
});
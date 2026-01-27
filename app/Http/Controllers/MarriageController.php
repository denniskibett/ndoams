<?php

namespace App\Http\Controllers;

use App\Models\Marriage;
use App\Models\User;
use App\Models\Category;
use App\Models\Image;
use App\Models\PdfUpload;
use App\Models\County;
use App\Models\ClerkManagement;
use App\Models\MarriageTypeExtension;
use App\Services\ImageService;
use App\Http\Requests\StoreMarriageRequest;
use App\Http\Requests\UpdateMarriageRequest;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MarriageController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $role = $user->role->name;
        
        // Check if user has access to marriages
        $allowedRoles = ['admin', 'marriage_registrar', 'marriage_teller'];
        
        if (!in_array($role, $allowedRoles)) {
            abort(403, 'Unauthorized access. You do not have permission to view marriage records.');
        }
        
        // Get marriages based on role
        $marriages = $this->getMarriagesForRole($role, $user);
        
        // Get unlinked images for marriage tellers
        $unlinkedImages = null;
        $unlinkedPdfPages = null;
        $unlinkedPdfs = null;
        
        if ($role === 'marriage_teller') {
            $unlinkedImages = Image::whereDoesntHave('marriage')
                                ->whereHas('uploader.role', function($query) {
                                    $query->where('name', 'data_clerk');
                                })
                                ->with('uploader.role')
                                ->latest()
                                ->get();
            
            // Get unlinked PDF pages (pages without marriage records)
            // IMPORTANT: Check both pdf_id AND pdf_page_id are null
            $unlinkedPdfPages = \App\Models\PdfPage::whereDoesntHave('marriage', function($query) {
                                    $query->whereNotNull('pdf_page_id');
                                })
                                ->whereHas('pdfUpload.uploader.role', function($query) {
                                    $query->where('name', 'data_clerk');
                                })
                                ->with([
                                    'pdfUpload' => function($query) {
                                        $query->with(['uploader.role', 'county']);
                                    },
                                    'assignedUser'
                                ])
                                ->latest()
                                ->get();
            
            // Get unlinked PDFs (entire PDFs that don't have any marriage linked via pdf_id)
            // This is for legacy compatibility
            $unlinkedPdfs = PdfUpload::whereDoesntHave('marriages', function($query) {
                                    $query->whereNotNull('pdf_id');
                                })
                                ->whereHas('uploader.role', function($query) {
                                    $query->where('name', 'data_clerk');
                                })
                                ->with('uploader.role')
                                ->latest()
                                ->get();
        }
        
        // Get role-specific stats
        $stats = $this->getRoleSpecificStats($role, $user);

        return view('marriages.index', compact(
            'marriages', 
            'stats', 
            'unlinkedImages', 
            'unlinkedPdfPages', 
            'unlinkedPdfs'
        ));
    }

        

    private function getMarriagesForRole($role, $user)
    {
        switch ($role) {
            case 'admin':
            case 'marriage_registrar':
                return Marriage::with([
                    'spouses', 
                    'witnesses', 
                    'createdBy', 
                    'image',
                    'pdf'
                ])->latest()->get();

            case 'marriage_teller':
                return Marriage::with([
                    'spouses', 
                    'witnesses', 
                    'createdBy', 
                    'image',
                    'pdf'
                ])->where(function($query) use ($user) {
                    $query->where('created_by', $user->id)
                        ->orWhereHas('image', function($q) {
                            $q->whereHas('uploader', function($subQuery) {
                                $subQuery->whereHas('role', function($roleQuery) {
                                    $roleQuery->where('name', 'data_clerk');
                                });
                            });
                        })
                        ->orWhereHas('pdf', function($q) {
                            $q->whereHas('uploader', function($subQuery) {
                                $subQuery->whereHas('role', function($roleQuery) {
                                    $roleQuery->where('name', 'data_clerk');
                                });
                            });
                        });
                })->latest()->get();

            default:
                abort(403, 'Unauthorized access.');
        }
    }

    private function getRoleSpecificStats($role, $user)
    {
        switch ($role) {
            case 'marriage_teller':
                // Get verification status category IDs
                $verifiedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Verified')
                    ->value('id');
                
                $rejectedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Rejected')
                    ->value('id');
                
                $unverifiedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Unverified')
                    ->value('id');
                
                // Count marriages by data clerks (through PDF pages or images)
                $totalMarriages = Marriage::count();
                $completedMarriages = Marriage::where('system_status', 'Completed')->count();
                $pendingMarriages = Marriage::where('system_status', 'Pending')->count();
                
                // Count data clerks
                $activeClerks = User::whereHas('role', function($query) {
                    $query->where('name', 'data_clerk');
                })->count();
                
                // Count images uploaded by data clerks
                $dataClerkImages = Image::whereHas('uploader.role', function($query) {
                    $query->where('name', 'data_clerk');
                })->count();

                // Count PDF PAGES uploaded by data clerks (not PDFs)
                $dataClerkPdfPages = \App\Models\PdfPage::whereHas('pdfUpload.uploader.role', function($query) {
                    $query->where('name', 'data_clerk');
                })->count();
                
                // Count PDF UPLOADS uploaded by data clerks (legacy)
                $dataClerkPdfs = PdfUpload::whereHas('uploader.role', function($query) {
                    $query->where('name', 'data_clerk');
                })->count();
                
                // Count UNLINKED pages (pages without marriage records)
                $unlinkedPdfPages = \App\Models\PdfPage::whereDoesntHave('marriage')
                    ->whereHas('pdfUpload.uploader.role', function($query) {
                        $query->where('name', 'data_clerk');
                    })
                    ->count();
                
                // Count LINKED pages (pages with marriage records)
                $linkedPdfPages = \App\Models\PdfPage::whereHas('marriage')
                    ->whereHas('pdfUpload.uploader.role', function($query) {
                        $query->where('name', 'data_clerk');
                    })
                    ->count();
                
                // Count COMPLETED pages (status = completed)
                $completedPdfPages = \App\Models\PdfPage::where('status', 'completed')
                    ->whereHas('pdfUpload.uploader.role', function($query) {
                        $query->where('name', 'data_clerk');
                    })
                    ->count();
                
                // Count PENDING pages (status = pending)
                $pendingPdfPages = \App\Models\PdfPage::where('status', 'pending')
                    ->whereHas('pdfUpload.uploader.role', function($query) {
                        $query->where('name', 'data_clerk');
                    })
                    ->count();
                
                // Count PROCESSING pages (status = processing)
                $processingPdfPages = \App\Models\PdfPage::where('status', 'processing')
                    ->whereHas('pdfUpload.uploader.role', function($query) {
                        $query->where('name', 'data_clerk');
                    })
                    ->count();
                
                // Calculate completion rate for PDF pages
                $pdfPagesCompletionRate = $dataClerkPdfPages > 0 
                    ? round(($completedPdfPages / $dataClerkPdfPages) * 100) 
                    : 0;

                return [
                    'team_total' => $totalMarriages,
                    'team_completed' => $completedMarriages,
                    'pending_review' => $pendingMarriages,
                    'active_clerks' => $activeClerks,
                    
                    // PDF Pages statistics
                    'data_clerk_pdf_pages' => $dataClerkPdfPages,
                    'unlinked_pdf_pages' => $unlinkedPdfPages,
                    'linked_pdf_pages' => $linkedPdfPages,
                    'completed_pdf_pages' => $completedPdfPages,
                    'pending_pdf_pages' => $pendingPdfPages,
                    'processing_pdf_pages' => $processingPdfPages,
                    'pdf_pages_completion_rate' => $pdfPagesCompletionRate,
                    
                    // Legacy PDF statistics (for backward compatibility)
                    'data_clerk_pdfs' => $dataClerkPdfs,
                    'data_clerk_images' => $dataClerkImages,
                    
                    // Marriage verification statistics
                    'verified' => $verifiedStatusId ? Marriage::where('verification_status_id', $verifiedStatusId)->count() : 0,
                    'pending' => $pendingMarriages,
                    'rejected' => $rejectedStatusId ? Marriage::where('verification_status_id', $rejectedStatusId)->count() : 0,
                    
                    // Overall completion rate
                    'overall_completion_rate' => $totalMarriages > 0 
                        ? round(($completedMarriages / $totalMarriages) * 100) 
                        : 0,
                ];

            case 'admin':
            case 'marriage_registrar':
                // Get verification status category IDs
                $verifiedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Verified')
                    ->value('id');
                
                $rejectedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Rejected')
                    ->value('id');
                
                $unverifiedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Unverified')
                    ->value('id');

                // Enhanced counts for admin/registrar
                $totalMarriages = Marriage::count();
                $completedMarriages = Marriage::where('system_status', 'Completed')->count();
                $pendingMarriages = Marriage::where('system_status', 'Pending')->count();
                $verifiedMarriages = $verifiedStatusId ? Marriage::where('verification_status_id', $verifiedStatusId)->count() : 0;
                $rejectedMarriages = $rejectedStatusId ? Marriage::where('verification_status_id', $rejectedStatusId)->count() : 0;
                $unverifiedMarriages = $unverifiedStatusId ? Marriage::where('verification_status_id', $unverifiedStatusId)->count() : 0;

                // PDF Pages statistics (admin view)
                $totalPdfPages = \App\Models\PdfPage::count();
                $completedPdfPages = \App\Models\PdfPage::where('status', 'completed')->count();
                $pendingPdfPages = \App\Models\PdfPage::where('status', 'pending')->count();
                $processingPdfPages = \App\Models\PdfPage::where('status', 'processing')->count();
                $linkedPdfPages = \App\Models\PdfPage::whereHas('marriage')->count();
                $unlinkedPdfPages = \App\Models\PdfPage::whereDoesntHave('marriage')->count();
                
                // PDF pages completion rate
                $pdfPagesCompletionRate = $totalPdfPages > 0 
                    ? round(($completedPdfPages / $totalPdfPages) * 100) 
                    : 0;

                // Pending verification (completed but unverified)
                $pendingVerification = Marriage::where('system_status', 'Completed')
                                            ->where('verification_status_id', $unverifiedStatusId)
                                            ->count();

                return [
                    'team_total' => $totalMarriages,
                    'total_verified' => $verifiedMarriages,
                    'verified_today' => Marriage::where('verification_status_id', $verifiedStatusId)
                                            ->whereDate('updated_at', today())
                                            ->count(),
                    'verification_queue' => $pendingVerification,
                    'rejection_rate' => $verifiedMarriages + $rejectedMarriages > 0 ? 
                        round(($rejectedMarriages / ($verifiedMarriages + $rejectedMarriages)) * 100) . '%' : '0%',
                    'verified' => $verifiedMarriages,
                    'pending' => $pendingMarriages,
                    'rejected' => $rejectedMarriages,
                    'unverified' => $unverifiedMarriages,
                    'pending_verification' => $pendingVerification,
                    
                    // PDF Pages statistics
                    'total_pdf_pages' => $totalPdfPages,
                    'completed_pdf_pages' => $completedPdfPages,
                    'pending_pdf_pages' => $pendingPdfPages,
                    'processing_pdf_pages' => $processingPdfPages,
                    'linked_pdf_pages' => $linkedPdfPages,
                    'unlinked_pdf_pages' => $unlinkedPdfPages,
                    'pdf_pages_completion_rate' => $pdfPagesCompletionRate,
                    
                    // Images statistics
                    'total_images' => Image::count(),
                    'linked_images' => Image::whereHas('marriage')->count(),
                    'unlinked_images' => Image::whereDoesntHave('marriage')->count(),
                ];

            case 'data_clerk':
                // Simple counts for data clerk with PDF pages
                $myImageUploads = Image::where('uploaded_by', $user->id)
                                ->whereMonth('created_at', now()->month)
                                ->count();
                
                // Count PDF pages uploaded by this clerk
                $myPdfPagesUploaded = \App\Models\PdfPage::whereHas('pdfUpload', function($query) use ($user) {
                    $query->where('uploaded_by', $user->id);
                })->whereMonth('created_at', now()->month)->count();
                
                // Count PDF pages assigned to this clerk
                $myAssignedPdfPages = \App\Models\PdfPage::where('assigned_to', $user->id)->count();
                
                // Count completed PDF pages
                $myCompletedPdfPages = \App\Models\PdfPage::where('assigned_to', $user->id)
                    ->where('status', 'completed')->count();
                
                // Count marriages created from my uploads
                $myMarriagesFromUploads = Marriage::whereHas('pdf', function($query) use ($user) {
                    $query->where('uploaded_by', $user->id);
                })->orWhereHas('image', function($query) use ($user) {
                    $query->where('uploaded_by', $user->id);
                })->count();
                                    
                $myCompleted = Marriage::where('created_by', $user->id)
                                    ->where('system_status', 'Completed')
                                    ->count();
                                    
                $myPending = Marriage::where('created_by', $user->id)
                                ->where('system_status', 'Pending')
                                ->count();

                return [
                    'my_image_uploads' => $myImageUploads,
                    'my_pdf_pages_uploaded' => $myPdfPagesUploaded,
                    'my_assigned_pdf_pages' => $myAssignedPdfPages,
                    'my_completed_pdf_pages' => $myCompletedPdfPages,
                    'my_marriages_from_uploads' => $myMarriagesFromUploads,
                    'my_completed' => $myCompleted,
                    'my_pending' => $myPending,
                    'pdf_pages_progress' => $myAssignedPdfPages > 0 
                        ? round(($myCompletedPdfPages / $myAssignedPdfPages) * 100) . '%' 
                        : '0%',
                    'progress' => '100%'
                ];

            default:
                // Get verification status category IDs
                $verifiedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Verified')
                    ->value('id');
                
                $rejectedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Rejected')
                    ->value('id');
                
                $unverifiedStatusId = Category::where('type', 'verification_status')
                    ->where('name', 'Unverified')
                    ->value('id');
                
                // Simple counts for users
                return [
                    'total_records' => $verifiedStatusId ? Marriage::where('verification_status_id', $verifiedStatusId)->count() : 0,
                    'verified' => $verifiedStatusId ? Marriage::where('verification_status_id', $verifiedStatusId)->count() : 0,
                    'pending' => $unverifiedStatusId ? Marriage::where('verification_status_id', $unverifiedStatusId)->count() : 0,
                    'rejected' => $rejectedStatusId ? Marriage::where('verification_status_id', $rejectedStatusId)->count() : 0
                ];
        }
    }

    public function availableImages()
    {
        $user = Auth::user();
        
        // Only marriage tellers can access this
        if ($user->role->name !== 'marriage_teller') {
            abort(403, 'Only marriage tellers can access available images.');
        }

        $filters = [
            'year' => request('year'),
            'month' => request('month')
        ];

        $availableImages = FileService::getAvailableImages($filters);

        return view('marriages.available-images', compact('availableImages'));
    }


    public function create()
    {
        $user = Auth::user();
        
        // Check if user has permission to create marriages
        if (!in_array($user->role->name, ['marriage_registrar', 'admin', 'marriage_teller'])) {
            abort(403, 'Unauthorized access.');
        }

        $categories = Category::whereIn('type', ['marriage_type', 'marriage_status', 'verification_status', 'id_type'])
                            ->get();

        return view('marriages.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role->name, ['marriage_registrar', 'admin', 'marriage_teller'])) {
            abort(403, 'You do not have permission to create marriage records.');
        }

        $validated = $request->validate([
            'certificate_serial' => 'required|string|max:255|unique:marriages,certificate_serial',
            'marriage_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'marriage_type_id' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'reg_date' => 'nullable|date',
            'sub_county' => 'nullable|string|max:255',
            'constituency' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:2000|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        try {
            DB::beginTransaction();

            // Upload image using ImageService
            $imageService = new ImageService();
            $imageData = [
                'name' => $validated['certificate_serial'] . ' Certificate',
                'certificate_serial' => $validated['certificate_serial'],
                'year' => $validated['year'] ?? now()->year,
                'month' => $validated['month'] ?? now()->month,
                'fit_type' => 'stretch',
            ];

            $image = $imageService->storeImage($request->file('image'), $imageData, $user->id);

            // Create marriage record with image_id
            $marriageData = [
                'certificate_serial' => $validated['certificate_serial'],
                'marriage_date' => $validated['marriage_date'],
                'venue' => $validated['venue'],
                'county' => $validated['county'],
                'marriage_type_id' => $validated['marriage_type_id'],
                'image_id' => $image->id,
                'created_by' => $user->id,
                'system_status' => 'Pending',
            ];

            // Add optional fields
            $optionalFields = ['reg_date', 'sub_county', 'year', 'month'];
            foreach ($optionalFields as $field) {
                if (!empty($validated[$field])) {
                    $marriageData[$field] = $validated[$field];
                }
            }

            $marriage = Marriage::create($marriageData);

            DB::commit();

            return redirect()->route('marriages.show', $marriage)
                            ->with('success', 'Marriage record created successfully with image!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create marriage record: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function createFromImage($imageId)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'marriage_teller') {
            abort(403, 'Only marriage tellers can create marriages from images.');
        }

        // Get the available image
        $image = Image::findOrFail($imageId);
        
        // Verify this image is not already linked to a marriage
        if ($image->marriage) {
            abort(404, 'This image is already linked to a marriage record.');
        }
        
        // Load categories for the form
        $categories = Category::whereIn('type', ['marriage_type', 'marriage_status', 'verification_status', 'id_type'])
                            ->get();

        // Get counties with their constituencies for cascading dropdown
        $countiesWithConstituencies = County::select('name', 'constituency')
            ->get()
            ->groupBy('name')
            ->map(function($items) {
                return $items->pluck('constituency')->unique()->values();
            });

        return view('marriages.create-from-image', compact('image', 'categories', 'countiesWithConstituencies'));
    }

    public function storeFromImage(Request $request, $imageId)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'marriage_teller') {
            abort(403, 'Only marriage tellers can create marriages from images.');
        }

        // Get verification status category ID for 'Unverified'
        $unverifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id');

        // Validate the request with all new fields
        $validated = $request->validate([
            'certificate_serial' => 'required|string|max:255|unique:marriages,certificate_serial',
            'marriage_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'sub_county' => 'nullable|string|max:255',
            'reg_date' => 'nullable|date',
            'marriage_type_id' => 'required|exists:categories,id',
            'year' => 'nullable|integer|min:2000|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
            
            // Spouse fields
            'husband_name' => 'required|string|max:255',
            'husband_id_type' => 'nullable|string|max:255',
            'husband_id_number' => 'nullable|string|max:255',
            'husband_father_name' => 'nullable|string|max:255',
            'husband_mother_name' => 'nullable|string|max:255',
            'husband_occupation' => 'nullable|string|max:255',
            'husband_address' => 'nullable|string|max:500',
            
            'wife_name' => 'required|string|max:255',
            'wife_id_type' => 'nullable|string|max:255',
            'wife_id_number' => 'nullable|string|max:255',
            'wife_father_name' => 'nullable|string|max:255',
            'wife_mother_name' => 'nullable|string|max:255',
            'wife_occupation' => 'nullable|string|max:255',
            'wife_address' => 'nullable|string|max:500',
            
            // Witness fields
            'witness1_name' => 'required|string|max:255',
            'witness1_id_type' => 'nullable|string|max:255',
            'witness1_id_number' => 'nullable|string|max:255',
            'witness1_address' => 'nullable|string|max:500',
            
            'witness2_name' => 'required|string|max:255',
            'witness2_id_type' => 'nullable|string|max:255',
            'witness2_id_number' => 'nullable|string|max:255',
            'witness2_address' => 'nullable|string|max:500',
            
            // Marriage extension fields
            'mahr_agreed' => 'nullable|string',
            'mahr_paid' => 'nullable|string',
            'mahr_deferred' => 'nullable|string',
            'gifts' => 'nullable|string',
            'muslim_officer' => 'nullable|string',
            'church_org' => 'nullable|string',
            'pastor_name' => 'nullable|string',
            'entry_no' => 'nullable|string',
            'temple' => 'nullable|string',
            'dowry' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get the image
            $image = Image::findOrFail($imageId);

            // Verify this image is not already linked
            if ($image->marriage) {
                throw new \Exception('This image is already linked to a marriage record.');
            }

            // Create the marriage record with image_id
            $marriage = Marriage::create([
                'certificate_serial' => $validated['certificate_serial'],
                'marriage_date' => $validated['marriage_date'],
                'venue' => $validated['venue'],
                'county' => $validated['county'],
                'sub_county' => $validated['sub_county'] ?? null,
                'reg_date' => $validated['reg_date'] ?? null,
                'marriage_type_id' => $validated['marriage_type_id'],
                'verification_status_id' => $unverifiedStatusId,
                'year' => $validated['year'] ?? null,
                'month' => $validated['month'] ?? null,
                'image_id' => $imageId,
                'created_by' => $user->id,
                'verified_by' => $user->id,
                'updated_by' => $user->id,
                'system_status' => 'Pending',
            ]);

            // Create spouses
            $husband = $marriage->spouses()->create([
                'name' => $validated['husband_name'],
                'gender' => 'male',
                'id_type' => $validated['husband_id_type'] ?? null,
                'id_number' => $validated['husband_id_number'] ?? null,
                'father_name' => $validated['husband_father_name'] ?? null,
                'mother_name' => $validated['husband_mother_name'] ?? null,
                'occupation' => $validated['husband_occupation'] ?? null,
                'address' => $validated['husband_address'] ?? null,
            ]);

            $wife = $marriage->spouses()->create([
                'name' => $validated['wife_name'],
                'gender' => 'female',
                'id_type' => $validated['wife_id_type'] ?? null,
                'id_number' => $validated['wife_id_number'] ?? null,
                'father_name' => $validated['wife_father_name'] ?? null,
                'mother_name' => $validated['wife_mother_name'] ?? null,
                'occupation' => $validated['wife_occupation'] ?? null,
                'address' => $validated['wife_address'] ?? null,
            ]);

            // Create witnesses
            $marriage->witnesses()->create([
                'name' => $validated['witness1_name'],
                'id_type' => $validated['witness1_id_type'] ?? null,
                'id_number' => $validated['witness1_id_number'] ?? null,
                'address' => $validated['witness1_address'] ?? null,
            ]);

            $marriage->witnesses()->create([
                'name' => $validated['witness2_name'],
                'id_type' => $validated['witness2_id_type'] ?? null,
                'id_number' => $validated['witness2_id_number'] ?? null,
                'address' => $validated['witness2_address'] ?? null,
            ]);

            // Create marriage type extension if there are any extension fields
            $hasExtensionData = collect([
                'mahr_agreed', 'mahr_paid', 'mahr_deferred', 'gifts', 'muslim_officer',
                'church_org', 'pastor_name', 'entry_no', 'temple', 'dowry'
            ])->contains(function ($field) use ($validated) {
                return !empty($validated[$field]);
            });

            if ($hasExtensionData) {
                MarriageTypeExtension::create([
                    'marriage_id' => $marriage->id,
                    'mahr_agreed' => $validated['mahr_agreed'] ?? null,
                    'mahr_paid' => $validated['mahr_paid'] ?? null,
                    'mahr_deferred' => $validated['mahr_deferred'] ?? null,
                    'gifts' => $validated['gifts'] ?? null,
                    'muslim_officer' => $validated['muslim_officer'] ?? null,
                    'church_org' => $validated['church_org'] ?? null,
                    'pastor_name' => $validated['pastor_name'] ?? null,
                    'entry_no' => $validated['entry_no'] ?? null,
                    'temple' => $validated['temple'] ?? null,
                    'dowry' => $validated['dowry'] ?? null,
                    'created_by' => $user->id,
                ]);
            }

            DB::commit();

            return redirect()->route('marriages.show', $marriage)
                            ->with('success', 'Marriage record created successfully from image!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create marriage record: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function createFromPdf($pdfId)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'marriage_teller') {
            abort(403, 'Only marriage tellers can create marriages from PDFs.');
        }

        // Get the available PDF with relationships
        $pdf = PdfUpload::with(['county', 'uploader'])->findOrFail($pdfId);
        
        // Check if this PDF is already linked to a marriage
        $existingMarriage = null;
        if ($pdf->marriage) {
            $existingMarriage = Marriage::with([
                'spouses',
                'witnesses',
                'marriageExtension',
                'marriageType',
                'marriageStatus',
                'verificationStatus',
                'createdBy'
            ])->find($pdf->marriage->id);
        }
        
        // Prepare existing marriage data for JavaScript
        $existingMarriageData = null;
        if ($existingMarriage) {
            $existingMarriageData = [
                'id' => $existingMarriage->id,
                'certificate_serial' => $existingMarriage->certificate_serial,
                'marriage_date' => $existingMarriage->marriage_date,
                'reg_date' => $existingMarriage->reg_date,
                'venue' => $existingMarriage->venue,
                'county' => $existingMarriage->county,
                'sub_county' => $existingMarriage->sub_county,
                'marriage_type_id' => $existingMarriage->marriage_type_id,
                'marriage_status_id' => $existingMarriage->marriage_status_id,
                'verification_status_id' => $existingMarriage->verification_status_id,
                'year' => $existingMarriage->year,
                'month' => $existingMarriage->month,
                'system_status' => $existingMarriage->system_status,
                'created_at' => optional($existingMarriage->created_at)->toDateTimeString(),
            ];
            
            // Add spouses
            if ($existingMarriage->spouses) {
                $existingMarriageData['spouses'] = $existingMarriage->spouses->map(function($spouse) {
                    return [
                        'name' => $spouse->name,
                        'gender' => $spouse->gender,
                        'age' => $spouse->age,
                        'id_type' => $spouse->id_type,
                        'id_number' => $spouse->id_number,
                        'father_name' => $spouse->father_name,
                        'father_occupation' => $spouse->father_occupation,
                        'father_residence' => $spouse->father_residence,
                        'mother_name' => $spouse->mother_name,
                        'mother_occupation' => $spouse->mother_occupation,
                        'mother_residence' => $spouse->mother_residence,
                        'occupation' => $spouse->occupation,
                        'residence' => $spouse->residence,
                    ];
                })->toArray();
            }
            
            // Add witnesses
            if ($existingMarriage->witnesses) {
                $existingMarriageData['witnesses'] = $existingMarriage->witnesses->map(function($witness) {
                    return [
                        'name' => $witness->name,
                        'side' => $witness->side,
                        'id_type' => $witness->id_type,
                        'id_number' => $witness->id_number,
                        'address' => $witness->address,
                    ];
                })->toArray();
            }
            
            // Add extension
            if ($existingMarriage->marriageExtension) {
                $existingMarriageData['extension'] = [
                    'mahr_agreed' => $existingMarriage->marriageExtension->mahr_agreed,
                    'mahr_paid' => $existingMarriage->marriageExtension->mahr_paid,
                    'mahr_deferred' => $existingMarriage->marriageExtension->mahr_deferred,
                    'gifts' => $existingMarriage->marriageExtension->gifts,
                    'muslim_officer' => $existingMarriage->marriageExtension->muslim_officer,
                    'church_org' => $existingMarriage->marriageExtension->church_org,
                    'pastor_name' => $existingMarriage->marriageExtension->pastor_name,
                    'entry_no' => $existingMarriage->marriageExtension->entry_no,
                    'temple' => $existingMarriage->marriageExtension->temple,
                    'dowry' => $existingMarriage->marriageExtension->dowry,
                ];
            }
        }
        
        // Load categories for the form
        $categories = Category::whereIn('type', ['marriage_type', 'marriage_status', 'verification_status', 'id_type'])
                            ->get();

        // Get all counties with their constituencies
        $counties = County::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        // Get all constituencies grouped by county
        $countiesWithConstituencies = County::select('name', 'constituency')
            ->orderBy('name')
            ->orderBy('constituency')
            ->get()
            ->groupBy('name')
            ->map(function($items) {
                return $items->pluck('constituency')->unique()->values()->toArray();
            })
            ->toArray();

        // Get all constituencies for the dropdown (flat list)
        $constituencies = County::select('constituency')
            ->distinct()
            ->orderBy('constituency')
            ->pluck('constituency')
            ->toArray();

        // Get wards for residence fields
        $wards = County::select('wards')
            ->distinct()
            ->orderBy('wards')
            ->pluck('wards')
            ->toArray();

        $pdfData = [
            'id' => $pdf->id,
            'certificate_serial' => $pdf->certificate_serial,
            'year' => $pdf->year,
            'month' => $pdf->month,
            'county' => $pdf->county->name ?? $pdf->county_code ?? 'Nairobi',
            'county_code' => $pdf->county_code,
        ];

        return view('marriages.create-from-pdf', compact(
            'pdf', 
            'categories', 
            'counties',
            'countiesWithConstituencies',
            'constituencies',
            'wards',
            'existingMarriage',
            'existingMarriageData',
            'pdfData'
        ));
    }

    public function storeFromPdf(Request $request, $pdfId)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'marriage_teller') {
            return back()->with('error', 'Only marriage tellers can create marriages from PDFs.');
        }

        // Get verification status category ID for 'Unverified'
        $unverifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id');

        // Check if this is an update
        $isUpdate = $request->has('is_edit_mode') && $request->is_edit_mode == '1';
        $marriageId = $request->get('marriage_id');
        
        if ($isUpdate && $marriageId) {
            // Update existing marriage
            return $this->updateMarriageFromPdf($request, $pdfId, $marriageId);
        }

        // Validate the request with all new fields
        $validated = $request->validate([
            'certificate_serial' => 'required|string|max:255|unique:marriages,certificate_serial',
            'marriage_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'sub_county' => 'nullable|string|max:255',
            'reg_date' => 'nullable|date',
            'marriage_type_id' => 'required|exists:categories,id',
            'marriage_status_id' => 'nullable|exists:categories,id', 
            'year' => 'nullable|integer|min:2000|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
            
            // Spouse fields with age
            'husband_name' => 'required|string|max:255',
            'husband_age' => 'nullable|integer|min:18|max:120',
            'husband_father_name' => 'nullable|string|max:255',
            'husband_father_occupation' => 'nullable|string|max:255',
            'husband_father_residence' => 'nullable|string|max:500',
            'husband_mother_name' => 'nullable|string|max:255',
            'husband_mother_occupation' => 'nullable|string|max:255',
            'husband_mother_residence' => 'nullable|string|max:500',
            'husband_occupation' => 'nullable|string|max:255',
            'husband_residence' => 'nullable|string|max:500',
            
            'wife_name' => 'required|string|max:255',
            'wife_age' => 'nullable|integer|min:18|max:120',
            'wife_father_name' => 'nullable|string|max:255',
            'wife_father_occupation' => 'nullable|string|max:255',
            'wife_father_residence' => 'nullable|string|max:500',
            'wife_mother_name' => 'nullable|string|max:255',
            'wife_mother_occupation' => 'nullable|string|max:255',
            'wife_mother_residence' => 'nullable|string|max:500',
            'wife_occupation' => 'nullable|string|max:255',
            'wife_residence' => 'nullable|string|max:500',
            
            // Witness fields with side selection
            'witness1_name' => 'required|string|max:255',
            'witness1_side' => 'nullable|in:husband,wife,both', 
            
            'witness2_name' => 'required|string|max:255',
            'witness2_side' => 'nullable|in:husband,wife,both',
            
            // Marriage extension fields
            'mahr_agreed' => 'nullable|string',
            'mahr_paid' => 'nullable|string',
            'mahr_deferred' => 'nullable|string',
            'gifts' => 'nullable|string',
            'muslim_officer' => 'nullable|string',
            'church_org' => 'nullable|string',
            'pastor_name' => 'nullable|string',
            'entry_no' => 'nullable|string',
            'temple' => 'nullable|string',
            'dowry' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get the PDF
            $pdf = PdfUpload::findOrFail($pdfId);

            // Verify this PDF is not already linked
            if ($pdf->marriage) {
                throw new \Exception('This PDF is already linked to a marriage record.');
            }

            // Get county ID based on county name
            $countyRecord = County::where('name', $validated['county'])->first();
            $countyId = $countyRecord ? $countyRecord->id : null;

            // Get sub_county ID (constituency)
            $subCountyId = null;
            if (!empty($validated['sub_county'])) {
                $subCountyRecord = County::where('constituency', $validated['sub_county'])
                    ->where('name', $validated['county'])
                    ->first();
                $subCountyId = $subCountyRecord ? $subCountyRecord->id : null;
            }

            // Convert all string fields to uppercase
            $uppercasedData = collect($validated)->map(function ($item, $key) {
                // Don't uppercase date fields, numbers, IDs, or select options
                if (in_array($key, ['marriage_date', 'reg_date', 'year', 'month', 'husband_age', 'wife_age', 
                                'marriage_type_id', 'marriage_status_id', 'verification_status_id',
                                'witness1_side', 'witness2_side']) || 
                    is_numeric($item) || 
                    str_ends_with($key, '_id')) {
                    return $item;
                }
                return is_string($item) ? strtoupper($item) : $item;
            })->toArray();

            // Create the marriage record with pdf_id
            $marriage = Marriage::create([
                'certificate_serial' => $uppercasedData['certificate_serial'],
                'marriage_date' => $uppercasedData['marriage_date'],
                'venue' => $uppercasedData['venue'],
                'county' => $uppercasedData['county'],
                'county_id' => $countyId, 
                'sub_county' => $uppercasedData['sub_county'] ?? null,
                'sub_county_id' => $subCountyId,
                'reg_date' => $uppercasedData['reg_date'] ?? null,
                'marriage_type_id' => $uppercasedData['marriage_type_id'],
                'marriage_status_id' => $uppercasedData['marriage_status_id'] ?? null,
                'verification_status_id' => $unverifiedStatusId,
                'year' => $uppercasedData['year'] ?? $pdf->year,
                'month' => $uppercasedData['month'] ?? $pdf->month,
                'pdf_id' => $pdfId,
                'image_id' => null,
                'created_by' => $user->id,
                'verified_by' => $user->id,
                'updated_by' => $user->id,
                'system_status' => 'Pending',
            ]);

            // Create husband
            $husband = $marriage->spouses()->create([
                'name' => $uppercasedData['husband_name'],
                'spouse_type' => 'husband', // Changed from 'gender' to 'spouse_type'
                'age' => $uppercasedData['husband_age'] ?? null,
                'father_name' => $uppercasedData['husband_father_name'] ?? null,
                'father_occupation' => $uppercasedData['husband_father_occupation'] ?? null,
                'father_residence' => $uppercasedData['husband_father_residence'] ?? null,
                'mother_name' => $uppercasedData['husband_mother_name'] ?? null,
                'mother_occupation' => $uppercasedData['husband_mother_occupation'] ?? null,
                'mother_residence' => $uppercasedData['husband_mother_residence'] ?? null,
                'occupation' => $uppercasedData['husband_occupation'] ?? null,
                'residence' => $uppercasedData['husband_residence'] ?? null,
                'county' => $uppercasedData['county'] ?? null,
                'created_by' => $user->id,
                'verified_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Create wife
            $wife = $marriage->spouses()->create([
                'name' => $uppercasedData['wife_name'],
                'spouse_type' => 'wife', // Changed from 'gender' to 'spouse_type'
                'age' => $uppercasedData['wife_age'] ?? null,
                'father_name' => $uppercasedData['wife_father_name'] ?? null,
                'father_occupation' => $uppercasedData['wife_father_occupation'] ?? null,
                'father_residence' => $uppercasedData['wife_father_residence'] ?? null,
                'mother_name' => $uppercasedData['wife_mother_name'] ?? null,
                'mother_occupation' => $uppercasedData['wife_mother_occupation'] ?? null,
                'mother_residence' => $uppercasedData['wife_mother_residence'] ?? null,
                'occupation' => $uppercasedData['wife_occupation'] ?? null,
                'residence' => $uppercasedData['wife_residence'] ?? null,
                'county' => $uppercasedData['county'] ?? null,
                'created_by' => $user->id,
                'verified_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Create witnesses
            $marriage->witnesses()->create([
                'name' => $uppercasedData['witness1_name'],
                'spouse_side' => $uppercasedData['witness1_side'] ?? null, // Changed from 'side' to 'spouse_side'
                'created_by' => $user->id,
                'verified_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $marriage->witnesses()->create([
                'name' => $uppercasedData['witness2_name'],
                'spouse_side' => $uppercasedData['witness2_side'] ?? null, // Changed from 'side' to 'spouse_side'
                'created_by' => $user->id,
                'verified_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Create marriage type extension if there are any extension fields
            $hasExtensionData = collect([
                'mahr_agreed', 'mahr_paid', 'mahr_deferred', 'gifts', 'muslim_officer',
                'church_org', 'pastor_name', 'entry_no', 'temple', 'dowry'
            ])->contains(function ($field) use ($uppercasedData) {
                return !empty($uppercasedData[$field]);
            });

            if ($hasExtensionData) {
                MarriageTypeExtension::create([
                    'marriage_id' => $marriage->id,
                    'mahr_agreed' => $uppercasedData['mahr_agreed'] ?? null,
                    'mahr_paid' => $uppercasedData['mahr_paid'] ?? null,
                    'mahr_deferred' => $uppercasedData['mahr_deferred'] ?? null,
                    'gifts' => $uppercasedData['gifts'] ?? null,
                    'muslim_officer' => $uppercasedData['muslim_officer'] ?? null,
                    'church_org' => $uppercasedData['church_org'] ?? null,
                    'pastor_name' => $uppercasedData['pastor_name'] ?? null,
                    'entry_no' => $uppercasedData['entry_no'] ?? null,
                    'temple' => $uppercasedData['temple'] ?? null,
                    'dowry' => $uppercasedData['dowry'] ?? null,
                    'created_by' => $user->id,
                ]);
            }

            DB::commit();

            // Debug: Check what was saved
            \Log::info('Marriage created with ID: ' . $marriage->id);
            \Log::info('Husband saved: ' . ($husband ? 'Yes' : 'No'));
            \Log::info('Wife saved: ' . ($wife ? 'Yes' : 'No'));
            \Log::info('Witnesses saved: ' . $marriage->witnesses()->count());

            return redirect()->route('marriages.show', $marriage)
                            ->with('success', 'Marriage record created successfully from PDF!')
                            ->with('fields_inserted', [
                                'Marriage Created: ' . $marriage->certificate_serial,
                                'Husband: ' . $uppercasedData['husband_name'],
                                'Wife: ' . $uppercasedData['wife_name'],
                                'Witnesses: ' . $uppercasedData['witness1_name'] . ', ' . $uppercasedData['witness2_name']
                            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in storeFromPdf: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->with('error', 'Failed to create marriage record: ' . $e->getMessage())
                        ->withInput();
        }
    }

    private function updateMarriageFromPdf(Request $request, $pdfId, $marriageId)
    {
        $user = Auth::user();
        
        if ($user->role->name !== 'marriage_teller') {
            return back()->with('error', 'Only marriage tellers can update marriages from PDFs.');
        }

        // Get verification status category IDs
        $verificationStatuses = Category::where('type', 'verification_status')
            ->whereIn('name', ['Verified', 'Rejected', 'Unverified'])
            ->pluck('id', 'name');

        // Validate the request
        $validated = $request->validate([
            'certificate_serial' => 'required|string|max:255|unique:marriages,certificate_serial,' . $marriageId,
            'marriage_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'sub_county' => 'nullable|string|max:255',
            'reg_date' => 'nullable|date',
            'marriage_type_id' => 'required|exists:categories,id',
            'verification_status' => 'nullable|in:Verified,Rejected,Unverified',
            'year' => 'nullable|integer|min:2000|max:2030',
            'month' => 'nullable|integer|min:1|max:12',
            
            // Spouse fields
            'husband_name' => 'required|string|max:255',
            'husband_age' => 'nullable|integer|min:18|max:120',
            'husband_id_type' => 'nullable|string|max:255',
            'husband_id_number' => 'nullable|string|max:255',
            'husband_father_name' => 'nullable|string|max:255',
            'husband_father_occupation' => 'nullable|string|max:255',
            'husband_father_residence' => 'nullable|string|max:500',
            'husband_mother_name' => 'nullable|string|max:255',
            'husband_mother_occupation' => 'nullable|string|max:255',
            'husband_mother_residence' => 'nullable|string|max:500',
            'husband_occupation' => 'nullable|string|max:255',
            'husband_residence' => 'nullable|string|max:500',
            'husband_address' => 'nullable|string|max:500',
            
            'wife_name' => 'required|string|max:255',
            'wife_age' => 'nullable|integer|min:18|max:120',
            'wife_id_type' => 'nullable|string|max:255',
            'wife_id_number' => 'nullable|string|max:255',
            'wife_father_name' => 'nullable|string|max:255',
            'wife_father_occupation' => 'nullable|string|max:255',
            'wife_father_residence' => 'nullable|string|max:500',
            'wife_mother_name' => 'nullable|string|max:255',
            'wife_mother_occupation' => 'nullable|string|max:255',
            'wife_mother_residence' => 'nullable|string|max:500',
            'wife_occupation' => 'nullable|string|max:255',
            'wife_residence' => 'nullable|string|max:500',
            'wife_address' => 'nullable|string|max:500',
            
            // Witness fields
            'witness1_name' => 'required|string|max:255',
            'witness1_side' => 'nullable|in:husband,wife,both',
            'witness1_id_type' => 'nullable|string|max:255',
            'witness1_id_number' => 'nullable|string|max:255',
            'witness1_address' => 'nullable|string|max:500',
            
            'witness2_name' => 'required|string|max:255',
            'witness2_side' => 'nullable|in:husband,wife,both',
            'witness2_id_type' => 'nullable|string|max:255',
            'witness2_id_number' => 'nullable|string|max:255',
            'witness2_address' => 'nullable|string|max:500',
            
            // Marriage extension fields
            'mahr_agreed' => 'nullable|string',
            'mahr_paid' => 'nullable|string',
            'mahr_deferred' => 'nullable|string',
            'gifts' => 'nullable|string',
            'muslim_officer' => 'nullable|string',
            'church_org' => 'nullable|string',
            'pastor_name' => 'nullable|string',
            'entry_no' => 'nullable|string',
            'temple' => 'nullable|string',
            'dowry' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get the marriage record
            $marriage = Marriage::findOrFail($marriageId);
            
            // Get the PDF
            $pdf = PdfUpload::findOrFail($pdfId);

            // Convert all string fields to uppercase
            $uppercasedData = collect($validated)->map(function ($item, $key) {
                // Don't uppercase date fields, numbers, IDs, or select options
                if (in_array($key, ['marriage_date', 'reg_date', 'year', 'month', 'husband_age', 'wife_age', 
                                'marriage_type_id', 'marriage_status_id', 'verification_status_id',
                                'witness1_side', 'witness2_side']) || 
                    is_numeric($item) || 
                    str_ends_with($key, '_id')) {
                    return $item;
                }
                return is_string($item) ? strtoupper($item) : $item;
            })->toArray();

            // Prepare update data
            $updateData = [
                'certificate_serial' => $uppercasedData['certificate_serial'],
                'marriage_date' => $uppercasedData['marriage_date'],
                'venue' => $uppercasedData['venue'],
                'county' => $uppercasedData['county'],
                'sub_county' => $uppercasedData['sub_county'] ?? null,
                'reg_date' => $uppercasedData['reg_date'] ?? null,
                'marriage_type_id' => $uppercasedData['marriage_type_id'],
                'year' => $uppercasedData['year'] ?? $pdf->year,
                'month' => $uppercasedData['month'] ?? $pdf->month,
                'updated_by' => $user->id,
            ];

            // Handle verification status if provided
            if ($request->has('verification_status')) {
                $statusName = $validated['verification_status'];
                if (isset($verificationStatuses[$statusName])) {
                    $updateData['verification_status_id'] = $verificationStatuses[$statusName];
                }
            }

            // Update the marriage record
            $marriage->update($updateData);

            // Update or create spouses
            $husband = $marriage->spouses()->where('gender', 'male')->first();
            if ($husband) {
                $husband->update([
                    'name' => $uppercasedData['husband_name'],
                    'age' => $uppercasedData['husband_age'] ?? null,
                    'id_type' => $uppercasedData['husband_id_type'] ?? null,
                    'id_number' => $uppercasedData['husband_id_number'] ?? null,
                    'father_name' => $uppercasedData['husband_father_name'] ?? null,
                    'father_occupation' => $uppercasedData['husband_father_occupation'] ?? null,
                    'father_residence' => $uppercasedData['husband_father_residence'] ?? null,
                    'mother_name' => $uppercasedData['husband_mother_name'] ?? null,
                    'mother_occupation' => $uppercasedData['husband_mother_occupation'] ?? null,
                    'mother_residence' => $uppercasedData['husband_mother_residence'] ?? null,
                    'occupation' => $uppercasedData['husband_occupation'] ?? null,
                    'residence' => $uppercasedData['husband_residence'] ?? null,
                    'address' => $uppercasedData['husband_address'] ?? null,
                ]);
            } else {
                $marriage->spouses()->create([
                    'name' => $uppercasedData['husband_name'],
                    'gender' => 'male',
                    'age' => $uppercasedData['husband_age'] ?? null,
                    'id_type' => $uppercasedData['husband_id_type'] ?? null,
                    'id_number' => $uppercasedData['husband_id_number'] ?? null,
                    'father_name' => $uppercasedData['husband_father_name'] ?? null,
                    'father_occupation' => $uppercasedData['husband_father_occupation'] ?? null,
                    'father_residence' => $uppercasedData['husband_father_residence'] ?? null,
                    'mother_name' => $uppercasedData['husband_mother_name'] ?? null,
                    'mother_occupation' => $uppercasedData['husband_mother_occupation'] ?? null,
                    'mother_residence' => $uppercasedData['husband_mother_residence'] ?? null,
                    'occupation' => $uppercasedData['husband_occupation'] ?? null,
                    'residence' => $uppercasedData['husband_residence'] ?? null,
                    'address' => $uppercasedData['husband_address'] ?? null,
                ]);
            }

            $wife = $marriage->spouses()->where('gender', 'female')->first();
            if ($wife) {
                $wife->update([
                    'name' => $uppercasedData['wife_name'],
                    'age' => $uppercasedData['wife_age'] ?? null,
                    'id_type' => $uppercasedData['wife_id_type'] ?? null,
                    'id_number' => $uppercasedData['wife_id_number'] ?? null,
                    'father_name' => $uppercasedData['wife_father_name'] ?? null,
                    'father_occupation' => $uppercasedData['wife_father_occupation'] ?? null,
                    'father_residence' => $uppercasedData['wife_father_residence'] ?? null,
                    'mother_name' => $uppercasedData['wife_mother_name'] ?? null,
                    'mother_occupation' => $uppercasedData['wife_mother_occupation'] ?? null,
                    'mother_residence' => $uppercasedData['wife_mother_residence'] ?? null,
                    'occupation' => $uppercasedData['wife_occupation'] ?? null,
                    'residence' => $uppercasedData['wife_residence'] ?? null,
                    'address' => $uppercasedData['wife_address'] ?? null,
                ]);
            } else {
                $marriage->spouses()->create([
                    'name' => $uppercasedData['wife_name'],
                    'gender' => 'female',
                    'age' => $uppercasedData['wife_age'] ?? null,
                    'id_type' => $uppercasedData['wife_id_type'] ?? null,
                    'id_number' => $uppercasedData['wife_id_number'] ?? null,
                    'father_name' => $uppercasedData['wife_father_name'] ?? null,
                    'father_occupation' => $uppercasedData['wife_father_occupation'] ?? null,
                    'father_residence' => $uppercasedData['wife_father_residence'] ?? null,
                    'mother_name' => $uppercasedData['wife_mother_name'] ?? null,
                    'mother_occupation' => $uppercasedData['wife_mother_occupation'] ?? null,
                    'mother_residence' => $uppercasedData['wife_mother_residence'] ?? null,
                    'occupation' => $uppercasedData['wife_occupation'] ?? null,
                    'residence' => $uppercasedData['wife_residence'] ?? null,
                    'address' => $uppercasedData['wife_address'] ?? null,
                ]);
            }

            // Update or create witnesses
            $witnesses = $marriage->witnesses()->get();
            if ($witnesses->count() > 0) {
                $witnesses[0]->update([
                    'name' => $uppercasedData['witness1_name'],
                    'side' => $uppercasedData['witness1_side'] ?? null,
                    'id_type' => $uppercasedData['witness1_id_type'] ?? null,
                    'id_number' => $uppercasedData['witness1_id_number'] ?? null,
                    'address' => $uppercasedData['witness1_address'] ?? null,
                ]);
                
                if ($witnesses->count() > 1) {
                    $witnesses[1]->update([
                        'name' => $uppercasedData['witness2_name'],
                        'side' => $uppercasedData['witness2_side'] ?? null,
                        'id_type' => $uppercasedData['witness2_id_type'] ?? null,
                        'id_number' => $uppercasedData['witness2_id_number'] ?? null,
                        'address' => $uppercasedData['witness2_address'] ?? null,
                    ]);
                } else {
                    $marriage->witnesses()->create([
                        'name' => $uppercasedData['witness2_name'],
                        'side' => $uppercasedData['witness2_side'] ?? null,
                        'id_type' => $uppercasedData['witness2_id_type'] ?? null,
                        'id_number' => $uppercasedData['witness2_id_number'] ?? null,
                        'address' => $uppercasedData['witness2_address'] ?? null,
                    ]);
                }
            } else {
                $marriage->witnesses()->create([
                    'name' => $uppercasedData['witness1_name'],
                    'side' => $uppercasedData['witness1_side'] ?? null,
                    'id_type' => $uppercasedData['witness1_id_type'] ?? null,
                    'id_number' => $uppercasedData['witness1_id_number'] ?? null,
                    'address' => $uppercasedData['witness1_address'] ?? null,
                ]);
                
                $marriage->witnesses()->create([
                    'name' => $uppercasedData['witness2_name'],
                    'side' => $uppercasedData['witness2_side'] ?? null,
                    'id_type' => $uppercasedData['witness2_id_type'] ?? null,
                    'id_number' => $uppercasedData['witness2_id_number'] ?? null,
                    'address' => $uppercasedData['witness2_address'] ?? null,
                ]);
            }

            // Update or create marriage extension
            $hasExtensionData = collect([
                'mahr_agreed', 'mahr_paid', 'mahr_deferred', 'gifts', 'muslim_officer',
                'church_org', 'pastor_name', 'entry_no', 'temple', 'dowry'
            ])->contains(function ($field) use ($uppercasedData) {
                return !empty($uppercasedData[$field]);
            });

            if ($hasExtensionData) {
                $extension = $marriage->marriageExtension;
                if ($extension) {
                    $extension->update([
                        'mahr_agreed' => $uppercasedData['mahr_agreed'] ?? null,
                        'mahr_paid' => $uppercasedData['mahr_paid'] ?? null,
                        'mahr_deferred' => $uppercasedData['mahr_deferred'] ?? null,
                        'gifts' => $uppercasedData['gifts'] ?? null,
                        'muslim_officer' => $uppercasedData['muslim_officer'] ?? null,
                        'church_org' => $uppercasedData['church_org'] ?? null,
                        'pastor_name' => $uppercasedData['pastor_name'] ?? null,
                        'entry_no' => $uppercasedData['entry_no'] ?? null,
                        'temple' => $uppercasedData['temple'] ?? null,
                        'dowry' => $uppercasedData['dowry'] ?? null,
                        'updated_by' => $user->id,
                    ]);
                } else {
                    MarriageTypeExtension::create([
                        'marriage_id' => $marriage->id,
                        'mahr_agreed' => $uppercasedData['mahr_agreed'] ?? null,
                        'mahr_paid' => $uppercasedData['mahr_paid'] ?? null,
                        'mahr_deferred' => $uppercasedData['mahr_deferred'] ?? null,
                        'gifts' => $uppercasedData['gifts'] ?? null,
                        'muslim_officer' => $uppercasedData['muslim_officer'] ?? null,
                        'church_org' => $uppercasedData['church_org'] ?? null,
                        'pastor_name' => $uppercasedData['pastor_name'] ?? null,
                        'entry_no' => $uppercasedData['entry_no'] ?? null,
                        'temple' => $uppercasedData['temple'] ?? null,
                        'dowry' => $uppercasedData['dowry'] ?? null,
                        'created_by' => $user->id,
                    ]);
                }
            }

            DB::commit();

            // Track updated fields for the success alert
            $updatedFields = collect($uppercasedData)
                ->filter(function ($value, $key) {
                    return !empty($value) && !in_array($key, ['_token', '_method']);
                })
                ->keys()
                ->map(function ($field) {
                    // Format field names for display
                    return str_replace('_', ' ', ucwords($field));
                })
                ->toArray();

            // Return a redirect response with flash data for alert
            return redirect()->route('marriages.show', $marriage)
                            ->with('success', 'Marriage record updated successfully from PDF!')
                            ->with('fields_inserted', $updatedFields);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update marriage record: ' . $e->getMessage())
                        ->withInput();
        }
    }

    private function transformMarriageDataForForm($marriage)
    {
        if (!$marriage) {
            return [];
        }

        // Transform main marriage data
        $data = [
            'id' => $marriage->id,
            'certificate_serial' => $marriage->certificate_serial,
            'marriage_date' => $marriage->marriage_date,
            'reg_date' => $marriage->reg_date,
            'venue' => $marriage->venue,
            'county' => $marriage->county,
            'sub_county' => $marriage->sub_county,
            'marriage_type_id' => $marriage->marriage_type_id,
            'marriage_status_id' => $marriage->marriage_status_id,
            'verification_status_id' => $marriage->verification_status_id,
            'year' => $marriage->year,
            'month' => $marriage->month,
            'system_status' => $marriage->system_status,
            'created_at' => $marriage->created_at,
            
            // Spouse data
            'husband' => null,
            'wife' => null,
            
            // Witness data
            'witnesses' => [],
            
            // Extension data
            'extension' => null
        ];

        // Extract husband and wife from spouses
        if ($marriage->spouses && $marriage->spouses->count() > 0) {
            $husband = $marriage->spouses->firstWhere('gender', 'male');
            $wife = $marriage->spouses->firstWhere('gender', 'female');
            
            if ($husband) {
                $data['husband'] = [
                    'name' => $husband->name,
                    'age' => $husband->age,
                    'id_type' => $husband->id_type,
                    'id_number' => $husband->id_number,
                    'father_name' => $husband->father_name,
                    'father_occupation' => $husband->father_occupation,
                    'father_residence' => $husband->father_residence,
                    'mother_name' => $husband->mother_name,
                    'mother_occupation' => $husband->mother_occupation,
                    'mother_residence' => $husband->mother_residence,
                    'occupation' => $husband->occupation,
                    'residence' => $husband->residence,
                    'address' => $husband->address,
                ];
            }
            
            if ($wife) {
                $data['wife'] = [
                    'name' => $wife->name,
                    'age' => $wife->age,
                    'id_type' => $wife->id_type,
                    'id_number' => $wife->id_number,
                    'father_name' => $wife->father_name,
                    'father_occupation' => $wife->father_occupation,
                    'father_residence' => $wife->father_residence,
                    'mother_name' => $wife->mother_name,
                    'mother_occupation' => $wife->mother_occupation,
                    'mother_residence' => $wife->mother_residence,
                    'occupation' => $wife->occupation,
                    'residence' => $wife->residence,
                    'address' => $wife->address,
                ];
            }
        }

        // Extract witnesses
        if ($marriage->witnesses && $marriage->witnesses->count() > 0) {
            $data['witnesses'] = $marriage->witnesses->map(function($witness) {
                return [
                    'name' => $witness->name,
                    'side' => $witness->side,
                    'id_type' => $witness->id_type,
                    'id_number' => $witness->id_number,
                    'address' => $witness->address,
                ];
            })->toArray();
        }

        // Extract marriage extension data
        if ($marriage->marriageExtension) {
            $data['extension'] = [
                'mahr_agreed' => $marriage->marriageExtension->mahr_agreed,
                'mahr_paid' => $marriage->marriageExtension->mahr_paid,
                'mahr_deferred' => $marriage->marriageExtension->mahr_deferred,
                'gifts' => $marriage->marriageExtension->gifts,
                'muslim_officer' => $marriage->marriageExtension->muslim_officer,
                'church_org' => $marriage->marriageExtension->church_org,
                'pastor_name' => $marriage->marriageExtension->pastor_name,
                'entry_no' => $marriage->marriageExtension->entry_no,
                'temple' => $marriage->marriageExtension->temple,
                'dowry' => $marriage->marriageExtension->dowry,
            ];
        }

        return $data;
    }
        

    private function calculateCompletionRate($marriage)
    {
        $totalFields = 0;
        $completedFields = 0;

        // Basic marriage info
        $basicFields = ['certificate_serial', 'marriage_date', 'reg_date', 'venue'];
        $totalFields += count($basicFields);
        foreach ($basicFields as $field) {
            if (!empty($marriage->$field)) {
                $completedFields++;
            }
        }

        // Location fields
        $locationFields = ['county', 'sub_county'];
        $totalFields += count($locationFields);
        foreach ($locationFields as $field) {
            if (!empty($marriage->$field)) {
                $completedFields++;
            }
        }

        // Spouses (2 required)
        $totalFields += 2; // We expect 2 spouses
        $completedFields += min($marriage->spouses->count(), 2);

        // Witnesses (2 required)
        $totalFields += 2; // We expect 2 witnesses
        $completedFields += min($marriage->witnesses->count(), 2);

        // Calculate percentage
        return $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;
    }

  
    public function show($id)
    {
        $user = Auth::user();
        $role = $user->role->name;

        // Load marriage record first
        $marriage = Marriage::with(['spouses', 'witnesses', 'marriageExtension', 'createdBy', 'image', 'pdf'])
                            ->findOrFail($id);

        // Role-based access control
        switch ($role) {
            case 'admin':
            case 'marriage_registrar':
                // Full access - no restrictions
                break;

            case 'marriage_teller':
                // Marriage teller can see:
                // 1. Marriages they created themselves
                // 2. Marriages that have images uploaded by data clerks (for completion)
                $canView = $marriage->created_by == $user->id || 
                        (($marriage->image_id && $marriage->image && $marriage->image->uploader->role->name === 'data_clerk') ||
                         ($marriage->pdf_id && $marriage->pdf && $marriage->pdf->uploader->role->name === 'data_clerk'));
                
                if (!$canView) {
                    abort(403, 'You do not have permission to access this marriage record.');
                }
                break;

            case 'data_clerk':
                // Data clerks cannot view marriages at all
                abort(403, 'You do not have permission to access marriage records.');

            case 'user':
            default:
                // Regular users cannot view any marriage records
                abort(403, 'You do not have permission to access this marriage record.');
        }

        // Calculate completion rate if needed
        $completionRate = $this->calculateCompletionRate($marriage);

        // Load categories
        $categories = Category::whereIn('type', ['marriage_type', 'marriage_status', 'verification_status', 'id_type'])
                            ->get();

        // Show marriage page based on role
        return $this->showMarriageForRole($marriage, $categories, $role, $user);
    }

    public function edit(Marriage $marriage)
    {
        $user = Auth::user();
        
        if (!$this->canEditMarriage($marriage, $user)) {
            abort(403, 'You do not have permission to edit this marriage record.');
        }

        $categories = Category::whereIn('type', ['marriage_type', 'marriage_status', 'verification_status', 'id_type'])
                            ->get();

        return view('marriages.edit', compact('marriage', 'categories'));
    }

    public function update(Request $request, Marriage $marriage)
    {
        $user = Auth::user();
        
        if (!$this->canEditMarriage($marriage, $user)) {
            abort(403, 'You do not have permission to edit this marriage record.');
        }

        // Get verification status category IDs
        $verificationStatuses = Category::where('type', 'verification_status')
            ->whereIn('name', ['Verified', 'Rejected', 'Unverified'])
            ->pluck('id', 'name');

        $validated = $request->validate([
            'certificate_serial' => 'required|string|max:255|unique:marriages,certificate_serial,' . $marriage->id,
            'marriage_date' => 'required|date',
            'venue' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'marriage_type_id' => 'required|exists:categories,id',
            // Add other validation rules as needed
            'verification_status' => 'nullable|in:Verified,Rejected,Unverified',
            'verification_notes' => 'nullable|string|max:500',
            'system_status' => 'nullable|in:Pending,Completed',
        ]);

        $data = [
            'certificate_serial' => $validated['certificate_serial'],
            'marriage_date' => $validated['marriage_date'],
            'venue' => $validated['venue'],
            'county' => $validated['county'],
            'marriage_type_id' => $validated['marriage_type_id'],
            'updated_by' => $user->id,
        ];

        // Handle registrar/admin specific fields
        if (in_array($user->role->name, ['marriage_registrar', 'admin'])) {
            if ($request->has('verification_status')) {
                $statusName = $validated['verification_status'];
                if (isset($verificationStatuses[$statusName])) {
                    $data['verification_status_id'] = $verificationStatuses[$statusName];
                    $data['verified_by'] = $user->id;
                }
            }
            if ($request->has('verification_notes')) {
                $data['verification_notes'] = $validated['verification_notes'];
            }
            if ($request->has('system_status')) {
                $data['system_status'] = $validated['system_status'];
            }
        }

        // Marriage teller can only mark as completed
        if ($user->role->name === 'marriage_teller' && $marriage->system_status === 'Pending' && $request->has('mark_completed')) {
            $data['system_status'] = 'Completed';
        }

        $marriage->update($data);
        
        return redirect()->route('marriages.show', $marriage)
            ->with('success', 'Marriage record updated successfully.');
    }

    public function destroy(Marriage $marriage)
    {
        if (!$this->canDeleteMarriage($marriage, Auth::user())) {
            abort(403, 'You do not have permission to delete this marriage record.');
        }

        if ($marriage->image_main) {
            FileService::deleteFile($marriage->image_main);
        }
        
        $marriage->delete();
        
        return redirect()->route('marriages.index')
            ->with('success', 'Marriage record deleted successfully.');
    }

    // ==================== ROLE-SPECIFIC ACTIONS ====================

    /**
     * Data Clerk - Process Bulk Upload
     */
    public function processBulkUpload(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is data clerk
        if ($user->role->name !== 'data_clerk') {
            abort(403, 'Unauthorized access. Only data clerks can process bulk uploads.');
        }
        
        $request->validate([
            'files.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'year' => 'required|integer',
            'month' => 'required|integer|between:1,12'
        ]);

        $assignment = ClerkManagement::where('data_clerk_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have an active assignment.'
            ], 403);
        }

        $processedFiles = [];

        foreach ($request->file('files') as $file) {
            $processedFiles[] = $this->createMarriageFromFile($file, $request->year, $request->month);
        }

        return response()->json([
            'success' => true, 
            'message' => count($processedFiles) . ' files processed successfully',
            'processed' => $processedFiles
        ]);
    }

    /**
     * Data Clerk - Quick Save Basic Info
     */
    public function quickSave(Request $request, Marriage $marriage)
    {
        $user = Auth::user();
        
        // Check if user is data clerk
        if ($user->role->name !== 'data_clerk') {
            abort(403, 'Unauthorized access. Only data clerks can use quick save.');
        }
        
        // Verify user has access to this marriage
        if ($marriage->created_by !== $user->id || $marriage->system_status !== 'Pending') {
            abort(403, 'You can only edit your own pending records.');
        }

        $request->validate([
            'certificate_serial' => 'nullable|string|max:255',
            'husband_name' => 'required|string|max:255'
        ]);

        // Convert to uppercase
        $data = [
            'certificate_serial' => strtoupper($request->certificate_serial),
            'husband_name' => strtoupper($request->husband_name),
            'updated_by' => $user->id
        ];
        
        $marriage->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Marriage record updated successfully'
        ]);
    }

    /**
     * Marriage Teller - Create Clerk Assignment
     */
    public function createClerkAssignment(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is marriage teller
        if ($user->role->name !== 'marriage_teller') {
            abort(403, 'Unauthorized access. Only marriage tellers can create clerk assignments.');
        }
        
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
            'marriage_teller_id' => $user->id,
            'year' => $request->year,
            'month' => $request->month,
            'target_count' => $request->target_count,
            'notes' => $request->notes,
            'status' => 'active'
        ]);

        return redirect()->route('marriages.show', 0)
            ->with('success', 'Clerk assignment created successfully.');
    }

    /**
     * Marriage Teller - Complete Detailed Entry
     */
    public function completeDetails(Request $request, Marriage $marriage)
    {
        $user = Auth::user();
        
        // Check if user is marriage teller
        if ($user->role->name !== 'marriage_teller') {
            abort(403, 'Unauthorized access. Only marriage tellers can complete details.');
        }
        
        if (!$this->canAccessMarriage($marriage, $user) || $marriage->system_status !== 'Pending') {
            abort(403, 'You can only complete details for pending records in your team.');
        }

        $request->validate([
            'certificate_serial' => 'required|string|max:255',
            'marriage_type_id' => 'required|exists:categories,id',
            'marriage_date' => 'required|date',
            // Add other validation rules as needed
        ]);

        $data = $request->all();
        
        // Convert all string fields to uppercase
        $data = collect($data)->map(function ($item) {
            return is_string($item) ? strtoupper($item) : $item;
        })->toArray();

        $marriage->update($data + [
            'system_status' => 'Completed',
            'updated_by' => $user->id
        ]);

        return redirect()->route('marriages.show', $marriage)
            ->with('success', 'Marriage details completed successfully.');
    }

    /**
     * Marriage Registrar - Verify Marriage
     */
    public function verify(Request $request, Marriage $marriage)
    {
        $user = Auth::user();
        
        // Check if user is marriage registrar
        if ($user->role->name !== 'marriage_registrar') {
            abort(403, 'Unauthorized access. Only marriage registrars can verify records.');
        }
        
        if ($marriage->system_status !== 'Completed') {
            abort(403, 'You can only verify completed records.');
        }

        // Get verification status category IDs
        $verificationStatuses = Category::where('type', 'verification_status')
            ->whereIn('name', ['Verified', 'Rejected'])
            ->pluck('id', 'name');

        $request->validate([
            'verification_status' => 'required|in:Verified,Rejected',
            'notes' => 'nullable|string'
        ]);

        $statusName = $request->verification_status;
        $verificationStatusId = $verificationStatuses[$statusName] ?? null;

        if (!$verificationStatusId) {
            return redirect()->back()->with('error', 'Invalid verification status.');
        }

        $marriage->update([
            'verification_status_id' => $verificationStatusId,
            'verified_by' => $user->id,
            'updated_by' => $user->id,
            'verification_notes' => $request->notes ?? null,
        ]);

        return redirect()->route('marriages.show', $marriage)
            ->with('success', 'Marriage verification status updated');
    }

    // ==================== HELPER METHODS ====================


    /**
     * Get Data Clerk specific statistics
     */
    private function getDataClerkStats($user, $baseStats)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $myUploads = Marriage::where('created_by', $user->id)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $myCompleted = Marriage::where('created_by', $user->id)
            ->where('system_status', 'Completed')
            ->count();

        $myPending = Marriage::where('created_by', $user->id)
            ->where('system_status', 'Pending')
            ->count();

        // Get assignment progress
        $assignment = ClerkManagement::where('data_clerk_id', $user->id)
            ->where('status', 'active')
            ->first();

        $progress = $assignment ? 
            round(($myUploads / $assignment->target_count) * 100) . '%' : 
            '0%';

        return array_merge($baseStats, [
            'my_uploads' => $myUploads,
            'my_completed' => $myCompleted,
            'my_pending' => $myPending,
            'progress' => $progress,
        ]);
    }


    private function getMarriageTellerStats($user, $baseStats)
    {
        // Total marriages assigned to this teller's team
        $teamTotal = Marriage::whereHas('clerkManagement', function($query) use ($user) {
            $query->where('marriage_teller_id', $user->id);
        })->count();

        // Active clerks under this marriage teller
        $activeClerks = ClerkManagement::where('marriage_teller_id', $user->id)
            ->where('status', 'active')
            ->distinct('data_clerk_id')
            ->count('data_clerk_id');

        // Pending marriages for review
        $pendingReview = Marriage::whereHas('clerkManagement', function($query) use ($user) {
            $query->where('marriage_teller_id', $user->id);
        })->where('system_status', 'Pending')->count();

        // Completed marriages by team
        $teamCompleted = Marriage::whereHas('clerkManagement', function($query) use ($user) {
            $query->where('marriage_teller_id', $user->id);
        })->where('system_status', 'Completed')->count();

        // Merge with base stats and return
        return array_merge($baseStats, [
            'team_total' => $teamTotal,
            'active_clerks' => $activeClerks,
            'pending_review' => $pendingReview,
            'team_completed' => $teamCompleted
        ]);
    }


    /**
     * Get Marriage Registrar specific statistics
     */
    private function getRegistrarStats($user, $baseStats)
    {
        // Get verification status category IDs
        $verifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Verified')
            ->value('id');
        
        $rejectedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Rejected')
            ->value('id');
        
        $unverifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id');

        $verificationQueue = Marriage::where('system_status', 'Completed')
            ->where('verification_status_id', $unverifiedStatusId)
            ->count();

        $verifiedToday = Marriage::where('verification_status_id', $verifiedStatusId)
            ->whereDate('updated_at', today())
            ->count();

        $totalVerified = Marriage::where('verification_status_id', $verifiedStatusId)->count();

        $totalProcessed = Marriage::where('verification_status_id', $verifiedStatusId)
            ->orWhere('verification_status_id', $rejectedStatusId)
            ->count();
        
        $rejectionRate = $totalProcessed > 0 ? 
            round(($baseStats['rejected'] / $totalProcessed) * 100) . '%' : 
            '0%';

        return array_merge($baseStats, [
            'verification_queue' => $verificationQueue,
            'verified_today' => $verifiedToday,
            'total_verified' => $totalVerified,
            'rejection_rate' => $rejectionRate
        ]);
    }

    /**
     * Show role-specific dashboard when id = 0
     */
    private function showRoleDashboard($role, $user)
    {
        return match($role) {
            'data_clerk' => $this->showDataClerkBulkUpload($user),
            'marriage_teller' => $this->showMarriageTellerClerkManagement($user),
            'marriage_registrar' => $this->showRegistrarVerificationQueue($user),
            default => redirect()->route('marriages.index')
        };
    }

    /**
     * Data Clerk - Bulk Upload Dashboard
     */
    private function showDataClerkBulkUpload($user)
    {
        $assignment = ClerkManagement::where('data_clerk_id', $user->id)
            ->where('status', 'active')
            ->first();

        $pendingMarriages = Marriage::where('created_by', $user->id)
            ->where('system_status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('marriages.data-clerk-bulk-upload', compact('assignment', 'pendingMarriages'));
    }

    /**
     * Marriage Teller - Clerk Management Dashboard
     */
    private function showMarriageTellerClerkManagement($user)
    {
        $dataClerks = User::whereHas('role', function($query) {
            $query->where('name', 'data_clerk');
        })->get();

        $assignments = ClerkManagement::where('marriage_teller_id', $user->id)
            ->with(['dataClerk', 'marriages'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('marriages.marriage-teller-clerk-management', compact('dataClerks', 'assignments'));
    }

    /**
     * Marriage Registrar - Verification Queue
     */
    private function showRegistrarVerificationQueue($user)
    {
        // Get verification status category ID for 'Unverified'
        $unverifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id');
        
        $verificationQueue = Marriage::where('system_status', 'Completed')
            ->where('verification_status_id', $unverifiedStatusId)
            ->with(['createdBy', 'spouses', 'witnesses'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get verification status category IDs for stats
        $verifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Verified')
            ->value('id');
        
        $rejectedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Rejected')
            ->value('id');
        
        $unverifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id');

        $stats = [
            'total' => Marriage::count(),
            'pending' => Marriage::where('verification_status_id', $unverifiedStatusId)->count(),
            'verified' => Marriage::where('verification_status_id', $verifiedStatusId)->count(),
            'rejected' => Marriage::where('verification_status_id', $rejectedStatusId)->count(),
        ];

        return view('marriages.registrar-verification-queue', compact('verificationQueue', 'stats'));
    }

    /**
     * Show marriage record based on user role
     */
    private function showMarriageForRole($marriage, $categories, $role, $user)
    {
        return match($role) {
            'data_clerk' => $this->showDataClerkMarriage($marriage, $categories, $user),
            'marriage_teller' => $this->showMarriageTellerMarriage($marriage, $categories, $user),
            'marriage_registrar' => $this->showRegistrarMarriage($marriage, $categories, $user),
            default => view('marriages.show', compact('marriage', 'categories'))
        };
    }

    /**
     * Data Clerk - Quick Entry View
     */
    private function showDataClerkMarriage($marriage, $categories, $user)
    {
        // Data clerks can only edit their own pending records
        if ($marriage->created_by == $user->id && $marriage->system_status == 'Pending') {
            return view('marriages.data-clerk-quick-entry', compact('marriage', 'categories'));
        }
        
        // Read-only view for other records
        return view('marriages.show', compact('marriage', 'categories'));
    }

    /**
     * Marriage Teller - Detail Entry View
     */
    private function showMarriageTellerMarriage($marriage, $categories, $user)
    {
        // Marriage tellers can complete details for pending records in their team
        if ($marriage->system_status == 'Pending' && $this->canAccessMarriage($marriage, $user)) {
            return view('marriages.marriage-teller-detail-entry', compact('marriage', 'categories'));
        }
        
        // Read-only view for completed records
        return view('marriages.show', compact('marriage', 'categories'));
    }

    /**
     * Marriage Registrar - Verification View
     */
    private function showRegistrarMarriage($marriage, $categories, $user)
    {
        // Get verification status category ID for 'Unverified'
        $unverifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id');
        
        // Registrars can verify completed but unverified records
        if ($marriage->system_status == 'Completed' && $marriage->verification_status_id == $unverifiedStatusId) {
            return view('marriages.registrar-verification', compact('marriage', 'categories'));
        }
        
        // Read-only view for other records
        return view('marriages.show', compact('marriage', 'categories'));
    }

    // ==================== PERMISSION CHECKS ====================


    private function canAccessMarriage(Marriage $marriage, User $user)
    {
        // Get role name via relationship
        $roleName = $user->role->name ?? null;

        if ($roleName === 'admin') {
            return true;
        }

        if ($roleName === 'marriage_teller') {
            return ClerkManagement::where('marriage_teller_id', $user->id)
                ->whereHas('marriages', function($query) use ($marriage) {
                    $query->where('id', $marriage->id);
                })
                ->exists();
        } 

        if ($roleName === 'marriage_registrar') {
            // Marriage registrars should have access to all marriages
            // Remove the relationship check that's causing the error
            return true;
        }

        // Default: only creator can access
        return $marriage->created_by === $user->id;
    }

    private function canEditMarriage(Marriage $marriage, User $user)
    {
        if ($user->role->name === 'admin') {
            return true;
        }
        
        if ($user->role->name === 'marriage_teller') {
            return $this->canAccessMarriage($marriage, $user) && $marriage->system_status === 'Pending';
        }

        if ($user->role->name === 'marriage_registrar') {
            // Marriage registrars can edit pending or completed marriages
            return $this->canAccessMarriage($marriage, $user) && 
                in_array($marriage->system_status, ['Pending', 'Completed']);
        }

        return $marriage->created_by === $user->id && $marriage->system_status === 'Pending';
    }

    /**
     * Check if user can delete marriage record
     */
    private function canDeleteMarriage(Marriage $marriage, User $user)
    {
        return $user->role->name === 'admin' || $marriage->created_by === $user->id;
    }

    private function createMarriageFromFile($file, $year, $month)
    {
        $optimizedPath = FileService::saveCompressedImage($file, 'marriages');
        
        $marriage = Marriage::create([
            'year' => $year,
            'month' => $month,
            'image_main' => $optimizedPath,
            'original_filename' => $file->getClientOriginalName(),
            'system_status' => 'Pending',
            'verification_status' => 'Unverified',
            'created_by' => Auth::id()
        ]);

        return [
            'id' => $marriage->id,
            'filename' => $file->getClientOriginalName(),
            'preview_url' => Storage::url($optimizedPath)
        ];
    }

    public function quickCreateFromPage(Request $request)
{
    $user = Auth::user();
    
    if ($user->role->name !== 'marriage_teller') {
        return back()->with('error', 'Only marriage tellers can create marriages from PDF pages.');
    }

    $validated = $request->validate([
        'pdf_page_id' => 'required|exists:pdf_pages,id',
        'certificate_serial' => 'required|string|max:255|unique:marriages,certificate_serial',
        'page_number' => 'nullable|integer|min:1',
    ]);

    try {
        DB::beginTransaction();

        $page = \App\Models\PdfPage::with('pdfUpload')->findOrFail($validated['pdf_page_id']);
        
        // Check if page is already linked
        if ($page->marriage) {
            throw new \Exception('This PDF page is already linked to a marriage record.');
        }

        // Get the PDF upload
        $pdf = $page->pdfUpload;

        // Get verification status category ID for 'Unverified'
        $unverifiedStatusId = Category::where('type', 'verification_status')
            ->where('name', 'Unverified')
            ->value('id');

        // Create a basic marriage record
        $marriage = Marriage::create([
            'certificate_serial' => strtoupper($validated['certificate_serial']),
            'pdf_page_id' => $page->id,
            'pdf_id' => $pdf->id,
            'verification_status_id' => $unverifiedStatusId,
            'year' => $pdf->year,
            'month' => $pdf->month,
            'county' => $pdf->county->name ?? $pdf->county_code,
            'created_by' => $user->id,
            'system_status' => 'Pending',
        ]);

        // Update page status
        $page->update([
            'status' => 'completed',
            'data_entry_by' => $user->id,
            'completed_at' => now(),
        ]);

        DB::commit();

        return redirect()->route('marriages.show', $marriage)
                        ->with('success', 'Marriage record created successfully from PDF page!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to create marriage record: ' . $e->getMessage())
                    ->withInput();
    }
}
/**
 * Helper method to calculate completion rate for a marriage
 * This should be added to your Marriage model or as a controller method
 */
private function calculateMarriageCompletionRate($marriage)
{
    $totalFields = 0;
    $completedFields = 0;

    // Basic marriage info fields (4 fields)
    $basicFields = ['certificate_serial', 'marriage_date', 'venue', 'county'];
    $totalFields += count($basicFields);
    foreach ($basicFields as $field) {
        if (!empty($marriage->$field)) {
            $completedFields++;
        }
    }

    // Spouses count (2 required)
    $totalFields += 2;
    $completedFields += min($marriage->spouses->count(), 2);

    // Witnesses count (2 required)
    $totalFields += 2;
    $completedFields += min($marriage->witnesses->count(), 2);

    // Calculate percentage
    return $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;
}

public function createFromPdfPage(PdfPage $pdfPage)
{
    $user = Auth::user();
    
    if ($user->role->name !== 'marriage_teller') {
        abort(403, 'Only marriage tellers can create marriages from PDF pages.');
    }

    // Get the PDF upload associated with this page
    $pdf = $pdfPage->pdfUpload;
    
    // Check if this page is already linked to a marriage
    $existingMarriage = $pdfPage->marriage;
    
    if ($existingMarriage) {
        return redirect()->route('marriages.show', $existingMarriage)
            ->with('info', 'This PDF page is already linked to a marriage record.');
    }
    
    // Load categories for the form
    $categories = Category::whereIn('type', ['marriage_type', 'marriage_status', 'verification_status', 'id_type'])
                        ->get();

    // Get all counties with their constituencies
    $counties = County::select('name')
        ->distinct()
        ->orderBy('name')
        ->pluck('name')
        ->toArray();

    // Get all constituencies grouped by county
    $countiesWithConstituencies = County::select('name', 'constituency')
        ->orderBy('name')
        ->orderBy('constituency')
        ->get()
        ->groupBy('name')
        ->map(function($items) {
            return $items->pluck('constituency')->unique()->values()->toArray();
        })
        ->toArray();

    // Get all constituencies for the dropdown (flat list)
    $constituencies = County::select('constituency')
        ->distinct()
        ->orderBy('constituency')
        ->pluck('constituency')
        ->toArray();

    // Get wards for residence fields
    $wards = County::select('wards')
        ->distinct()
        ->orderBy('wards')
        ->pluck('wards')
        ->toArray();

    // Prepare PDF page data
    $pdfPageData = [
        'id' => $pdfPage->id,
        'page_number' => $pdfPage->page_number,
        'status' => $pdfPage->status,
        'pdf_upload_id' => $pdf->id,
        'certificate_serial' => $pdf->certificate_serial,
        'year' => $pdf->year,
        'month' => $pdf->month,
        'county' => $pdf->county->name ?? $pdf->county_code ?? 'Nairobi',
        'county_code' => $pdf->county_code,
        'pdf_name' => $pdf->name,
        'total_pages' => $pdf->total_pages,
    ];

    return view('marriages.create-from-pdf-page', compact(
        'pdfPage', 
        'pdf', 
        'categories', 
        'counties',
        'countiesWithConstituencies',
        'constituencies',
        'wards',
        'existingMarriage',
        'pdfPageData'

    ));
}
}
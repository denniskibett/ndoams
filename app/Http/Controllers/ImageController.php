<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; 


class ImageController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        // Only get images for the authenticated user
        $query = Image::with(['uploader', 'updater'])
                    ->where('uploaded_by', Auth::id())
                    ->latest();
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('certificate_serial', 'like', "%{$search}%")
                ->orWhere('filename', 'like', "%{$search}%");
            });
        }
        
        // Year filter
        if ($request->has('year') && $request->year != '') {
            $query->where('year', $request->year);
        }
        
        // Month filter
        if ($request->has('month') && $request->month != '') {
            $query->where('month', $request->month);
        }

        $images = $query->paginate(12);
        
        // Get grouped images using service
        $groupedImages = $this->imageService->getImagesGroupedByPeriod(Auth::id());
            
        // Get available years for filter dropdown (for current user only)
        $availableYears = Image::where('uploaded_by', Auth::id())->distinct()->pluck('year')->sortDesc();
        $availableMonths = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        // Check layout
        $layout = $request->get('layout', 'grid');
        
        // Get storage statistics using service
        $stats = $this->imageService->getStorageStats(Auth::id());

        if ($layout === 'grid' || $request->ajax()) {
            return view('images.index', compact('images', 'groupedImages', 'availableYears', 'availableMonths', 'layout', 'stats'));
        }
        
        return view('images.index', compact('images', 'availableYears', 'availableMonths', 'layout', 'stats'));
    }
    
    public function create()
    {
        return view('images.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'certificate_serial' => 'nullable|string|max:255',
            'year' => 'required|integer|min:2000|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'fit_type' => 'required|in:stretch,fit,original',
            'rotation_angle' => 'required|integer',
        ]);

        try {
            if ($request->hasFile('image')) {
                // Use ImageService to handle everything
                $this->imageService->storeImage(
                    $request->file('image'),
                    $validated,
                    Auth::id()
                );

                return redirect()->route('images.index')
                    ->with('success', 'Image uploaded successfully with A4 background.');
            }

            return back()->with('error', 'No image file was uploaded.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error processing image: ' . $e->getMessage());
        }
    }

    public function show(Image $image)
    {
        return view('images.show', compact('image'));
    }

    public function edit(Image $image)
    {
        // Check if the file exists
        $fileExists = Storage::disk('public')->exists($image->image_path);
        
        if (!$fileExists) {
            Log::warning("Image file not found for editing", [
                'image_id' => $image->id,
                'image_path' => $image->image_path
            ]);
        }

        return view('images.edit', compact('image', 'fileExists'));
    }

    public function update(Request $request, Image $image)
    {
        Log::debug('Image Update Request Data:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'certificate_serial' => 'nullable|string|max:255',
            'year' => 'required|integer|min:2000|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'fit_type' => 'required|in:stretch,fit,original',
            'rotation_angle' => 'required|integer',
        ]);

        Log::debug('Update validation passed');

        try {
            // Use ImageService to handle the update
            $file = $request->hasFile('image') ? $request->file('image') : null;
            
            $this->imageService->updateImage(
                $image,
                $validated,
                $file,
                Auth::id()
            );
        
            Log::debug('Image updated successfully', [
                'image_id' => $image->id,
                'changes' => $image->getChanges()
            ]);

            return redirect()->route('images.index')
                ->with('success', 'Image updated successfully.');

        } catch (\Exception $e) {
            Log::error('Image update error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return back()->with('error', 'Error updating image: ' . $e->getMessage());
        }
    }

    public function destroy(Image $image)
    {
        try {
            // Use ImageService to handle deletion
            $this->imageService->deleteImage($image);

            return redirect()->route('images.index')
                ->with('success', 'Image deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting image: ' . $e->getMessage());
        }
    }

    /**
     * Clean up orphaned files (can be called via artisan command or manually)
     */
    public function cleanup()
    {
        try {
            $deletedFiles = $this->imageService->cleanupOrphanedFiles();
            
            $message = count($deletedFiles) > 0 
                ? 'Cleanup completed. Deleted ' . count($deletedFiles) . ' orphaned files.'
                : 'No orphaned files found.';
                
            return redirect()->route('images.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Error during cleanup: ' . $e->getMessage());
        }
    }
}
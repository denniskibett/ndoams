<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;
use Intervention\Image\ImageManager;

class ImageService
{
    private const A4_WIDTH = 595;
    private const A4_HEIGHT = 842;
    private const A4_BACKGROUND_COLOR = '#FFFFFF';
    private const MAX_FILE_SIZE_KB = 50;
    private const QUALITY = 85;

    private $imageManager;

    public function __construct()
    {
        if (!class_exists('Intervention\Image\ImageManager')) {
            throw new Exception('Intervention Image package is not installed. Please run: composer require intervention/image');
        }
        
        $this->imageManager = new ImageManager(['driver' => 'gd']);
    }

    /**
     * Store an uploaded image with A4 background in nested year/month directories
     */
    public function storeImage(UploadedFile $file, array $data, int $userId): Image
    {
        \Log::debug('ImageService: Starting image storage process', $data);
        
        $year = $data['year'] ?? now()->year;
        $month = $data['month'] ?? now()->month;
        $certificateSerial = $data['certificate_serial'] ?? null;
        $fitType = $data['fit_type'] ?? 'stretch';
        $rotationAngle = $data['rotation_angle'] ?? 0;

        // Generate filename
        $filename = $this->generateFilename($year, $month, $certificateSerial);
        
        // Use nested directory structure: images/year/month/
        $directory = "images/{$year}/" . str_pad($month, 2, '0', STR_PAD_LEFT);
        
        \Log::debug('ImageService: Processing image with A4 background', [
            'filename' => $filename,
            'directory' => $directory,
            'rotation_angle' => $rotationAngle,
            'fit_type' => $fitType
        ]);

        // Process image with A4 background and rotation
        $storagePath = $this->processImageWithA4Background($file, $directory, $filename, $rotationAngle, $fitType);

        // Get file size in KB
        $fileSizeKB = ceil(Storage::disk('public')->size($storagePath) / 1024);

        \Log::debug('ImageService: Image processed successfully', [
            'storage_path' => $storagePath,
            'file_size_kb' => $fileSizeKB
        ]);

        // Create image record
        return Image::create([
            'image_path' => $storagePath,
            'name' => $data['name'],
            'filename' => $filename,
            'certificate_serial' => $certificateSerial,
            'year' => $year,
            'month' => $month,
            'file_size' => $fileSizeKB,
            'fit_type' => $fitType,
            'uploaded_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    /**
     * Update an existing image with A4 background and rotation support
     */
    public function updateImage(Image $image, array $data, ?UploadedFile $file, int $userId): Image
    {
        \Log::debug('ImageService: Starting image update process', $data);
        
        $rotationAngle = $data['rotation_angle'] ?? 0;
        $fitType = $data['fit_type'] ?? $image->fit_type ?? 'stretch';
        
        // Store update data
        $updateData = [
            'name' => $data['name'],
            'certificate_serial' => $data['certificate_serial'],
            'year' => $data['year'],
            'month' => $data['month'],
            'fit_type' => $fitType,
            'updated_by' => $userId,
        ];

        // If a new file is uploaded or rotation changed or fit type changed, reprocess the image
        if ($file || $rotationAngle != 0 || $fitType != $image->fit_type) {
            \Log::debug('ImageService: Reprocessing image due to changes', [
                'has_new_file' => (bool)$file,
                'rotation_changed' => $rotationAngle != 0,
                'fit_type_changed' => $fitType != $image->fit_type
            ]);

            // Store old path for deletion
            $oldImagePath = $image->image_path;

            if ($file) {
                // Process new file with A4 background
                $filename = $this->generateFilename(
                    $data['year'] ?? $image->year, 
                    $data['month'] ?? $image->month, 
                    $data['certificate_serial'] ?? $image->certificate_serial
                );

                // Use nested directory structure
                $directory = "images/{$data['year']}/" . str_pad($data['month'], 2, '0', STR_PAD_LEFT);
                $storagePath = $this->processImageWithA4Background($file, $directory, $filename, $rotationAngle, $fitType);
                
                $updateData['image_path'] = $storagePath;
                $updateData['filename'] = $filename;
            } else {
                // Reprocess existing image with rotation and fit type on A4 background
                $storagePath = $this->reprocessImageWithRotation($image, $rotationAngle, $fitType);
                $updateData['image_path'] = $storagePath;
                $updateData['filename'] = $image->filename;
            }

            // Get file size in KB
            $fileSizeKB = ceil(Storage::disk('public')->size($updateData['image_path']) / 1024);
            $updateData['file_size'] = $fileSizeKB;

            // Delete old file after successful processing
            if ($oldImagePath && $oldImagePath !== $updateData['image_path'] && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
                \Log::debug('ImageService: Deleted old image file', ['path' => $oldImagePath]);
            }
        }

        \Log::debug('ImageService: Updating image record', $updateData);

        // Update the image record
        $image->update($updateData);

        return $image->fresh();
    }

    /**
     * Process image with A4 background with different fit types
     */
    private function processImageWithA4Background(UploadedFile $file, string $directory, string $filename, int $rotationAngle = 0, string $fitType = 'stretch'): string
    {
        // Create A4 canvas
        $canvas = $this->imageManager->canvas(self::A4_WIDTH, self::A4_HEIGHT, self::A4_BACKGROUND_COLOR);
        
        // Load and process the uploaded image
        $uploadedImage = $this->imageManager->make($file->getRealPath());
        
        // Apply rotation if needed
        if ($rotationAngle != 0) {
            $uploadedImage->rotate(-$rotationAngle);
        }
        
        // Resize based on fit type
        $resizedImage = $this->resizeForA4($uploadedImage, $fitType);
        
        // Calculate position to fill A4 properly
        list($x, $y) = $this->calculatePosition($resizedImage, $fitType);
        
        // Insert the image onto A4 canvas
        $canvas->insert($resizedImage, 'top-left', intval($x), intval($y));
        
        // Ensure directory exists
        Storage::disk('public')->makeDirectory($directory);
        
        // Save the final image as PNG
        $fullPath = Storage::disk('public')->path($directory . '/' . $filename);
        
        // Save with initial quality
        $canvas->save($fullPath, self::QUALITY);
        
        // Optimize file size to be under 50KB with better compression
        $this->compressToTargetSize($fullPath);
        
        // Free memory
        $uploadedImage->destroy();
        $resizedImage->destroy();
        $canvas->destroy();
        
        // Return the nested path
        return $directory . '/' . $filename;
    }

    /**
     * Resize image based on fit type - UPDATED WITH STRETCH TO TOUCH EDGES
     */
    private function resizeForA4($image, string $fitType)
    {
        $originalWidth = $image->getWidth();
        $originalHeight = $image->getHeight();
        $originalRatio = $originalWidth / $originalHeight;
        $a4Ratio = self::A4_WIDTH / self::A4_HEIGHT; // 595/842 ≈ 0.706

        switch ($fitType) {
            case 'stretch':
                // STRETCH TO TOUCH EDGES: Maintain aspect ratio but force to touch edges
                // No distortion - image will overflow in one dimension to touch all edges
                if ($originalRatio > $a4Ratio) {
                    // Landscape image - stretch height to touch top/bottom, width will overflow
                    $newHeight = self::A4_HEIGHT;
                    $newWidth = ($originalWidth / $originalHeight) * $newHeight;
                    return $image->resize($newWidth, $newHeight);
                } else {
                    // Portrait image - stretch width to touch sides, height will overflow
                    $newWidth = self::A4_WIDTH;
                    $newHeight = ($originalHeight / $originalWidth) * $newWidth;
                    return $image->resize($newWidth, $newHeight);
                }

            case 'fit':
                // FIT: Maintain aspect ratio, fit within boundaries (may have empty space)
                if ($originalRatio > $a4Ratio) {
                    // Landscape - fit to width
                    return $image->resize(self::A4_WIDTH, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                } else {
                    // Portrait - fit to height
                    return $image->resize(null, self::A4_HEIGHT, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }

            case 'original':
                // ORIGINAL: Keep original size
                return $image;

            case 'true_stretch':
                // TRUE STRETCH: Force exact A4 dimensions (will distort image)
                return $image->resize(self::A4_WIDTH, self::A4_HEIGHT);

            default:
                // Default to stretch to touch edges
                return $this->resizeForA4($image, 'stretch');
        }
    }

    /**
     * Calculate position based on fit type - UPDATED FOR STRETCH TO TOUCH EDGES
     */
    private function calculatePosition($image, string $fitType): array
    {
        $imageWidth = $image->getWidth();
        $imageHeight = $image->getHeight();

        switch ($fitType) {
            case 'stretch':
                // For STRETCH TO TOUCH EDGES: Center the dimension that overflows
                if ($imageWidth > self::A4_WIDTH) {
                    // Landscape image - center horizontally (vertical touches edges)
                    $x = (self::A4_WIDTH - $imageWidth) / 2;
                    $y = 0;
                } else {
                    // Portrait image - center vertically (horizontal touches edges)
                    $x = 0;
                    $y = (self::A4_HEIGHT - $imageHeight) / 2;
                }
                break;

            case 'true_stretch':
                // For TRUE STRETCH: Image is exactly A4 size, position at top-left
                return [0, 0];

            case 'fit':
                // For FIT: Center the image since it maintains aspect ratio
                $x = (self::A4_WIDTH - $imageWidth) / 2;
                $y = (self::A4_HEIGHT - $imageHeight) / 2;
                break;

            case 'original':
                // For ORIGINAL: Center the original image
                $x = (self::A4_WIDTH - $imageWidth) / 2;
                $y = (self::A4_HEIGHT - $imageHeight) / 2;
                break;

            default:
                $x = 0;
                $y = 0;
        }

        return [max(0, $x), max(0, $y)];
    }

    /**
     * Reprocess existing image with rotation and fit type on A4 background
     */
    private function reprocessImageWithRotation(Image $image, int $rotationAngle, string $fitType): string
    {
        // Load the current A4 image from storage
        $currentPath = Storage::disk('public')->path($image->image_path);
        
        // Create A4 canvas
        $canvas = $this->imageManager->canvas(self::A4_WIDTH, self::A4_HEIGHT, self::A4_BACKGROUND_COLOR);
        
        // Load the current image
        $currentImage = $this->imageManager->make($currentPath);
        
        // Apply rotation
        if ($rotationAngle != 0) {
            $currentImage->rotate(-$rotationAngle);
        }
        
        // Resize based on fit type
        $resizedImage = $this->resizeForA4($currentImage, $fitType);
        
        // Calculate position
        list($x, $y) = $this->calculatePosition($resizedImage, $fitType);
        
        // Insert the rotated image onto A4 canvas
        $canvas->insert($resizedImage, 'top-left', intval($x), intval($y));
        
        // Save the final image (overwrite existing)
        $canvas->save($currentPath, self::QUALITY);
        
        // Optimize file size to be under 50KB
        $this->compressToTargetSize($currentPath);
        
        // Free memory
        $currentImage->destroy();
        $resizedImage->destroy();
        $canvas->destroy();
        
        return $image->image_path;
    }

    /**
     * Advanced compression to target size (50KB) with better quality preservation
     */
    private function compressToTargetSize(string $filePath): void
    {
        $maxSizeBytes = self::MAX_FILE_SIZE_KB * 1024;
        $currentSize = filesize($filePath);
        
        if ($currentSize <= $maxSizeBytes) {
            return; // Already under target size
        }

        $image = $this->imageManager->make($filePath);
        
        // First try: Reduce quality progressively
        $quality = self::QUALITY;
        $attempts = 0;
        $maxAttempts = 10;
        
        while ($currentSize > $maxSizeBytes && $quality >= 40 && $attempts < $maxAttempts) {
            $quality -= 5;
            $image->save($filePath, $quality);
            clearstatcache(true, $filePath);
            $currentSize = filesize($filePath);
            $attempts++;
        }
        
        // If still too large, try reducing dimensions slightly
        if ($currentSize > $maxSizeBytes && $quality <= 50) {
            $width = $image->getWidth();
            $height = $image->getHeight();
            
            // Reduce dimensions by 5% and try again
            $newWidth = $width * 0.95;
            $newHeight = $height * 0.95;
            
            $image->resize($newWidth, $newHeight);
            $image->save($filePath, max(40, $quality));
            clearstatcache(true, $filePath);
            $currentSize = filesize($filePath);
        }
        
        $image->destroy();
    }

    /**
     * Clean up orphaned files (files in storage without DB records)
     */
    public function cleanupOrphanedFiles(): array
    {
        $deletedFiles = [];
        
        // Get all image files from storage (including nested directories)
        $allFiles = Storage::disk('public')->allFiles('images');
        
        foreach ($allFiles as $file) {
            // Check if this file exists in the database
            $exists = Image::where('image_path', $file)->exists();
            
            if (!$exists) {
                // File exists in storage but not in DB - delete it
                Storage::disk('public')->delete($file);
                $deletedFiles[] = $file;
                \Log::debug('ImageService: Deleted orphaned file', ['path' => $file]);
            }
        }
        
        \Log::info('ImageService: Cleanup completed', [
            'total_files_checked' => count($allFiles),
            'orphaned_files_deleted' => count($deletedFiles)
        ]);
        
        return $deletedFiles;
    }

    /**
     * Delete an image and its file
     */
    public function deleteImage(Image $image): bool
    {
        // Delete physical file if it exists
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
            \Log::debug('ImageService: Deleted image file', ['path' => $image->image_path]);
        }
        
        // Delete database record
        return $image->delete();
    }

    /**
     * Generate filename with format: year_month_certificate_serial.png
     */
    private function generateFilename(int $year, int $month, ?string $certificateSerial): string
    {
        $baseName = $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT);
        
        if ($certificateSerial) {
            $baseName .= '_' . Str::slug($certificateSerial);
        } else {
            // Add random string if no certificate serial to avoid conflicts
            $baseName .= '_' . Str::random(6);
        }
        
        // Always use PNG for A4 images
        return $baseName . '.png';
    }

    /**
     * Get available fit types with descriptions
     */
    public function getFitTypes(): array
    {
        return [
            'stretch' => [
                'name' => 'Stretch to Touch Edges',
                'description' => 'Maintains aspect ratio, touches all edges (recommended for certificates)',
                'behavior' => 'No distortion, one dimension may overflow to touch edges'
            ],
            'true_stretch' => [
                'name' => 'True Stretch',
                'description' => 'Forces exact A4 dimensions (may distort image)',
                'behavior' => 'Distorts image to perfectly fill A4 canvas'
            ],
            'fit' => [
                'name' => 'Fit Within',
                'description' => 'Fits within A4 boundaries, maintains aspect ratio',
                'behavior' => 'May have empty space around edges'
            ],
            'original' => [
                'name' => 'Original Size',
                'description' => 'Keeps original image dimensions',
                'behavior' => 'Centered on A4 canvas, may be smaller or larger than A4'
            ]
        ];
    }

    /**
     * Get images grouped by year and month for current user
     */
    public function getImagesGroupedByPeriod(int $userId)
    {
        return Image::where('uploaded_by', $userId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(['year', function ($item) {
                return $item->month;
            }]);
    }

    /**
     * Get all images for current user (for row layout)
     */
    public function getImagesForUser(int $userId)
    {
        return Image::where('uploaded_by', $userId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(int $userId): array
    {
        $totalImages = Image::where('uploaded_by', $userId)->count();
        $storageUsed = Image::where('uploaded_by', $userId)->sum('file_size');
        $thisMonth = Image::where('uploaded_by', $userId)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $recentImages = Image::where('uploaded_by', $userId)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            'total_images' => $totalImages,
            'storage_used' => $storageUsed, // in KB
            'this_month' => $thisMonth,
            'recent_images' => $recentImages,
        ];
    }
}
@extends('layouts.app')

@section('content')
<main>
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'Upload New PDF' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    <div class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Upload New PDF</h1>
                
                <!-- Success Message -->
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-green-800">Success!</h3>
                            <p class="text-sm text-green-700 mt-1">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Error Message -->
                @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-red-800">Upload Failed</h3>
                            <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
                @endif
                
                <form action="{{ route('pdf-uploads.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Year -->
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year *</label>
                            <input type="number" name="year" id="year" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                min="2000" max="{{ date('Y') }}" value="{{ old('year', date('Y')) }}">
                            @error('year')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Month -->
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month *</label>
                            <select name="month" id="month" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Month</option>
                                @foreach($months as $value => $label)
                                    <option value="{{ $value }}" {{ old('month') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('month')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- County Code -->
                        <div class="md:col-span-2">
                            <label for="county_code" class="block text-sm font-medium text-gray-700 mb-2">County *</label>
                            <select id="county_code" name="county_code" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Select County</option>
                                @foreach($counties as $county)
                                <option value="{{ $county->county_code }}" {{ old('county_code') == $county->county_code ? 'selected' : '' }}>
                                    {{ $county->name }} ({{ $county->county_code }})
                                </option>
                                @endforeach
                            </select>
                            @error('county_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- PDF File Upload -->
                        <div class="md:col-span-2">
                            <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">PDF File *</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors duration-200"
                                 id="drop-area">
                                <input type="file" name="pdf_file" id="pdf_file" required 
                                    accept=".pdf" 
                                    class="hidden"
                                    onchange="handleFileSelect(this)">
                                <label for="pdf_file" class="cursor-pointer block">
                                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-lg text-gray-700 mb-2">Click to upload or drag & drop PDF</p>
                                    <p class="text-sm text-gray-500 mb-4">Maximum file size: 100MB</p>
                                    <p id="file-name" class="text-sm text-blue-600 font-medium">No file chosen</p>
                                    <p id="file-size" class="text-xs text-gray-500 mt-1"></p>
                                </label>
                            </div>
                            @error('pdf_file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            
                            <!-- Upload Progress (hidden by default) -->
                            <div id="upload-progress" class="mt-4 hidden">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Uploading...</span>
                                    <span id="progress-percentage">0%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <p id="progress-text" class="text-xs text-gray-500 mt-2"></p>
                            </div>
                        </div>
                        
                        <!-- Upload Information -->
                        <div class="md:col-span-2 mt-2">
                            <div class="text-sm text-gray-600">
                                <p><i class="fas fa-info-circle mr-2"></i> 
                                    After upload, the system will:
                                </p>
                                <ul class="mt-2 space-y-1 pl-6">
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                        <span>Create one PDF record with locked year, month, and county</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                        <span>Create individual page records for data entry (1 record per page)</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                        <span>Generate preview images for each page</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                        <span>Make pages available for marriage data entry</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-4">
                        <a href="{{ route('pdf-uploads.index') }}" 
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                        <button type="submit" id="submit-btn"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-upload mr-2"></i> 
                            <span id="submit-text">Upload PDF</span>
                            <span id="loading-spinner" class="hidden ml-2">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- File validation info -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-sm font-medium text-blue-800 mb-2">Important Notes:</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li class="flex items-start">
                        <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                        <span><strong>Year, Month, and County</strong> will be locked for all pages in this PDF and cannot be changed later.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                        <span>Each page in the PDF will become a separate record for marriage data entry.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                        <span>Large files may take a few moments to process and create page records.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-info-circle mr-2 mt-0.5"></i>
                        <span>Only PDF files are allowed. Maximum file size: 100MB.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>    

<script>
// File selection handling
function handleFileSelect(input) {
    const fileNameDisplay = document.getElementById('file-name');
    const fileSizeDisplay = document.getElementById('file-size');
    
    if (input.files.length > 0) {
        const file = input.files[0];
        fileNameDisplay.textContent = file.name;
        fileNameDisplay.classList.add('font-medium');
        
        // Display file size
        const fileSize = formatFileSize(file.size);
        fileSizeDisplay.textContent = `Size: ${fileSize}`;
        
        // Validate file size
        const maxSize = 100 * 1024 * 1024; // 100MB
        if (file.size > maxSize) {
            alert('File size exceeds 100MB limit. Please select a smaller file.');
            input.value = '';
            fileNameDisplay.textContent = 'No file chosen';
            fileNameDisplay.classList.remove('font-medium');
            fileSizeDisplay.textContent = '';
        }
    } else {
        fileNameDisplay.textContent = 'No file chosen';
        fileNameDisplay.classList.remove('font-medium');
        fileSizeDisplay.textContent = '';
    }
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag and drop functionality
const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('pdf_file');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, unhighlight, false);
});

function highlight() {
    dropArea.classList.add('border-blue-400', 'bg-blue-50');
}

function unhighlight() {
    dropArea.classList.remove('border-blue-400', 'bg-blue-50');
}

dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    fileInput.files = files;
    handleFileSelect(fileInput);
}

// Form submission handling
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('pdf_file');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');
    const loadingSpinner = document.getElementById('loading-spinner');
    const progressSection = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressPercentage = document.getElementById('progress-percentage');
    const progressText = document.getElementById('progress-text');
    
    if (fileInput.files.length === 0) {
        e.preventDefault();
        alert('Please select a PDF file to upload.');
        return;
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitText.textContent = 'Processing...';
    loadingSpinner.classList.remove('hidden');
    
    // Show progress bar for large files
    const file = fileInput.files[0];
    if (file.size > 10 * 1024 * 1024) { // Show for files > 10MB
        progressSection.classList.remove('hidden');
        progressText.textContent = `Processing ${formatFileSize(file.size)} file...`;
        
        // Simulate progress (in real app, you'd use XMLHttpRequest with progress events)
        let progress = 0;
        const interval = setInterval(() => {
            progress += 5;
            if (progress <= 95) {
                progressBar.style.width = progress + '%';
                progressPercentage.textContent = progress + '%';
            }
        }, 200);
        
        // Clear interval when form submits
        setTimeout(() => clearInterval(interval), 4000);
    }
});

// Form validation on page load
document.addEventListener('DOMContentLoaded', function() {
    // Pre-fill month if there's an error and old value exists
    const monthSelect = document.getElementById('month');
    const countySelect = document.getElementById('county_code');
    
    // Add search functionality to county select (optional)
    new TomSelect(countySelect, {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        },
        placeholder: 'Select County',
        maxOptions: null
    });
});
</script>

<style>
/* Drop area hover effects */
#drop-area {
    transition: all 0.3s ease;
}

#drop-area:hover {
    border-color: #3b82f6;
    background-color: #f8fafc;
}

/* Progress bar animation */
#progress-bar {
    transition: width 0.3s ease;
}

/* Disabled button styling */
button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

@if(config('app.env') === 'local')
<!-- Include TomSelect for better dropdowns (optional) -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
@endif
@endsection
@extends('layouts.app')

@section('content')
<main>
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'Edit PDF Upload' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    <div class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit PDF Upload</h1>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- PDF Preview (3/4 width) -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current PDF Preview</label>
                    <iframe src="{{ route('pdf-uploads.preview', $pdfUpload) }}" 
                            class="w-full h-[80vh] border rounded-lg"></iframe>
                </div>

                <!-- Form Fields (1/4 width) -->
                <div class="lg:col-span-1">
                    <form action="{{ route('pdf-uploads.update', $pdfUpload) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <!-- Year -->
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year *</label>
                            <input type="number" name="year" id="year" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                min="2000" max="{{ date('Y') }}" value="{{ old('year', $pdfUpload->year) }}">
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
                                    <option value="{{ $value }}" {{ old('month', $pdfUpload->month) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('month')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- County -->
                        <div>
                            <label for="county_code" class="block text-sm font-medium text-gray-700 mb-2">County *</label>
                            <select id="county_code" name="county_code" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <option value="">Select County</option>
                                @foreach($counties as $county)
                                <option value="{{ $county->county_code }}" 
                                    {{ old('county_code', $pdfUpload->county_code) == $county->county_code ? 'selected' : '' }}>
                                    {{ $county->name }} ({{ $county->county_code }})
                                </option>
                                @endforeach
                            </select>
                            @error('county_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" id="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" {{ $pdfUpload->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $pdfUpload->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="processing" {{ $pdfUpload->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="archived" {{ $pdfUpload->status == 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                        </div>

                        <!-- PDF Upload -->
                        <div>
                            <label for="pdf_file" class="block text-sm font-medium text-gray-700 mb-2">Replace PDF (optional)</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" class="hidden" onchange="updateFileName(this)">
                                <label for="pdf_file" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-sm text-gray-500 mb-2">Click to upload new PDF</p>
                                    <p id="file-name" class="text-sm text-blue-600">{{ $pdfUpload->name ?? 'No file chosen' }}</p>
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-2 mt-4">
                            <a href="{{ route('pdf-uploads.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-save mr-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
    </div>    

<script>
function updateFileName(input) {
    const fileNameDisplay = document.getElementById('file-name');
    if (input.files.length > 0) {
        fileNameDisplay.textContent = input.files[0].name;
        fileNameDisplay.classList.add('font-medium');
    } else {
        fileNameDisplay.textContent = '{{ $pdfUpload->name }}';
        fileNameDisplay.classList.remove('font-medium');
    }
}
</script>
@endsection

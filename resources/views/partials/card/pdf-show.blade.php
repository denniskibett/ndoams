<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - PDF Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- PDF Preview Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">PDF Preview</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('pdf-uploads.download', $pdfUpload) }}" 
                       class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </a>
                    <a href="{{ route('pdf-uploads.preview', $pdfUpload) }}" target="_blank"
                       class="inline-flex items-center px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Preview
                    </a>
                </div>
            </div>
            
            <!-- PDF Thumbnail -->
            {{-- <div class="mb-4">
                <img src="{{ route('pdf-uploads.thumbnail', $pdfUpload) }}" 
                     alt="PDF Thumbnail"
                     class="w-full h-64 object-contain bg-gray-100 dark:bg-gray-700 rounded-lg">
            </div> --}}
            
            <div class="border-t dark:border-gray-700 pt-4">
                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">File Information</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">File Name</p>
                        <p class="font-medium">{{ $pdfUpload->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">File Size</p>
                        <p class="font-medium">{{ ($pdfUpload->file_size) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pages</p>
                        <p class="font-medium">{{ $pdfUpload->total_pages }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Upload Date</p>
                        <p class="font-medium">{{ $pdfUpload->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completion Progress Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Document Completion</h3>
            
            @php
                $completionFields = [
                    'Basic Information' => [
                        'year' => !empty($pdfUpload->year),
                        'month' => !empty($pdfUpload->month),
                        'county' => !empty($pdfUpload->county_code),
                    ],
                    'File Details' => [
                        'file_name' => !empty($pdfUpload->name),
                        'file_size' => !empty($pdfUpload->file_size),
                        'pages' => !empty($pdfUpload->total_pages),
                    ],
                    'Metadata' => [
                        'status' => !empty($pdfUpload->status),
                        'uploader' => !empty($pdfUpload->uploaded_by),
                    ]
                ];
            @endphp

            @foreach($completionFields as $category => $fields)
                <div class="mb-6 last:mb-0">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-medium text-gray-700 dark:text-gray-300">{{ $category }}</h4>
                        @php
                            $completed = count(array_filter($fields));
                            $total = count($fields);
                            $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp
                        <span class="text-sm font-medium {{ $percentage == 100 ? 'text-green-600' : 'text-blue-600' }}">
                            {{ $percentage }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-3">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($fields as $field => $isCompleted)
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full mr-2 {{ $isCompleted ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                                <span class="text-sm {{ $isCompleted ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500 dark:text-gray-500' }}">
                                    {{ ucfirst(str_replace('_', ' ', $field)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Right Column - Statistics & Actions -->
    <div class="space-y-6">
        <!-- Statistics Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Statistics</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Uploaded By</p>
                    <p class="font-medium">{{ $pdfUpload->uploader->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">County</p>
                    <p class="font-medium">{{ $pdfUpload->county->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Period</p>
                    <p class="font-medium">{{ $pdfUpload->month }}, {{ $pdfUpload->year }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $pdfUpload->status == 'active' ? 'bg-green-100 text-green-800 dark:bg-green-800/30 dark:text-green-400' : 
                           ($pdfUpload->status == 'processing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/30 dark:text-yellow-400' : 
                           'bg-red-100 text-red-800 dark:bg-red-800/30 dark:text-red-400') }}">
                        {{ ucfirst($pdfUpload->status) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Last Updated</p>
                    <p class="font-medium">{{ $pdfUpload->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-3">
                @if($pdfUpload->marriages_count > 0)
                    <a href="{{ route('marriages.index', ['pdf_id' => $pdfUpload->id]) }}"
                       class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium text-blue-600 dark:text-blue-400">
                                View {{ $pdfUpload->marriages_count }} Linked Marriages
                            </span>
                        </div>
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('marriages.create', ['pdf_id' => $pdfUpload->id]) }}"
                       class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/30 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/50">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="font-medium text-green-600 dark:text-green-400">
                                Link to Marriage Record
                            </span>
                        </div>
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endif

                <a href="{{ route('pdf-uploads.edit', $pdfUpload) }}"
                   class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Edit Details</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Storage Analysis Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Storage Analysis</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">This File</span>
                    <span class="font-medium">{{ ($pdfUpload->file_size) }}</span>
                </div>
               
               
            </div>
        </div>
    </div>
</div>
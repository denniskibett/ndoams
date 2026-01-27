<div
    x-data="{
        search: '',
        filterYear: '',
        filterMonth: '',
        filterCounty: '',
        filterStatus: '',
        filterMarriageType: '',
        perPage: '10',
        sortColumn: 'id',
        sortDirection: 'desc',
        currentPage: 1,
        totalPages: 1,
        totalItems: {{ $pdfPages->count() }},
        items: {{ Js::from($pdfPages) }},
        allItems: {{ Js::from($pdfPages) }},
        paginatedItems: [],
        filteredItems: [],
        
        marriageTypes: {{ Js::from($marriageTypes ?? []) }},
        
        showRoute: '{{ route('pdf-uploads.show', ':id') }}',
        editRoute: '{{ route('pdf-uploads.edit', ':id') }}',
        destroyRoute: '{{ route('pdf-uploads.destroy', ':id') }}',
        storeRoute: '{{ route('pdf-uploads.store') }}',
        updateRoute: '{{ route('pdf-uploads.update', ':id') }}',
        
        monthNames: {
            '1': 'January', '2': 'February', '3': 'March', '4': 'April',
            '5': 'May', '6': 'June', '7': 'July', '8': 'August',
            '9': 'September', '10': 'October', '11': 'November', '12': 'December'
        },
        
        monthNumbers: {
            'January': '1', 'February': '2', 'March': '3', 'April': '4',
            'May': '5', 'June': '6', 'July': '7', 'August': '8',
            'September': '9', 'October': '10', 'November': '11', 'December': '12'
        },
        
        isCreateModalOpen: false,
        isEditModalOpen: false,
        isDeleteModalOpen: false,
        selectedItem: null,
        
        modalError: null,
        isLoading: false,
        
        formData: {
            pdf_upload_id: '',
            page_id: '',
            year: '{{ date('Y') }}',
            month: '',
            county_code: '',
            marriage_type_id: '',
            pdf_file: null,
            pdf_upload_status: 'uploaded',
            page_status: '',
            name: ''
        },
        
        fileName: '',
        fileSize: '',
        
        isUploading: false,
        uploadProgress: 0,
        uploadStatus: '',
        chunkSize: 1.5 * 1024 * 1024,
        
        init() {
            console.log('PDF DataTable initialized with', this.allItems.length, 'items');
            this.applyFilters();
            this.initFileDrop();
        },
        
        initFileDrop() {
            const dropArea = document.getElementById('create-drop-area');
            if (!dropArea) return;
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, this.preventDefaults, false);
            });
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => {
                    dropArea.classList.add('border-blue-400', 'bg-blue-50');
                }, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, () => {
                    dropArea.classList.remove('border-blue-400', 'bg-blue-50');
                }, false);
            });
            
            dropArea.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                this.handleFileSelect(files[0]);
            }, false);
        },
        
        preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        },
        
        handleFileSelect(file) {
            if (!file) return;
            
            if (file.type !== 'application/pdf') {
                this.showModalError('Only PDF files are allowed.');
                return;
            }
            
            const maxSize = 100 * 1024 * 1024;
            if (file.size > maxSize) {
                this.showModalError('File size exceeds 100MB limit.');
                return;
            }
            
            this.formData.pdf_file = file;
            this.fileName = file.name;
            this.fileSize = this.formatFileSize(file.size);
        },
        
        clearFile() {
            this.formData.pdf_file = null;
            this.fileName = '';
            this.fileSize = '';
            const fileInput = document.getElementById('create-pdf-file');
            if (fileInput) fileInput.value = '';
        },
        
        showModalError(message) {
            this.modalError = message;
            setTimeout(() => {
                this.modalError = null;
            }, 5000);
        },
        
        clearModalError() {
            this.modalError = null;
        },
        
        openCreateModal() {
            this.isCreateModalOpen = true;
            this.resetForm();
            this.clearModalError();
            this.isUploading = false;
            this.uploadProgress = 0;
            this.uploadStatus = '';
        },
        
        openEditModal(item) {
            this.selectedItem = item;
            this.isEditModalOpen = true;
            this.clearModalError();
            
            if (item && item.pdf_upload) {
                const monthNumber = item.pdf_upload.month?.toString();
                const monthName = this.monthNames[monthNumber] || '';
                
                this.formData = {
                    pdf_upload_id: item.pdf_upload.id,
                    page_id: item.id,
                    year: item.pdf_upload.year || '{{ date('Y') }}',
                    month: monthName,
                    county_code: item.pdf_upload.county_code || '',
                    marriage_type_id: item.pdf_upload.marriage_type_id || '',    
                    pdf_file: null,
                    pdf_upload_status: item.pdf_upload.status || 'uploaded',
                    page_status: item.status || '',
                    name: item.pdf_upload.name || ''
                };
            }
        },
        
        openDeleteModal(item) {
            this.selectedItem = item;
            this.isDeleteModalOpen = true;
            this.clearModalError();
        },
        
        resetForm() {
            this.formData = {
                pdf_upload_id: '',
                page_id: '',
                year: '{{ date('Y') }}',
                month: '',
                county_code: '',
                marriage_type_id: '',
                pdf_file: null,
                pdf_upload_status: 'uploaded',
                page_status: '',
                name: ''
            };
            this.fileName = '';
            this.fileSize = '';
        },
        
        getMonthNumber(monthName) {
            return this.monthNumbers[monthName] || monthName;
        },
        
        async uploadFile(file) {
            this.isUploading = true;
            this.uploadProgress = 0;
            this.uploadStatus = 'Preparing upload...';
            
            try {
                const fileSizeMB = file.size / (1024 * 1024);
                
                if (fileSizeMB <= 2) {
                    this.uploadStatus = 'Uploading small file...';
                    await this.uploadRegularFile(file);
                } else {
                    this.uploadStatus = 'Processing large file...';
                    await this.uploadLargeFileWithBase64(file);
                }
                
            } catch (error) {
                console.error('Upload error:', error);
                this.showModalError('Upload failed: ' + error.message);
                this.isUploading = false;
                this.isLoading = false;
            }
        },
        
        async uploadLargeFileWithBase64(file) {
            const totalChunks = Math.ceil(file.size / this.chunkSize);
            const uploadId = 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            this.uploadStatus = 'Processing 0/' + totalChunks + ' chunks...';
            
            for (let i = 0; i < totalChunks; i++) {
                const start = i * this.chunkSize;
                const end = Math.min(start + this.chunkSize, file.size);
                const chunk = file.slice(start, end);
                
                this.uploadProgress = Math.round(((i + 1) / totalChunks) * 100);
                this.uploadStatus = 'Processing ' + (i + 1) + '/' + totalChunks + ' chunks...';
                
                const base64Chunk = await this.readChunkAsBase64(chunk);
                
                try {
                    const response = await fetch('{{ route("pdf-uploads.upload-base64-chunk") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            upload_id: uploadId,
                            chunk_index: i,
                            total_chunks: totalChunks,
                            chunk_data: base64Chunk,
                            original_name: file.name,
                            is_last_chunk: (i === totalChunks - 1),
                            year: this.formData.year,
                            month: this.getMonthNumber(this.formData.month),
                            county_code: this.formData.county_code,
                            marriage_type_id: this.formData.marriage_type_id || null
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (!response.ok || !result.success) {
                        throw new Error('Chunk ' + (i + 1) + ' upload failed: ' + (result.message || 'Unknown error'));
                    }
                    
                    if (i === totalChunks - 1 && result.success) {
                        this.uploadStatus = 'Upload complete!';
                        this.uploadProgress = 100;
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                    
                } catch (error) {
                    console.error('Chunk upload error:', error);
                    throw new Error('Chunk ' + (i + 1) + ' upload failed: ' + error.message);
                }
            }
        },
        
        readChunkAsBase64(chunk) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => {
                    const base64 = reader.result.split(',')[1];
                    resolve(base64);
                };
                reader.onerror = reject;
                reader.readAsDataURL(chunk);
            });
        },
        
        async uploadRegularFile(file) {
            const monthNumber = this.getMonthNumber(this.formData.month);
            
            const formData = new FormData();
            formData.append('year', this.formData.year);
            formData.append('month', monthNumber);
            formData.append('county_code', this.formData.county_code);
            if (this.formData.marriage_type_id) {
                formData.append('marriage_type_id', this.formData.marriage_type_id);
            }
            formData.append('pdf_file', file);
            formData.append('_token', '{{ csrf_token() }}');
            
            try {
                const response = await fetch(this.storeRoute, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (result.success) {
                        window.location.reload();
                    } else {
                        this.showModalError(result.message || 'Upload failed');
                    }
                } else {
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0];
                        this.showModalError(firstError);
                    } else if (result.message) {
                        this.showModalError(result.message);
                    } else {
                        this.showModalError('Upload failed with status: ' + response.status);
                    }
                }
            } catch (error) {
                console.error('Upload error:', error);
                this.showModalError('Upload failed: ' + error.message);
            }
        },
        
        async submitCreateForm() {
            this.isLoading = true;
            this.clearModalError();
            
            if (!this.formData.year || !this.formData.month || !this.formData.county_code) {
                this.showModalError('Please fill in all required fields.');
                this.isLoading = false;
                return;
            }
            
            if (!this.formData.pdf_file) {
                this.showModalError('Please select a PDF file.');
                this.isLoading = false;
                return;
            }
            
            const file = this.formData.pdf_file;
            await this.uploadFile(file);
            
            this.isLoading = false;
        },
        
        async submitEditForm() {
            if (!this.selectedItem || !this.selectedItem.pdf_upload) return;
            
            this.isLoading = true;
            this.clearModalError();
            
            const monthNumber = this.getMonthNumber(this.formData.month);
            
            if (!this.formData.year || !this.formData.month || !this.formData.county_code || !this.formData.pdf_upload_status) {
                this.showModalError('Year, Month, County, and PDF Status are required fields.');
                this.isLoading = false;
                return;
            }
            
            const formData = new FormData();
            formData.append('year', this.formData.year);
            formData.append('month', monthNumber);
            formData.append('county_code', this.formData.county_code);
            formData.append('pdf_upload_status', this.formData.pdf_upload_status);
            
            if (this.formData.page_status) {
                formData.append('page_status', this.formData.page_status);
                formData.append('current_page_id', this.formData.page_id);
            }
            
            if (this.formData.marriage_type_id) {
                formData.append('marriage_type_id', this.formData.marriage_type_id);
            }
            
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');
            
            if (this.formData.pdf_file) {
                formData.append('pdf_file', this.formData.pdf_file);
            }
            
            try {
                const route = this.updateRoute.replace(':id', this.formData.pdf_upload_id);
                const response = await fetch(route, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (result.success) {
                        if (this.selectedItem) {
                            if (this.selectedItem.pdf_upload) {
                                this.selectedItem.pdf_upload.status = this.formData.pdf_upload_status;
                                this.selectedItem.pdf_upload.year = this.formData.year;
                                this.selectedItem.pdf_upload.month = monthNumber;
                                this.selectedItem.pdf_upload.county_code = this.formData.county_code;
                                this.selectedItem.pdf_upload.marriage_type_id = this.formData.marriage_type_id;
                            }
                            
                            this.selectedItem.status = this.formData.page_status || this.selectedItem.status;
                            this.applyFilters();
                        }
                        
                        this.isEditModalOpen = false;
                    } else {
                        this.showModalError(result.message || 'Update failed');
                    }
                } else {
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0];
                        this.showModalError(firstError);
                    } else if (result.message) {
                        this.showModalError(result.message);
                    } else {
                        this.showModalError('Update failed with status: ' + response.status);
                    }
                }
            } catch (error) {
                console.error('Update error:', error);
                this.showModalError('Update failed: ' + error.message);
            } finally {
                this.isLoading = false;
            }
        },
        
        async submitDelete() {
            if (!this.selectedItem || !this.selectedItem.pdf_upload) return;
            
            this.isLoading = true;
            
            const itemId = this.selectedItem.pdf_upload.id;
            const route = this.destroyRoute.replace(':id', itemId);
            
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'DELETE');
                
                const response = await fetch(route, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (result.success) {
                        this.allItems = this.allItems.filter(item => item.pdf_upload?.id !== itemId);
                        this.applyFilters();
                        this.isDeleteModalOpen = false;
                    } else {
                        this.showModalError(result.message || 'Delete failed');
                    }
                } else {
                    this.showModalError(result.message || 'Delete failed with status: ' + response.status);
                }
            } catch (error) {
                console.error('Delete error:', error);
                this.showModalError('Delete failed: ' + error.message);
            } finally {
                this.isLoading = false;
            }
        },
        
        get hasActiveFilters() {
            return !!(
                this.search || 
                this.filterYear || 
                this.filterMonth || 
                this.filterCounty || 
                this.filterStatus ||
                this.filterMarriageType ||
                this.perPage !== '10' ||
                this.sortColumn !== 'id' || 
                this.sortDirection !== 'desc'
            );
        },
        
        applyFilters() {
            this.filteredItems = this.allItems.filter(item => {
                let matches = true;
                
                if (this.search) {
                    const searchTerm = this.search.toLowerCase();
                    const filename = (item.pdf_upload?.name || '').toLowerCase();
                    const uploaderName = (item.pdf_upload?.uploader?.name || '').toLowerCase();
                    const countyName = (item.pdf_upload?.county?.name || '').toLowerCase();
                    const marriageTypeName = (item.pdf_upload?.marriage_type?.name || '').toLowerCase();
                    const pageStatus = (item.status || '').toLowerCase();
                    const pdfUploadStatus = (item.pdf_upload?.status || '').toLowerCase();
                    const year = (item.pdf_upload?.year?.toString() || '').toLowerCase();
                    const month = this.getMonthName(item.pdf_upload?.month)?.toLowerCase() || '';
                    const pageNumber = (item.page_number?.toString() || '').toLowerCase();
                    const totalPages = (item.pdf_upload?.total_pages?.toString() || '').toLowerCase();
                    const fileSize = this.formatFileSize(item.pdf_upload?.file_size).toLowerCase();
                    
                    matches = matches && (
                        filename.includes(searchTerm) || 
                        uploaderName.includes(searchTerm) ||
                        countyName.includes(searchTerm) ||
                        marriageTypeName.includes(searchTerm) ||
                        pageStatus.includes(searchTerm) ||
                        pdfUploadStatus.includes(searchTerm) ||
                        year.includes(searchTerm) ||
                        month.includes(searchTerm) ||
                        pageNumber.includes(searchTerm) ||
                        totalPages.includes(searchTerm) ||
                        fileSize.includes(searchTerm)
                    );
                }
                
                if (this.filterYear) {
                    matches = matches && (item.pdf_upload?.year == this.filterYear);
                }
                
                if (this.filterMonth) {
                    matches = matches && (item.pdf_upload?.month == this.filterMonth);
                }
                
                if (this.filterCounty) {
                    matches = matches && (item.pdf_upload?.county_code == this.filterCounty);
                }
                
                if (this.filterStatus) {
                    matches = matches && (
                        item.status == this.filterStatus || 
                        item.pdf_upload?.status == this.filterStatus
                    );
                }
                
                if (this.filterMarriageType) {
                    matches = matches && (item.pdf_upload?.marriage_type_id == this.filterMarriageType);
                }
                
                return matches;
            });
            
            this.applySorting();
            this.updatePagination();
        },
        
        applySorting() {
            this.filteredItems.sort((a, b) => {
                let aValue, bValue;
                
                switch(this.sortColumn) {
                    case 'id':
                        aValue = a.id;
                        bValue = b.id;
                        break;
                    case 'filename':
                        aValue = (a.pdf_upload?.name || '').toLowerCase();
                        bValue = (b.pdf_upload?.name || '').toLowerCase();
                        break;
                    case 'year':
                        aValue = a.pdf_upload?.year || 0;
                        bValue = b.pdf_upload?.year || 0;
                        break;
                    case 'month':
                        aValue = a.pdf_upload?.month || 0;
                        bValue = b.pdf_upload?.month || 0;
                        break;
                    case 'county_code':
                        aValue = (a.pdf_upload?.county?.name || '').toLowerCase();
                        bValue = (b.pdf_upload?.county?.name || '').toLowerCase();
                        break;
                    case 'file_size':
                        aValue = this.calculatePerPageSize(a.pdf_upload?.file_size, a.pdf_upload?.total_pages) || 0;
                        bValue = this.calculatePerPageSize(b.pdf_upload?.file_size, b.pdf_upload?.total_pages) || 0;
                        break;
                    case 'total_pages':
                        aValue = a.pdf_upload?.total_pages || 0;
                        bValue = b.pdf_upload?.total_pages || 0;
                        break;
                    case 'status':
                        aValue = a.status || '';
                        bValue = b.status || '';
                        break;
                    case 'uploaded_by':
                        aValue = (a.pdf_upload?.uploader?.name || '').toLowerCase();
                        bValue = (b.pdf_upload?.uploader?.name || '').toLowerCase();
                        break;
                    case 'marriage_type':
                        aValue = (a.pdf_upload?.marriage_type?.name || '').toLowerCase();
                        bValue = (b.pdf_upload?.marriage_type?.name || '').toLowerCase();
                        break;
                    default:
                        aValue = a.id;
                        bValue = b.id;
                }
                
                if (typeof aValue === 'string' && typeof bValue === 'string') {
                    if (this.sortDirection === 'asc') {
                        return aValue.localeCompare(bValue);
                    } else {
                        return bValue.localeCompare(aValue);
                    }
                }
                
                if (this.sortDirection === 'asc') {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });
        },
        
        updatePagination() {
            const itemsPerPage = parseInt(this.perPage);
            const startIndex = (this.currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            
            this.paginatedItems = this.filteredItems.slice(startIndex, endIndex);
            this.totalPages = Math.ceil(this.filteredItems.length / itemsPerPage);
            this.totalItems = this.filteredItems.length;
            
            if (this.currentPage > this.totalPages && this.totalPages > 0) {
                this.currentPage = 1;
                this.updatePagination();
            }
        },
        
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                this.updatePagination();
            }
        },
        
        getPageNumbers() {
            const pages = [];
            const current = this.currentPage;
            const last = this.totalPages;
            
            if (last <= 5) {
                for (let i = 1; i <= last; i++) {
                    pages.push(i);
                }
            } else {
                if (current <= 3) {
                    pages.push(1, 2, 3, 4, '...', last);
                } else if (current >= last - 2) {
                    pages.push(1, '...', last - 3, last - 2, last - 1, last);
                } else {
                    pages.push(1, '...', current - 1, current, current + 1, '...', last);
                }
            }
            
            return pages;
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortDirection = 'desc';
                this.sortColumn = column;
            }
            this.applyFilters();
        },
        
        clearFilters() {
            this.search = '';
            this.filterYear = '';
            this.filterMonth = '';
            this.filterCounty = '';
            this.filterStatus = '';
            this.filterMarriageType = '';
            this.perPage = '10';
            this.sortColumn = 'id';
            this.sortDirection = 'desc';
            this.currentPage = 1;
            this.applyFilters();
        },
        
        getMonthName(monthNumber) {
            return this.monthNames[monthNumber] || monthNumber;
        },
        
        getMarriageTypeName(typeId) {
            if (!typeId) return 'N/A';
            const type = this.marriageTypes.find(t => t.id == typeId);
            return type ? type.name : 'N/A';
        },
        
        calculatePerPageSize(totalSize, totalPages) {
            if (!totalSize || !totalPages || totalPages === 0) return 0;
            return totalSize / totalPages;
        },
        
        formatFileSize(bytes) {
            if (!bytes || bytes === 0) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            let size = parseFloat(bytes);
            let unitIndex = 0;
            while (size >= 1024 && unitIndex < units.length - 1) {
                size /= 1024;
                unitIndex++;
            }
            return size.toFixed(unitIndex > 0 ? 2 : 0) + ' ' + units[unitIndex];
        },
        
        formatPerPageSize(totalSize, totalPages) {
            const perPageSize = this.calculatePerPageSize(totalSize, totalPages);
            return this.formatFileSize(perPageSize);
        },
        
        getStatusClass(status) {
            if (!status) return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
            
            const uploadStatusMap = {
                'uploaded': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'processing': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'ready': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'completed': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'archived': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400'
            };
            
            const pageStatusMap = {
                'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                'assigned': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                'in_progress': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400',
                'completed': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                'review_needed': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
                'skipped': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
            };
            
            return uploadStatusMap[status] || pageStatusMap[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
        },
        
        getStatusBadge(status) {
            if (!status) return 'N/A';
            
            const uploadStatusMap = {
                'uploaded': 'Uploaded',
                'processing': 'Processing',
                'ready': 'Ready',
                'completed': 'Completed',
                'archived': 'Archived'
            };
            
            const pageStatusMap = {
                'pending': 'Pending',
                'assigned': 'Assigned',
                'in_progress': 'In Progress',
                'completed': 'Completed',
                'review_needed': 'Review Needed',
                'skipped': 'Skipped'
            };
            
            const displayText = uploadStatusMap[status] || pageStatusMap[status];
            return displayText || status.charAt(0).toUpperCase() + status.slice(1);
        },
        
        buildRoute(route, id) {
            return route.replace(':id', id);
        }
    }"
    x-init="init()"
    class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]"
>
    <!-- Table Controls -->
    <div class="mb-4 px-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <!-- Left Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Show Entries -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">Show</span>
                    <div class="relative z-20 bg-transparent w-20">
                        <select
                            x-model="perPage"
                            @change="currentPage = 1; applyFilters()"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="absolute top-1/2 right-2 z-30 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">entries</span>
                </div>
            </div>

            <!-- Center Controls - Filters -->
            <div class="flex flex-wrap items-center gap-2 justify-center">
                <!-- Year Filter -->
                <div class="relative z-20 bg-transparent w-32">
                    <select
                        x-model="filterYear"
                        @change="currentPage = 1; applyFilters()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Month Filter -->
                <div class="relative z-20 bg-transparent w-36">
                    <select
                        x-model="filterMonth"
                        @change="currentPage = 1; applyFilters()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                        <option value="">All Months</option>
                        @foreach($months as $key => $month)
                            <option value="{{ $key }}">{{ $month }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- County Filter -->
                <div class="relative z-20 bg-transparent w-40">
                    <select
                        x-model="filterCounty"
                        @change="currentPage = 1; applyFilters()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                        <option value="">All Counties</option>
                        @foreach($counties as $county)
                            <option value="{{ $county->county_code }}">
                                {{ $county->name }} ({{ $county->county_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="relative z-20 bg-transparent w-40">
                    <select
                        x-model="filterStatus"
                        @change="currentPage = 1; applyFilters()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    >
                        <option value="">All Status</option>
                        <!-- PDF Upload Statuses -->
                        <option value="uploaded">Uploaded</option>
                        <option value="processing">Processing</option>
                        <option value="ready">Ready</option>
                        <option value="completed">Completed</option>
                        <option value="archived">Archived</option>
                        <!-- PDF Page Statuses -->
                        <option value="pending">Pending</option>
                        <option value="assigned">Assigned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="review_needed">Review Needed</option>
                        <option value="skipped">Skipped</option>
                    </select>
                </div>

                <!-- Marriage Type Filter -->
                <div class="relative z-20 bg-transparent w-36">
                    <select
                        x-model="filterMarriageType"
                        @change="currentPage = 1; applyFilters()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                    >
                        <option value="">All Types</option>
                        @foreach($marriageTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <button 
                    @click="clearFilters()"
                    x-show="hasActiveFilters"
                    x-cloak
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 whitespace-nowrap"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear Filters
                </button>
            </div>

            <!-- Right Controls - Search and Create -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search -->
                <div class="relative w-full lg:w-64">
                    <button class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" 
                                  d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" 
                                  fill=""/>
                        </svg>
                    </button>
                    <input
                        type="text"
                        x-model="search"
                        x-on:input.debounce.500ms="currentPage = 1; applyFilters()"
                        placeholder="Search by filename, name, county, type or status..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    />
                    <!-- Clear search button -->
                    <button 
                        x-show="search"
                        @click="search = ''; currentPage = 1; applyFilters()"
                        class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        x-cloak
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Create Button -->
                <button
                    @click="openCreateModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03] whitespace-nowrap"
                >
                    <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z" fill=""/>
                    </svg>
                    Upload New PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Active Filters Display -->
    <div x-show="hasActiveFilters" x-cloak class="mb-4 px-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Active filters:</span>
            
            <template x-if="search">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                    Search: "<span x-text="search"></span>"
                    <button @click="search = ''; currentPage = 1; applyFilters()" class="ml-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterYear">
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    Year: <span x-text="filterYear"></span>
                    <button @click="filterYear = ''; currentPage = 1; applyFilters()" class="ml-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterMonth">
                <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                    Month: <span x-text="getMonthName(filterMonth)"></span>
                    <button @click="filterMonth = ''; currentPage = 1; applyFilters()" class="ml-1 text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterCounty">
                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                    County: <span x-text="filterCounty"></span>
                    <button @click="filterCounty = ''; currentPage = 1; applyFilters()" class="ml-1 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterStatus">
                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-400">
                    Status: <span x-text="getStatusBadge(filterStatus)"></span>
                    <button @click="filterStatus = ''; currentPage = 1; applyFilters()" class="ml-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
            
            <template x-if="filterMarriageType">
                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                    Type: <span x-text="getMarriageTypeName(filterMarriageType)"></span>
                    <button @click="filterMarriageType = ''; currentPage = 1; applyFilters()" class="ml-1 text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
            </template>
        </div>
    </div>

    <!-- Table -->
    <div class="max-w-full overflow-x-auto">
        <div class="min-w-[1200px]">
            <!-- Table Header -->
            <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800">
                <!-- ID - 60px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('id')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">ID</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'id' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'id' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'id' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'id' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Filename - 180px -->
                <div class="col-span-2 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('filename')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400 truncate">Filename</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'filename' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'filename' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'filename' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'filename' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Year - 80px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('year')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Year</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'year' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'year' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'year' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'year' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Month - 100px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('month')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Month</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'month' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'month' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'month' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'month' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- County - 120px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('county_code')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400 truncate">County</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'county_code' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'county_code' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'county_code' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'county_code' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Marriage Type - 120px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('marriage_type')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400 truncate">Type</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'marriage_type' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'marriage_type' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'marriage_type' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'marriage_type' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Size - 100px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('file_size')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400 truncate">Size</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'file_size' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'file_size' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'file_size' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'file_size' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Pages - 80px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('total_pages')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Pages</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'total_pages' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'total_pages' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'total_pages' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'total_pages' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Status - 100px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('status')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Status</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'status' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'status' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'status' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'status' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Uploaded By - 120px -->
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('uploaded_by')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400 truncate">Uploaded By</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'uploaded_by' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'uploaded_by' && sortDirection === 'asc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/>
                            </svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'uploaded_by' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'uploaded_by' && sortDirection === 'desc')}" 
                                  width="8" height="5" viewBox="0 0 8 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Actions - 80px -->
                <div class="col-span-1 flex items-center px-2 py-3">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Actions</p>
                </div>
            </div>
            <!-- Table Header End -->

            <!-- Table Body -->
            <template x-if="paginatedItems.length > 0">
                <template x-for="item in paginatedItems" :key="item.id">
                    <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                        <!-- ID -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-sm block font-medium text-gray-600 dark:text-gray-400" x-text="item.id"></span>
                        </div>
                        
                        <!-- Filename -->
                        <div class="col-span-2 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <div class="truncate">
                                <a :href="buildRoute(showRoute, item.id)" 
                                   class="text-sm block font-medium text-gray-800 dark:text-white/90 hover:text-blue-600 truncate"
                                   :title="item.pdf_upload?.name || 'Page ' + item.page_number">
                                   <span x-text="item.pdf_upload?.name || 'Unknown PDF'"></span>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Year -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-400" x-text="item.pdf_upload?.year"></span>
                        </div>
                        
                        <!-- Month (Name) -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-400 truncate" 
                                  x-text="getMonthName(item.pdf_upload?.month)"></span>
                        </div>
                        
                        <!-- County -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-400 truncate" 
                                  x-text="item.pdf_upload?.county?.name || 'N/A'"></span>
                        </div>
                        
                        <!-- Marriage Type -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-400 truncate" 
                                :title="item.pdf_upload?.marriage_type?.name || 'N/A'">
                                <span x-text="item.pdf_upload?.marriage_type?.name || 'N/A'"></span>
                            </span>
                        </div>

                        <!-- Size Per Page -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-400 truncate" 
                                  x-text="formatPerPageSize(item.pdf_upload?.file_size, item.pdf_upload?.total_pages)"></span>
                        </div>
                        
                        <!-- Pages -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-400">
                                <span x-text="item.page_number"></span>/<span x-text="item.pdf_upload?.total_pages"></span>
                            </span>
                        </div>
                        
                        <!-- Status (Compact) -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-xs inline-flex rounded-full px-2 py-1 font-medium min-w-[70px] justify-center" 
                                  :class="getStatusClass(item.status)"
                                  x-text="getStatusBadge(item.status)">
                            </span>
                        </div>
                        
                        <!-- Uploaded By -->
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <div class="truncate">
                                <span class="text-sm text-gray-700 dark:text-gray-400 truncate" 
                                      x-text="item.pdf_upload?.uploader?.name || 'Unknown'">
                                </span>
                            </div>
                        </div>
                        
                        <!-- Actions (Ellipses Menu) -->
                        <div class="col-span-1 flex items-center px-2 py-3">
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                    <svg class="fill-current w-5 h-5" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M5.999 10.245C6.966 10.245 7.749 11.029 7.749 12s-.783 1.755-1.75 1.755S4.249 12.971 4.249 12s.783-1.755 1.75-1.755zm6 0c.967 0 1.75.784 1.75 1.755s-.783 1.755-1.75 1.755S10.249 12.971 10.249 12s.783-1.755 1.75-1.755zm6 0c.967 0 1.75.784 1.75 1.755s-.783 1.755-1.75 1.755S16.249 12.971 16.249 12s.783-1.755 1.75-1.755z"/>
                                    </svg>
                                </button>

                                <!-- Dropdown menu -->
                                <div x-show="open" @click.outside="open = false" x-transition
                                    x-cloak
                                    class="absolute right-0 z-50 mt-2 w-36 rounded-lg border border-gray-200 bg-white shadow-lg dark:bg-gray-800 dark:border-gray-700 space-y=1 p-1">
                                    
                                    <a :href="buildRoute(showRoute, item.id)"
                                    class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    
                                    <button @click="openEditModal(item); open = false"
                                    class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm text-yellow-600 rounded-md hover:bg-yellow-50 dark:text-yellow-400 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    
                                    <button @click="openDeleteModal(item); open = false"
                                            class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm text-red-600 rounded-md hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
            <template x-if="paginatedItems.length === 0">
                <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                    <div class="col-span-12 flex items-center justify-center px-4 py-8">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                <span x-show="hasActiveFilters">No PDFs match your filters</span>
                                <span x-show="!hasActiveFilters">No PDFs found</span>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <span x-show="hasActiveFilters">Try adjusting your filters or search terms</span>
                                <span x-show="!hasActiveFilters">Get started by uploading a new PDF file</span>
                            </p>
                            <div class="mt-4 flex flex-col sm:flex-row gap-3 justify-center">
                                <button 
                                    @click="openCreateModal()"
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    Upload Your First PDF
                                </button>
                                <button 
                                    x-show="hasActiveFilters"
                                    @click="clearFilters()"
                                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    Clear All Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <!-- Table Body End -->
        </div>
    </div>

    <!-- Pagination -->
    <div x-show="paginatedItems.length > 0" class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
            <!-- Pagination Info -->
            <div class="mb-4 xl:mb-0">
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    Showing
                    <span x-text="paginatedItems.length > 0 ? (currentPage - 1) * parseInt(perPage) + 1 : 0"></span>
                    to
                    <span x-text="Math.min(currentPage * parseInt(perPage), totalItems)"></span>
                    of
                    <span x-text="totalItems"></span>
                    entries
                </p>
            </div>

            <!-- Pagination Controls -->
            <div class="flex items-center gap-2">
                <!-- Previous Button -->
                <button 
                    @click="changePage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    :class="currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-400"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>

                <!-- Page Numbers -->
                <template x-for="page in getPageNumbers()" :key="page">
                    <div>
                        <button 
                            x-show="page !== '...'"
                            @click="changePage(page)"
                            :class="page === currentPage 
                                ? 'bg-blue-500 text-white border-blue-500' 
                                : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800'"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-medium transition"
                            x-text="page"
                        ></button>
                        <span 
                            x-show="page === '...'"
                            class="flex h-9 w-9 items-center justify-center text-gray-500"
                        >...</span>
                    </div>
                </template>

                <!-- Next Button -->
                <button 
                    @click="changePage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    :class="currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-400"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ================ CREATE MODAL ================ -->
    <div 
        x-show="isCreateModalOpen" 
        class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999"
        x-cloak
    >
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div 
            @click.outside="isCreateModalOpen = false"
            class="relative w-full max-w-[800px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10"
        >
            <!-- Close Button -->
            <button
                @click="isCreateModalOpen = false"
                class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
            >
                <svg
                    class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                        fill=""
                    />
                </svg>
            </button>

            <!-- Modal Title -->
            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
                Upload New PDF
            </h4>

            <!-- Error Message -->
            <div x-show="modalError" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-800 dark:text-red-300" x-text="modalError"></p>
                </div>
            </div>

            <!-- Create Form -->
            <div class="space-y-4">
                <!-- Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Year -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Year *
                        </label>
                        <input
                            type="number"
                            x-model="formData.year"
                            min="2000"
                            :max="new Date().getFullYear()"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                    </div>

                    <!-- Month -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Month *
                        </label>
                        <select
                            x-model="formData.month"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        >
                            <option value="">Select Month</option>
                            @foreach($months as $key => $month)
                                <option value="{{ $month }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- County -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            County *
                        </label>
                        <select
                            x-model="formData.county_code"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        >
                            <option value="">Select County</option>
                            @foreach($counties as $county)
                                <option value="{{ $county->county_code }}">
                                    {{ $county->name }} ({{ $county->county_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Marriage Type -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Marriage Type
                        </label>
                        <select
                            x-model="formData.marriage_type_id"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        >
                            <option value="">Select Type</option>
                            @foreach($marriageTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- File Upload -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        PDF File *
                    </label>
                    <div 
                        id="create-drop-area"
                        class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors duration-200"
                    >
                        <input 
                            type="file" 
                            id="create-pdf-file"
                            accept=".pdf" 
                            class="hidden"
                            @change="handleFileSelect($event.target.files[0])"
                        />
                        <label for="create-pdf-file" class="cursor-pointer block">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-lg text-gray-700 dark:text-gray-300 mb-2">Click to upload or drag & drop</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Maximum file size: 100MB</p>
                            
                            <!-- File Info Display -->
                            <template x-if="fileName">
                                <div class="mt-2">
                                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium" x-text="fileName"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="fileSize"></p>
                                    <button 
                                        @click="clearFile()"
                                        type="button"
                                        class="mt-2 text-sm text-red-600 dark:text-red-400 hover:text-red-800"
                                    >
                                        Remove File
                                    </button>
                                </div>
                            </template>
                            <template x-if="!fileName">
                                <p class="text-sm text-blue-600 dark:text-blue-400">No file chosen</p>
                            </template>
                        </label>
                    </div>
                </div>

                <!-- Upload Progress Indicator -->
                <div x-show="isUploading" x-cloak class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="mb-2 flex justify-between text-sm">
                        <span x-text="uploadStatus" class="text-blue-600 dark:text-blue-400 font-medium"></span>
                        <span x-text="uploadProgress + '%'" class="font-bold"></span>
                    </div>
                    <div class="h-3 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                        <div 
                            class="h-full rounded-full bg-blue-500 transition-all duration-300"
                            :style="{ width: uploadProgress + '%' }"
                        ></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                        Uploading large file in chunks...
                        Please don't close this window.
                    </p>
                </div>

                <!-- Info Section -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        <strong>Important:</strong> Year, Month, and County will be locked for all pages in this PDF and cannot be changed later.
                    </p>
                    <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                        <strong>Note:</strong> Files larger than 2MB will be uploaded automatically using base64 chunking.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end w-full gap-3 mt-6">
                <button
                    @click="isCreateModalOpen = false"
                    type="button"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                    :disabled="isUploading"
                >
                    Cancel
                </button>
                <button
                    @click="submitCreateForm()"
                    type="button"
                    class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-500 shadow-theme-xs hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                    :disabled="!formData.year || !formData.month || !formData.county_code || !formData.pdf_file || isLoading || isUploading"
                >
                    <template x-if="isLoading || isUploading">
                        <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isUploading ? 'Uploading...' : (isLoading ? 'Processing...' : 'Upload PDF')"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ================ EDIT MODAL ================ -->
    <div 
        x-show="isEditModalOpen" 
        class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999"
        x-cloak
    >
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div 
            @click.outside="isEditModalOpen = false"
            class="relative w-full max-w-[800px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10"
        >
            <!-- Close Button -->
            <button
                @click="isEditModalOpen = false"
                class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
            >
                <svg
                    class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                        fill=""
                    />
                </svg>
            </button>

            <!-- Modal Title -->
            <h4 class="mb-6 text-lg font-medium text-gray-800 dark:text-white/90">
                Edit PDF: <span x-text="selectedItem?.pdf_upload?.name || 'Unknown PDF'"></span>
                <template x-if="selectedItem">
                    <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                        (Page <span x-text="selectedItem.page_number"></span> of <span x-text="selectedItem.pdf_upload?.total_pages"></span>)
                    </span>
                </template>
            </h4>

            <!-- Error Message -->
            <div x-show="modalError" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg dark:bg-red-900/20 dark:border-red-800">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-red-800 dark:text-red-300" x-text="modalError"></p>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="space-y-4">
                <!-- Hidden fields -->
                <input type="hidden" x-model="formData.pdf_upload_id">
                <input type="hidden" x-model="formData.page_id">
                
                <!-- Grid Layout -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Year -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Year *
                        </label>
                        <input
                            type="number"
                            x-model="formData.year"
                            min="2000"
                            :max="new Date().getFullYear()"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                    </div>

                    <!-- Month -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Month *
                        </label>
                        <select
                            x-model="formData.month"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        >
                            <option value="">Select Month</option>
                            @foreach($months as $key => $month)
                                <option value="{{ $month }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- County -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            County *
                        </label>
                        <select
                            x-model="formData.county_code"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        >
                            <option value="">Select County</option>
                            @foreach($counties as $county)
                                <option value="{{ $county->county_code }}">
                                    {{ $county->name }} ({{ $county->county_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Marriage Type -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Marriage Type
                        </label>
                        <select
                            x-model="formData.marriage_type_id"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        >
                            <option value="">Select Type</option>
                            @foreach($marriageTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- <!-- PDF Upload Status -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            PDF Status *
                        </label>
                        <select
                            x-model="formData.pdf_upload_status"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        >
                            <option value="uploaded">Uploaded</option>
                            <option value="processing">Processing</option>
                            <option value="ready">Ready</option>
                            <option value="completed">Completed</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>

                    <!-- Page Status -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Page Status
                        </label>
                        <select
                            x-model="formData.page_status"
                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        >
                            <option value="">Select Page Status</option>
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="review_needed">Review Needed</option>
                            <option value="skipped">Skipped</option>
                        </select>
                    </div> --}}
                </div>

                <!-- File Replacement -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Replace PDF (optional)
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                        <input 
                            type="file" 
                            accept=".pdf" 
                            class="hidden"
                            id="edit-pdf-file"
                            @change="handleFileSelect($event.target.files[0])"
                        />
                        <label for="edit-pdf-file" class="cursor-pointer">
                            <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-sm text-gray-500 mb-2">Click to upload new PDF</p>
                            
                            <template x-if="fileName">
                                <p class="text-sm text-blue-600 dark:text-blue-400 font-medium" x-text="fileName"></p>
                            </template>
                            <template x-if="!fileName">
                                <p class="text-sm text-blue-600 dark:text-blue-400">Keep current file</p>
                            </template>
                        </label>
                    </div>
                </div>

                <!-- Current File Info -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>Current file:</strong> 
                        <span x-text="selectedItem?.pdf_upload?.name || 'Unknown'"></span>
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        <strong>Current page:</strong> 
                        <span x-text="selectedItem?.page_number || 'Unknown'"></span>
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end w-full gap-3 mt-6">
                <button
                    @click="isEditModalOpen = false"
                    type="button"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                >
                    Cancel
                </button>
                <button
                    @click="submitEditForm()"
                    type="button"
                    class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-blue-500 shadow-theme-xs hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                    :disabled="isLoading"
                >
                    <template x-if="isLoading">
                        <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isLoading ? 'Saving...' : 'Save Changes'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ================ DELETE MODAL ================ -->
    <div 
        x-show="isDeleteModalOpen" 
        class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-99999"
        x-cloak
    >
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div 
            @click.outside="isDeleteModalOpen = false"
            class="relative w-full max-w-[500px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10"
        >
            <!-- Close Button -->
            <button
                @click="isDeleteModalOpen = false"
                class="group absolute right-3 top-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition-colors hover:bg-gray-300 hover:text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 sm:right-6 sm:top-6 sm:h-11 sm:w-11"
            >
                <svg
                    class="transition-colors fill-current group-hover:text-gray-600 dark:group-hover:text-gray-200"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M6.04289 16.5413C5.65237 16.9318 5.65237 17.565 6.04289 17.9555C6.43342 18.346 7.06658 18.346 7.45711 17.9555L11.9987 13.4139L16.5408 17.956C16.9313 18.3466 17.5645 18.3466 17.955 17.956C18.3455 17.5655 18.3455 16.9323 17.955 16.5418L13.4129 11.9997L17.955 7.4576C18.3455 7.06707 18.3455 6.43391 17.955 6.04338C17.5645 5.65286 16.9313 5.65286 16.5408 6.04338L11.9987 10.5855L7.45711 6.0439C7.06658 5.65338 6.43342 5.65338 6.04289 6.0439C5.65237 6.43442 5.65237 7.06759 6.04289 7.45811L10.5845 11.9997L6.04289 16.5413Z"
                        fill=""
                    />
                </svg>
            </button>

            <!-- Warning Icon -->
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>

            <!-- Confirmation Message -->
            <div class="text-center">
                <h4 class="mb-3 text-xl font-semibold text-gray-800 dark:text-white/90">
                    Delete PDF?
                </h4>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Are you sure you want to delete 
                    <strong x-text="selectedItem?.pdf_upload?.name || 'this PDF'"></strong>?
                    This action cannot be undone.
                </p>
                
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <p class="text-sm text-red-800 dark:text-red-300">
                        <strong>Warning:</strong> This will delete all associated page records and any marriage data linked to this PDF.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-center w-full gap-3 mt-6">
                <button
                    @click="isDeleteModalOpen = false"
                    type="button"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 sm:w-auto"
                >
                    Cancel
                </button>
                <button
                    @click="submitDelete()"
                    type="button"
                    class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-red-500 shadow-theme-xs hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
                    :disabled="isLoading"
                >
                    <template x-if="isLoading">
                        <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <span x-text="isLoading ? 'Deleting...' : 'Delete PDF'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
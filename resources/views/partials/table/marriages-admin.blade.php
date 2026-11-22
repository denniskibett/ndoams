<!-- Admin Marriage List -->
<div x-data="{
    marriages: {{ $marriages->toJson() }},
    currentPage: 1,
    perPage: 10,
    
    get paginatedMarriages() {
        const start = (this.currentPage - 1) * this.perPage;
        const end = start + this.perPage;
        return this.marriages.slice(start, end);
    },
    
    get totalPages() {
        return Math.ceil(this.marriages.length / this.perPage);
    },
    
    get showingStart() {
        return ((this.currentPage - 1) * this.perPage) + 1;
    },
    
    get showingEnd() {
        const end = this.currentPage * this.perPage;
        return end > this.marriages.length ? this.marriages.length : end;
    },
    
    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.currentPage++;
        }
    },
    
    prevPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
        }
    },
    
    goToPage(page) {
        if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
        }
    }
}" 
x-init="$watch('currentPage', value => console.log('Page changed to:', value))"
class="bg-white dark:bg-gray-800 rounded-lg shadow">
    
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Marriage Records</h2>
        <span class="text-sm text-gray-600 dark:text-gray-400">
            Total: <span x-text="marriages.length"></span> records
        </span>
    </div>
    
    <div class="p-6">
        <template x-if="marriages.length > 0">
            <div>
                <!-- Records per page selector -->
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600 dark:text-gray-400">Show</label>
                        <select 
                            x-model="perPage"
                            class="border border-gray-300 dark:border-gray-600 rounded-lg px-2 py-1 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        >
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-600 dark:text-gray-400">entries</span>
                    </div>
                    
                    <!-- Showing info -->
                    <span class="text-sm text-gray-600 dark:text-gray-400" x-show="marriages.length > 0">
                        Showing <span x-text="showingStart"></span> to <span x-text="showingEnd"></span> of <span x-text="marriages.length"></span> entries
                    </span>
                </div>

                <!-- Marriage records list -->
                <div class="space-y-4">
                    <template x-for="marriage in paginatedMarriages" :key="marriage.id">
                        <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">
                                    <span x-show="marriage.certificate_serial">
                                        Certificate: <span x-text="marriage.certificate_serial"></span>
                                    </span>
                                    <span x-show="!marriage.certificate_serial" class="text-gray-400">
                                        Certificate: Not Entered Yet
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Date: 
                                    <span x-show="marriage.marriage_date">
                                        <span x-text="new Date(marriage.marriage_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })"></span>
                                    </span>
                                    <span x-show="!marriage.marriage_date" class="text-gray-400">Date not set</span>
                                    | 
                                    Spouses: <span x-text="marriage.spouses ? marriage.spouses.length : 0"></span> |
                                    Witnesses: <span x-text="marriage.witnesses ? marriage.witnesses.length : 0"></span> |
                                    Created by: <span x-text="marriage.created_by ? (marriage.created_by.name || 'System') : 'System'"></span>
                                </p>
                                <div class="flex space-x-2 mt-2">
                                    <!-- System Status Badge -->
                                    <span 
                                        class="px-2 py-1 text-xs rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': marriage.system_status === 'Completed',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': marriage.system_status === 'Pending',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': marriage.system_status === 'Approved',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300': !['Completed', 'Pending', 'Approved'].includes(marriage.system_status)
                                        }"
                                        x-text="marriage.system_status || 'Unknown'">
                                    </span>
                                    
                                    <!-- Verification Status Badge -->
                                    <span 
                                        class="px-2 py-1 text-xs rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': marriage.verification_status === 'Verified',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': marriage.verification_status === 'Unverified',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': marriage.verification_status && marriage.verification_status !== 'Verified' && marriage.verification_status !== 'Unverified'
                                        }"
                                        x-text="marriage.verification_status || 'Unknown'">
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <a :href="'{{ route('marriages.show', 'request') }}/' + marriage.id" 
                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 px-3 py-1 rounded border border-blue-200 dark:border-blue-800">
                                    View
                                </a>
                                
                                <a :href="'{{ route('marriages.edit', 'request') }}/' + marriage.id" 
                                   class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 px-3 py-1 rounded border border-green-200 dark:border-green-800">
                                    Edit
                                </a>
                                
                                <form :action="'{{ route('marriages.destroy', 'request') }}/' + marriage.id" 
                                      method="POST" 
                                      @submit.prevent="if(confirm('Are you sure you want to delete this marriage record?')) $el.submit()" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 px-3 py-1 rounded border border-red-200 dark:border-red-800">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Pagination Controls -->
                <div class="mt-6 flex items-center justify-between" x-show="totalPages > 1">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button 
                            @click="prevPage" 
                            :disabled="currentPage === 1"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                            :class="{'opacity-50 cursor-not-allowed': currentPage === 1}">
                            Previous
                        </button>
                        <button 
                            @click="nextPage" 
                            :disabled="currentPage === totalPages"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                            :class="{'opacity-50 cursor-not-allowed': currentPage === totalPages}">
                            Next
                        </button>
                    </div>
                    
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- Previous Page -->
                                <button 
                                    @click="prevPage" 
                                    :disabled="currentPage === 1"
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700"
                                    :class="{'opacity-50 cursor-not-allowed': currentPage === 1}">
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <!-- Page Numbers -->
                                <template x-for="page in Math.min(5, totalPages)" :key="page">
                                    <button 
                                        @click="goToPage(page)"
                                        :class="{
                                            'bg-blue-50 border-blue-500 text-blue-600 z-10 dark:bg-blue-900 dark:text-blue-300': currentPage === page,
                                            'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700': currentPage !== page
                                        }"
                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                        <span x-text="page"></span>
                                    </button>
                                </template>
                                
                                <!-- Next Page -->
                                <button 
                                    @click="nextPage" 
                                    :disabled="currentPage === totalPages"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-700"
                                    :class="{'opacity-50 cursor-not-allowed': currentPage === totalPages}">
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </template>
        
        <template x-if="marriages.length === 0">
            @include('partials.table.empty', ['message' => 'No marriage records found.'])
        </template>
    </div>
</div>
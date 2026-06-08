{{-- resources/views/partials/table/table-clerks.blade.php --}}
<div x-data="clerkManagement()" x-init="init()" class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]">
    <!-- Table Controls -->
    <div class="mb-4 px-4">
        <div class="flex flex-nowrap items-start gap-4 overflow-x-auto pb-2 lg:overflow-visible lg:flex-row lg:items-center lg:justify-between">
            <div class="flex shrink-0 items-center gap-2">
                <span class="text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">Show</span>
                <div class="relative w-20">
                    <select x-model="perPage" @change="currentPage = 1; applyFilters()" class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
                <span class="text-sm text-gray-500 whitespace-nowrap dark:text-gray-400">entries</span>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <div class="relative w-36">
                    <select x-model="filterStatus" @change="currentPage = 1; applyFilters()" class="h-10 bg-none w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-3 py-2 pr-8 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <span class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-500 dark:text-gray-400 pointer-events-none">
                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3.8335 5.9165L8.00016 10.0832L12.1668 5.9165" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </div>
                <button @click="clearFilters()" x-show="hasActiveFilters" x-cloak class="inline-flex h-10 shrink-0 items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 whitespace-nowrap">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear
                </button>
            </div>

            <div class="flex shrink-0 items-center gap-3">
                <div class="relative w-64">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                        <svg class="fill-current" width="18" height="18" viewBox="0 0 20 20" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z" fill="currentColor"/></svg>
                    </span>
                    <input type="text" x-model="search" x-on:input.debounce.500ms="currentPage = 1; applyFilters()" placeholder="Search by name, email, or notes..." class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-0 pl-9 pr-8 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"/>
                    <button x-show="search" @click="search = ''; currentPage = 1; applyFilters()" class="absolute top-1/2 right-2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" x-cloak>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <button @click="openAddClerkModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add Data Clerk
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
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </span>
            </template>
            <template x-if="filterStatus">
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                    Status: <span x-text="filterStatus === 'active' ? 'Active' : 'Inactive'"></span>
                    <button @click="filterStatus = ''; currentPage = 1; applyFilters()" class="ml-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </span>
            </template>
        </div>
    </div>

    <!-- Table -->
    <div class="max-w-full overflow-x-auto">
        <div class="min-w-[1100px]">
            <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800">
                <div class="col-span-3 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('name')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Clerk</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'name' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'name' && sortDirection === 'asc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/></svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'name' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'name' && sortDirection === 'desc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/></svg>
                        </span>
                    </button>
                </div>
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('status')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Status</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'status' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'status' && sortDirection === 'asc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/></svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'status' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'status' && sortDirection === 'desc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/></svg>
                        </span>
                    </button>
                </div>
                <div class="col-span-2 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('progress')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Progress</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'progress' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'progress' && sortDirection === 'asc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/></svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'progress' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'progress' && sortDirection === 'desc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/></svg>
                        </span>
                    </button>
                </div>
                <div class="col-span-2 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('target')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Pages</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'target' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'target' && sortDirection === 'asc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/></svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'target' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'target' && sortDirection === 'desc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/></svg>
                        </span>
                    </button>
                </div>
                <div class="col-span-2 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <button @click="sortBy('rating')" class="flex w-full items-center justify-between hover:text-blue-500">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Rating</p>
                        <span class="flex flex-col gap-0.5 ml-1">
                            <svg :class="{'fill-blue-500': sortColumn === 'rating' && sortDirection === 'asc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'rating' && sortDirection === 'asc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"/></svg>
                            <svg :class="{'fill-blue-500': sortColumn === 'rating' && sortDirection === 'desc', 'fill-gray-300 dark:fill-gray-700': !(sortColumn === 'rating' && sortDirection === 'desc')}" width="8" height="5" viewBox="0 0 8 5" fill="none"><path d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"/></svg>
                        </span>
                    </button>
                </div>
                <div class="col-span-1 flex items-center border-r border-gray-200 px-2 py-3 dark:border-gray-800">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Notes</p>
                </div>
                <div class="col-span-1 flex items-center px-2 py-3">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-400">Actions</p>
                </div>
            </div>

            <template x-if="paginatedItems.length > 0">
                <template x-for="item in paginatedItems" :key="item.id">
                    <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                        <div class="col-span-3 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <div>
                                <a :href="buildRoute(showRoute, item.id)" class="text-sm block font-medium text-gray-800 dark:text-white/90 hover:text-blue-600">
                                    <span x-text="item.dataClerk?.name || item.data_clerk?.name || 'Unknown'"></span>
                                </a>
                                <div class="text-xs text-gray-500 dark:text-gray-400" x-text="item.dataClerk?.email || ''"></div>
                            </div>
                        </div>
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-xs inline-flex rounded-full px-2 py-1 font-medium" :class="getStatusClass(item.status)" x-text="getStatusBadge(item.status)"></span>
                        </div>
                        <div class="col-span-2 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <div class="w-full">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600 dark:text-gray-400" x-text="(item.progress_percentage || 0) + '%'"></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2 rounded-full" :style="{ width: (item.progress_percentage || 0) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-2 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span class="text-sm text-gray-700 dark:text-gray-400"><span x-text="item.filled_count || 0"></span> / <span x-text="item.target_count || 0"></span></span>
                        </div>
                        <div class="col-span-2 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <div class="flex items-center">
                                <template x-if="item.rating">
                                    <div class="flex">
                                        <template x-for="i in 5" :key="i">
                                            <svg class="w-4 h-4" :class="i <= item.rating ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current'" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                            </svg>
                                        </template>
                                    </div>
                                </template>
                                <span x-show="!item.rating" class="text-sm text-gray-400">Not rated</span>
                            </div>
                        </div>
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-2 py-3 dark:border-gray-800">
                            <span x-show="item.notes" x-text="item.notes" class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-[120px]" :title="item.notes"></span>
                            <span x-show="!item.notes" class="text-sm text-gray-400">-</span>
                        </div>
                        <div class="col-span-1 flex items-center px-2 py-3">
                            <div class="flex items-center justify-center">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                        <svg class="fill-current" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" fill="currentColor"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.outside="open = false" x-transition x-cloak class="absolute right-0 z-50 mt-2 w-44 space-y-1 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                                        <a :href="buildRoute(showRoute, item.id)" class="text-theme-xs flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            View Pages
                                        </a>
                                        <button @click="openAssignModal(item); open = false" class="text-theme-xs flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            Assign Records
                                        </button>
                                        <button @click="openEditModal(item); open = false" class="text-theme-xs flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </button>
                                        <button @click="openDeleteModal(item); open = false" class="text-theme-xs flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left font-medium text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Remove
                                        </button>
                                    </div>
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
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">
                                <span x-show="hasActiveFilters">No clerks match your filters</span>
                                <span x-show="!hasActiveFilters">No clerks assigned yet</span>
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <span x-show="hasActiveFilters">Try adjusting your filters or search terms</span>
                                <span x-show="!hasActiveFilters">Click "Add Data Clerk" to get started</span>
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Pagination -->
    <div x-show="paginatedItems.length > 0" class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
            <div class="mb-4 xl:mb-0">
                <p class="text-sm text-gray-700 dark:text-gray-400">
                    Showing <span x-text="paginatedItems.length > 0 ? (currentPage - 1) * parseInt(perPage) + 1 : 0"></span>
                    to <span x-text="Math.min(currentPage * parseInt(perPage), totalItems)"></span>
                    of <span x-text="totalItems"></span> entries
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" :class="currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'" class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-400">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </button>
                <template x-for="page in getPageNumbers()" :key="page">
                    <div>
                        <button x-show="page !== '...'" @click="changePage(page)" :class="page === currentPage ? 'bg-blue-500 text-white border-blue-500' : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800'" class="flex h-9 w-9 items-center justify-center rounded-lg border text-sm font-medium transition" x-text="page"></button>
                        <span x-show="page === '...'" class="flex h-9 w-9 items-center justify-center text-gray-500">...</span>
                    </div>
                </template>
                <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages" :class="currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50 dark:hover:bg-gray-800'" class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-400">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ================ SLIDE-OVER MODALS ================ -->
    <!-- Add Clerk Slide-Over -->
    <div x-show="addClerkModalOpen" x-cloak class="fixed inset-0 overflow-hidden z-[99999]">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="addClerkModalOpen = false"></div>
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen max-w-md" x-show="addClerkModalOpen" x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                    <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-xl overflow-y-auto">
                        <div class="px-4 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Add Data Clerk</h2>
                                <button @click="addClerkModalOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex-1 px-4 py-6 sm:p-6">
                            <div x-show="modalError" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm text-red-800" x-text="modalError"></p>
                            </div>
                            <form @submit.prevent="submitAddClerk" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Data Clerk *</label>
                                    <select x-model="newClerkForm.data_clerk_id" required class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        <option value="">Choose a clerk...</option>
                                        <template x-for="clerk in availableDataClerks" :key="clerk.id">
                                            <option :value="clerk.id" x-text="clerk.name + ' (' + clerk.email + ')'"></option>
                                        </template>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Only clerks not assigned to any teller are shown.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (Optional)</label>
                                    <textarea x-model="newClerkForm.notes" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Any notes about this clerk..."></textarea>
                                </div>
                                <div class="flex justify-end gap-3 pt-4">
                                    <button type="button" @click="addClerkModalOpen = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</button>
                                    <button type="submit" :disabled="submitting" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                        <span x-show="!submitting">Add Clerk</span>
                                        <span x-show="submitting">Adding...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Clerk Slide-Over -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 overflow-hidden z-[99999]">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="editModalOpen = false"></div>
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen max-w-md" x-show="editModalOpen" x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                    <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-xl overflow-y-auto">
                        <div class="px-4 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Edit Clerk</h2>
                                <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex-1 px-4 py-6 sm:p-6">
                            <div x-show="modalError" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm text-red-800" x-text="modalError"></p>
                            </div>
                            <form @submit.prevent="updateClerk" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rating (1-5)</label>
                                    <select x-model="editForm.rating" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        <option value="">Select rating</option>
                                        <option value="1">⭐ 1 - Poor</option>
                                        <option value="2">⭐⭐ 2 - Fair</option>
                                        <option value="3">⭐⭐⭐ 3 - Good</option>
                                        <option value="4">⭐⭐⭐⭐ 4 - Very Good</option>
                                        <option value="5">⭐⭐⭐⭐⭐ 5 - Excellent</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                                    <textarea x-model="editForm.notes" rows="3" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" placeholder="Feedback about this clerk..."></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                    <select x-model="editForm.status" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="flex justify-end gap-3 pt-4">
                                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</button>
                                    <button type="submit" :disabled="submitting" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Records Slide-Over -->
    <div x-show="assignModalOpen" x-cloak class="fixed inset-0 overflow-hidden z-[99999]">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="assignModalOpen = false"></div>
            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen max-w-2xl" x-show="assignModalOpen" x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
                    <div class="h-full flex flex-col bg-white dark:bg-gray-900 shadow-xl overflow-y-auto">
                        <div class="px-4 py-6 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Assign Records to <span x-text="selectedClerk?.dataClerk?.name"></span>
                                </h2>
                                <button @click="assignModalOpen = false" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex-1 px-4 py-6 sm:p-6">
                            <div x-show="modalError" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm text-red-800" x-text="modalError"></p>
                            </div>
                            <div x-show="modalSuccess" x-cloak class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <p class="text-sm text-green-800" x-text="modalSuccess"></p>
                            </div>

                            <!-- Filters -->
                            <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800">
                                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
                                        <select x-model="assignFilters.year" @change="loadAssignablePdfs()" class="w-full border rounded-lg px-3 py-2 text-sm">
                                            <option value="">All Years</option>
                                            <template x-for="year in assignFiltersOptions.years" :key="year">
                                                <option :value="year" x-text="year"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Month</label>
                                        <select x-model="assignFilters.month" @change="loadAssignablePdfs()" class="w-full border rounded-lg px-3 py-2 text-sm">
                                            <option value="">All Months</option>
                                            <template x-for="month in assignFiltersOptions.months" :key="month">
                                                <option :value="month" x-text="getMonthName(month)"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">County</label>
                                        <select x-model="assignFilters.county" @change="loadAssignablePdfs()" class="w-full border rounded-lg px-3 py-2 text-sm">
                                            <option value="">All Counties</option>
                                            <template x-for="county in assignFiltersOptions.counties" :key="county">
                                                <option :value="county" x-text="county"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Marriage Type</label>
                                        <select x-model="assignFilters.marriageTypeId" @change="loadAssignablePdfs()" class="w-full border rounded-lg px-3 py-2 text-sm">
                                            <option value="">All Types</option>
                                            <template x-for="type in assignFiltersOptions.marriage_types" :key="type.id">
                                                <option :value="type.id" x-text="type.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="flex items-end">
                                        <button @click="resetAssignFilters()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-sm">Reset Filters</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Available PDFs List -->
                            <div class="max-h-96 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="pdf in assignablePdfs" :key="pdf.id">
                                        <div class="flex items-center p-4 hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <input type="checkbox" :value="pdf.id" x-model="selectedPdfIds" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                            <div class="ml-3 flex-1">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="pdf.name"></p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            <span x-text="pdf.year + ' - ' + getMonthName(pdf.month)"></span>
                                                            <span class="mx-1">•</span>
                                                            <span x-text="pdf.county_code || 'No county'"></span>
                                                            <span class="mx-1">•</span>
                                                            <span x-text="pdf.marriage_type?.name || 'No type'"></span>
                                                        </p>
                                                    </div>
                                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                        <span x-text="pdf.pending_pages_count"></span> pending pages
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="assignablePdfs.length === 0" class="text-center py-8 text-gray-500">
                                        No pending PDFs available for assignment
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between mt-6">
                                <div class="text-sm text-gray-600">
                                    Selected: <span x-text="selectedPdfIds.length"></span> PDFs
                                </div>
                                <div class="flex gap-3">
                                    <button @click="assignModalOpen = false" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                                    <button @click="submitAssignRecords()" :disabled="selectedPdfIds.length === 0 || assigning" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                        <span x-show="!assigning">Assign Selected</span>
                                        <span x-show="assigning">Assigning...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (center modal for confirmation) -->
    <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 flex items-center justify-center p-5 overflow-y-auto modal z-[99999]">
        <div class="fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]" @click="deleteModalOpen = false"></div>
        <div class="relative w-full max-w-md rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-10">
            <div class="text-center">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h4 class="mb-3 text-xl font-semibold text-gray-800 dark:text-white/90">Remove Clerk?</h4>
                <p class="mb-6 text-gray-600 dark:text-gray-400">
                    Are you sure you want to remove <strong x-text="selectedClerk?.dataClerk?.name"></strong>? 
                    This will unassign all pending pages.
                </p>
                <div x-show="modalError" x-cloak class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800" x-text="modalError"></p>
                </div>
            </div>
            <div class="flex items-center justify-center w-full gap-3 mt-6">
                <button @click="deleteModalOpen = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">Cancel</button>
                <button @click="submitDelete()" type="button" :disabled="submitting" class="flex justify-center w-full px-4 py-3 text-sm font-medium text-white rounded-lg bg-red-500 shadow-theme-xs hover:bg-red-600 disabled:opacity-50 sm:w-auto">
                    <span x-show="!submitting">Remove Clerk</span>
                    <span x-show="submitting">Removing...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function clerkManagement() {
    return {
        // Data properties
        search: '',
        filterStatus: '',
        perPage: '10',
        sortColumn: 'name',
        sortDirection: 'asc',
        currentPage: 1,
        totalPages: 1,
        totalItems: {{ $assignedClerks->count() }},
        items: {{ Js::from($assignedClerks) }},
        allItems: {{ Js::from($assignedClerks) }},
        paginatedItems: [],
        filteredItems: [],
        
        // Modal states
        addClerkModalOpen: false,
        editModalOpen: false,
        assignModalOpen: false,
        deleteModalOpen: false,
        
        // Form data
        newClerkForm: { data_clerk_id: '', notes: '' },
        editForm: { id: null, rating: '', notes: '', status: '' },
        selectedClerk: null,
        selectedPdfIds: [],
        assignablePdfs: [],
        assignFilters: { year: '', month: '', county: '', marriageTypeId: '' },
        assignFiltersOptions: { years: [], months: [], counties: [], marriage_types: [] },
        
        // UI states
        submitting: false,
        assigning: false,
        modalError: null,
        modalSuccess: null,
        
        showRoute: {{ Js::from(route('clerk-management.show', ':id')) }},
        availableDataClerks: {{ Js::from($availableDataClerks) }},
        
        init() {
            this.applyFilters();
        },
        
        get hasActiveFilters() {
            return !!(this.search || this.filterStatus);
        },
        
        applyFilters() {
            this.filteredItems = this.allItems.filter(item => {
                let matches = true;
                if (this.search) {
                    const searchTerm = this.search.toLowerCase();
                    const name = (item.dataClerk?.name || '').toLowerCase();
                    const email = (item.dataClerk?.email || '').toLowerCase();
                    const notes = (item.notes || '').toLowerCase();
                    matches = matches && (name.includes(searchTerm) || email.includes(searchTerm) || notes.includes(searchTerm));
                }
                if (this.filterStatus) {
                    matches = matches && (item.status === this.filterStatus);
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
                    case 'name': aValue = (a.dataClerk?.name || '').toLowerCase(); bValue = (b.dataClerk?.name || '').toLowerCase(); break;
                    case 'status': aValue = a.status || ''; bValue = b.status || ''; break;
                    case 'progress': aValue = a.progress_percentage || 0; bValue = b.progress_percentage || 0; break;
                    case 'pages': aValue = a.filled_count || 0; bValue = b.filled_count || 0; break;
                    case 'target': aValue = a.target_count || 0; bValue = b.target_count || 0; break;
                    case 'rating': aValue = a.rating || 0; bValue = b.rating || 0; break;
                    default: aValue = a.id; bValue = b.id;
                }
                if (typeof aValue === 'string') {
                    if (this.sortDirection === 'asc') return aValue.localeCompare(bValue);
                    else return bValue.localeCompare(aValue);
                }
                if (this.sortDirection === 'asc') return aValue > bValue ? 1 : -1;
                else return aValue < bValue ? 1 : -1;
            });
        },
        
        updatePagination() {
            const itemsPerPage = parseInt(this.perPage);
            const startIndex = (this.currentPage - 1) * itemsPerPage;
            this.paginatedItems = this.filteredItems.slice(startIndex, startIndex + itemsPerPage);
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
                for (let i = 1; i <= last; i++) pages.push(i);
            } else {
                if (current <= 3) pages.push(1, 2, 3, 4, '...', last);
                else if (current >= last - 2) pages.push(1, '...', last - 3, last - 2, last - 1, last);
                else pages.push(1, '...', current - 1, current, current + 1, '...', last);
            }
            return pages;
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortDirection = 'asc';
                this.sortColumn = column;
            }
            this.applyFilters();
        },
        
        clearFilters() {
            this.search = '';
            this.filterStatus = '';
            this.perPage = '10';
            this.sortColumn = 'name';
            this.sortDirection = 'asc';
            this.currentPage = 1;
            this.applyFilters();
        },
        
        getStatusClass(status) {
            if (status === 'active') return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400';
        },
        
        getStatusBadge(status) {
            return status === 'active' ? 'Active' : 'Inactive';
        },
        
        getMonthName(monthNumber) {
            const months = {1: 'January', 2: 'February', 3: 'March', 4: 'April', 5: 'May', 6: 'June', 7: 'July', 8: 'August', 9: 'September', 10: 'October', 11: 'November', 12: 'December'};
            return months[monthNumber] || monthNumber;
        },
        
        buildRoute(route, id) {
            return route.replace(':id', id);
        },
        
        // Add Clerk Methods
        openAddClerkModal() {
            this.newClerkForm = { data_clerk_id: '', notes: '' };
            this.modalError = null;
            this.addClerkModalOpen = true;
        },
        
        async submitAddClerk() {
            if (!this.newClerkForm.data_clerk_id) {
                this.modalError = 'Please select a clerk';
                setTimeout(() => { this.modalError = null; }, 3000);
                return;
            }
            this.submitting = true;
            this.modalError = null;
            try {
                const response = await fetch('{{ route("clerk-management.store") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(this.newClerkForm)
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.addClerkModalOpen = false;
                    window.location.reload();
                } else {
                    this.modalError = data.message || 'Failed to add clerk';
                }
            } catch (error) {
                this.modalError = 'Failed to add clerk';
            } finally {
                this.submitting = false;
            }
        },
        
        // Edit Clerk Methods
        openEditModal(clerk) {
            this.editForm = { id: clerk.id, rating: clerk.rating || '', notes: clerk.notes || '', status: clerk.status };
            this.modalError = null;
            this.editModalOpen = true;
        },
        
        async updateClerk() {
            this.submitting = true;
            this.modalError = null;
            try {
                const response = await fetch(`/clerk-management/${this.editForm.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ rating: this.editForm.rating, notes: this.editForm.notes, status: this.editForm.status })
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.editModalOpen = false;
                    window.location.reload();
                } else {
                    this.modalError = data.message || 'Failed to update clerk';
                }
            } catch (error) {
                this.modalError = 'Failed to update clerk';
            } finally {
                this.submitting = false;
            }
        },
        
        // Assign Records Methods
        async openAssignModal(clerk) {
            this.selectedClerk = clerk;
            this.selectedPdfIds = [];
            this.modalError = null;
            this.modalSuccess = null;
            this.assignFilters = { year: '', month: '', county: '', marriageTypeId: '' };
            this.assignModalOpen = true;
            await this.loadAssignablePdfs();
        },
        
        async loadAssignablePdfs() {
            try {
                // Use the dedicated pending-pdfs route (static route)
                let url = '{{ route("clerk-management.pending-pdfs") }}';
                
                // Build query parameters
                const params = new URLSearchParams();
                if (this.assignFilters.year) params.append('year', this.assignFilters.year);
                if (this.assignFilters.month) params.append('month', this.assignFilters.month);
                if (this.assignFilters.county) params.append('county_code', this.assignFilters.county);
                if (this.assignFilters.marriageTypeId) params.append('marriage_type_id', this.assignFilters.marriageTypeId);
                
                if (params.toString()) {
                    url += '?' + params.toString();
                }
                
                const response = await fetch(url, { 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    } 
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.assignablePdfs = data.pdf_uploads || [];
                    this.assignFiltersOptions = data.filters || { 
                        years: [], 
                        months: [], 
                        counties: [], 
                        marriage_types: [] 
                    };
                } else {
                    this.modalError = data.message || 'Failed to load PDFs';
                    this.assignablePdfs = [];
                }
            } catch (error) {
                console.error('Error loading PDFs:', error);
                this.modalError = 'Failed to load available PDFs';
                this.assignablePdfs = [];
            }
        },
        
        resetAssignFilters() {
            this.assignFilters = { year: '', month: '', county: '', marriageTypeId: '' };
            this.loadAssignablePdfs();
        },
        
        async submitAssignRecords() {
            if (this.selectedPdfIds.length === 0) {
                this.modalError = 'Please select at least one PDF to assign';
                setTimeout(() => { this.modalError = null; }, 3000);
                return;
            }
            
            this.assigning = true;
            this.modalError = null;
            this.modalSuccess = null;
            
            try {
                const response = await fetch('{{ route("clerk-management.assign-multiple") }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        clerk_management_id: this.selectedClerk.id, 
                        pdf_upload_ids: this.selectedPdfIds 
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    this.modalSuccess = data.message;
                    if (data.updated_clerk) {
                        const index = this.allItems.findIndex(item => item.id === this.selectedClerk.id);
                        if (index !== -1) this.allItems[index] = data.updated_clerk;
                        this.selectedClerk = data.updated_clerk;
                    }
                    this.applyFilters();
                    setTimeout(() => { this.assignModalOpen = false; }, 2000);
                } else {
                    this.modalError = data.message || 'Failed to assign records';
                }
            } catch (error) {
                console.error('Error:', error);
                this.modalError = 'Failed to assign records';
            } finally {
                this.assigning = false;
            }
        },
        
        // Delete Clerk Methods
        openDeleteModal(clerk) {
            this.selectedClerk = clerk;
            this.modalError = null;
            this.deleteModalOpen = true;
        },
        
        async submitDelete() {
            this.submitting = true;
            this.modalError = null;
            try {
                const response = await fetch(`/clerk-management/${this.selectedClerk.id}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    this.deleteModalOpen = false;
                    window.location.reload();
                } else {
                    this.modalError = data.message || 'Failed to remove clerk';
                }
            } catch (error) {
                this.modalError = 'Failed to remove clerk';
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
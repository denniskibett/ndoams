  <!-- TailAdmin Style DataTable -->
    <div
        x-data="pdfDataTable()"
        x-init="init()"
        class="overflow-hidden rounded-xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <!-- Table Controls -->
        <div class="mb-4 flex flex-col gap-4 px-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="text-gray-500 dark:text-gray-400"> Show </span>
                <div
                    x-data="{ isOptionSelected: false }"
                    class="relative z-20 bg-transparent"
                >
                    <select
                        x-model="perPage"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                        @click="isOptionSelected = true"
                        @change="applyFilters()"
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
                <span class="text-gray-500 dark:text-gray-400"> entries </span>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Year Filter -->
                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                    <select
                        x-model="filterYear"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                        @click="isOptionSelected = true"
                        @change="applyFilters()"
                    >
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Month Filter -->
                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                    <select
                        x-model="filterMonth"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                        @click="isOptionSelected = true"
                        @change="applyFilters()"
                    >
                        <option value="">All Months</option>
                        @foreach($months as $key => $month)
                            <option value="{{ $key }}">{{ $month }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- County Filter -->
                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                    <select
                        x-model="filterCounty"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                        @click="isOptionSelected = true"
                        @change="applyFilters()"
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
                <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
                    <select
                        x-model="filterStatus"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2 pr-8 pl-3 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                        :class="isOptionSelected && 'text-gray-500 dark:text-gray-400'"
                        @click="isOptionSelected = true"
                        @change="applyFilters()"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="relative">
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
                        @input.debounce.500ms="applyFilters()"
                        placeholder="Search by filename or name..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden xl:w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                    />
                </div>
                
                <!-- Create Button -->
                <a href="{{ route('pdf-uploads.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                    <svg
                      class="fill-current"
                      width="20"
                      height="20"
                      viewBox="0 0 20 20"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M9.77692 3.24224C9.91768 3.17186 10.0834 3.17186 10.2241 3.24224L15.3713 5.81573L10.3359 8.33331C10.1248 8.43888 9.87626 8.43888 9.66512 8.33331L4.6298 5.81573L9.77692 3.24224ZM3.70264 7.0292V13.4124C3.70264 13.6018 3.80964 13.775 3.97903 13.8597L9.25016 16.4952L9.25016 9.7837C9.16327 9.75296 9.07782 9.71671 8.99432 9.67496L3.70264 7.0292ZM10.7502 16.4955V9.78396C10.8373 9.75316 10.923 9.71683 11.0067 9.67496L16.2984 7.0292V13.4124C16.2984 13.6018 16.1914 13.775 16.022 13.8597L10.7502 16.4955ZM9.41463 17.4831L9.10612 18.1002C9.66916 18.3817 10.3319 18.3817 10.8949 18.1002L16.6928 15.2013C17.3704 14.8625 17.7984 14.17 17.7984 13.4124V6.58831C17.7984 5.83076 17.3704 5.13823 16.6928 4.79945L10.8949 1.90059C10.3319 1.61908 9.66916 1.61907 9.10612 1.90059L9.44152 2.57141L9.10612 1.90059L3.30823 4.79945C2.63065 5.13823 2.20264 5.83076 2.20264 6.58831V13.4124C2.20264 14.17 2.63065 14.8625 3.30823 15.2013L9.10612 18.1002L9.41463 17.4831Z"
                        fill=""
                      />
                    </svg>
                    Upload New PDF
                </a>
            </div>
        </div>

        <!-- Table -->
        <div class="max-w-full overflow-x-auto">
            <div class="min-w-[1400px]">
                <!-- Table Header -->
                <div class="grid grid-cols-12 border-t border-gray-200 dark:border-gray-800">
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('id')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">ID</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('filename')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Filename</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('year')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Year</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('month')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Month</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('county_code')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">County</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('file_size')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Size</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('total_pages')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Pages</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-2 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('status')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Status</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-1 flex items-center border-r border-gray-200 px-4 py-3 dark:border-gray-800">
                        <button @click="sortBy('uploaded_by')" class="flex w-full items-center justify-between hover:text-blue-500">
                            <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Uploaded By</p>
                            <span class="flex flex-col gap-0.5">
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
                    <div class="col-span-1 flex items-center px-4 py-3">
                        <p class="text-theme-xs font-medium text-gray-700 dark:text-gray-400">Actions</p>
                    </div>
                </div>
                <!-- Table Header End -->

                <!-- Table Body -->
                @if($pdfUploads->count() > 0)
                    @foreach($pdfUploads as $pdf)
                    <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="text-theme-sm block font-medium text-gray-600 dark:text-gray-400">
                                {{ $pdf->id }}
                            </span>
                        </div>
                        <div class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="truncate">
                                <a href="{{ route('pdf-uploads.show', $pdf) }}" 
                                   class="text-theme-sm block font-medium text-gray-800 dark:text-white/90 hover:text-blue-600 truncate"
                                   title="{{ $pdf->name }}">
                                    {{ $pdf->name }}
                                </a>
                            </div>
                        </div>
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                                {{ $pdf->year }}
                            </span>
                        </div>
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                                {{ $months[$pdf->month] ?? $pdf->month }}
                            </span>
                        </div>
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                              {{ $pdf->county->name ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                                {{ ($pdf->file_size) }}
                            </span>
                        </div>
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="text-theme-sm text-gray-700 dark:text-gray-400">
                                {{ $pdf->total_pages }}
                            </span>
                        </div>
                        <div class="col-span-2 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <span class="text-theme-xs inline-flex rounded-full px-3 py-1 font-medium {{ $pdf->status == 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ ucfirst($pdf->status) }}
                            </span>
                        </div>
                        <div class="col-span-1 flex items-center border-r border-gray-100 px-4 py-3 dark:border-gray-800">
                            <div class="truncate">
                                <span class="text-theme-sm text-gray-700 dark:text-gray-400 truncate">
                                    {{ $pdf->uploader->name ?? 'Unknown' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-span-1 flex items-center px-4 py-3">
                            <div class="flex items-center justify-center">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click="open = !open" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                        <!-- Ellipsis icon -->
                                        <svg class="fill-current w-6 h-6" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M5.999 10.245C6.966 10.245 7.749 11.029 7.749 12s-.783 1.755-1.75 1.755S4.249 12.971 4.249 12s.783-1.755 1.75-1.755zm6 0c.967 0 1.75.784 1.75 1.755s-.783 1.755-1.75 1.755S10.249 12.971 10.249 12s.783-1.755 1.75-1.755zm6 0c.967 0 1.75.784 1.75 1.755s-.783 1.755-1.75 1.755S16.249 12.971 16.249 12s.783-1.755 1.75-1.755z"/>
                                        </svg>
                                    </button>

                                    <!-- Dropdown menu -->
                                    <div x-show="open" @click.outside="open = false" x-transition
                                        x-cloak
                                        class="absolute right-0 z-50 mt-2 w-40 rounded-lg border border-gray-200 bg-white shadow-lg dark:bg-gray-800 dark:border-gray-700 space-y-1 p-1">
                                        
                                        <a href="{{ route('pdf-uploads.show', $pdf) }}"
                                        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                        
                                        <a href="{{ route('pdf-uploads.edit', $pdf) }}"
                                        class="flex items-center gap-2 px-3 py-2 text-sm text-yellow-600 rounded-md hover:bg-yellow-50 dark:text-yellow-400 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        
                                        <form action="{{ route('pdf-uploads.destroy', $pdf) }}" method="POST" class="w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Are you sure you want to delete this PDF?')"
                                                    class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm text-red-600 rounded-md hover:bg-red-50 dark:text-red-400 dark:hover:bg-gray-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="grid grid-cols-12 border-t border-gray-100 dark:border-gray-800">
                        <div class="col-span-12 flex items-center justify-center px-4 py-8">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No PDFs found</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Get started by uploading a new PDF file.
                                </p>
                                <a href="{{ route('pdf-uploads.create') }}" 
                                   class="mt-4 inline-flex items-center justify-center rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    Upload Your First PDF
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
                <!-- Table Body End -->
            </div>
        </div>

        <!-- Pagination -->
        @if($pdfUploads->count() > 0)
        <div class="border-t border-gray-100 py-4 pr-4 pl-[18px] dark:border-gray-800">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between">
                <p class="border-b border-gray-100 pb-3 text-center text-sm font-medium text-gray-500 xl:border-b-0 xl:pb-0 xl:text-left dark:border-gray-800 dark:text-gray-400">
                    Showing {{ $pdfUploads->firstItem() }} to {{ $pdfUploads->lastItem() }} of {{ $pdfUploads->total() }} entries
                </p>
                <div class="pt-4 xl:pt-0">
                    <nav class="inline-flex items-center gap-1">
                        {{-- Previous Page Link --}}
                        @if ($pdfUploads->onFirstPage())
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-400 dark:border-gray-800 dark:bg-gray-900">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $pdfUploads->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </a>
                        @endif

                        {{-- Pagination Elements --}}
                        @php
                            $current = $pdfUploads->currentPage();
                            $last = $pdfUploads->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $pdfUploads->url(1) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">1</a>
                            @if($start > 2)
                                <span class="inline-flex h-9 w-9 items-center justify-center text-gray-400">...</span>
                            @endif
                        @endif

                        @for ($i = $start; $i <= $end; $i++)
                            @if ($i == $current)
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-blue-500 bg-blue-500 text-sm font-medium text-white">{{ $i }}</span>
                            @else
                                <a href="{{ $pdfUploads->url($i) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">{{ $i }}</a>
                            @endif
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <span class="inline-flex h-9 w-9 items-center justify-center text-gray-400">...</span>
                            @endif
                            <a href="{{ $pdfUploads->url($last) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">{{ $last }}</a>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($pdfUploads->hasMorePages())
                            <a href="{{ $pdfUploads->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @else
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-400 dark:border-gray-800 dark:bg-gray-900">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
        @endif
    </div>
    <!-- ====== PDF Uploads DataTable End -->


@push('scripts')
<script>
    // Format file size helper
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Alpine.js DataTable Component
    function pdfDataTable() {
        return {
            search: "{{ request('search', '') }}",
            filterYear: "{{ request('year', '') }}",
            filterMonth: "{{ request('month', '') }}",
            filterCounty: "{{ request('county_code', '') }}",
            filterStatus: "{{ request('status', '') }}",
            perPage: "{{ request('per_page', '10') }}",
            sortColumn: "{{ request('sort', 'id') }}",
            sortDirection: "{{ request('direction', 'desc') }}",
            
            // Initialize values from URL
            init() {
                // Set initial values from URL parameters
                const urlParams = new URLSearchParams(window.location.search);
                this.search = urlParams.get('search') || '';
                this.filterYear = urlParams.get('year') || '';
                this.filterMonth = urlParams.get('month') || '';
                this.filterCounty = urlParams.get('county_code') || '';
                this.filterStatus = urlParams.get('status') || '';
                this.perPage = urlParams.get('per_page') || '10';
                this.sortColumn = urlParams.get('sort') || 'id';
                this.sortDirection = urlParams.get('direction') || 'desc';
            },
            
            // Apply filters function
            applyFilters() {
                const params = new URLSearchParams();
                
                if (this.search) params.append('search', this.search);
                if (this.filterYear) params.append('year', this.filterYear);
                if (this.filterMonth) params.append('month', this.filterMonth);
                if (this.filterCounty) params.append('county_code', this.filterCounty);
                if (this.filterStatus) params.append('status', this.filterStatus);
                if (this.perPage && this.perPage !== '10') params.append('per_page', this.perPage);
                if (this.sortColumn && this.sortColumn !== 'id') params.append('sort', this.sortColumn);
                if (this.sortDirection && this.sortDirection !== 'desc') params.append('direction', this.sortDirection);
                
                // Redirect with new filters
                window.location.href = '{{ route("pdf-uploads.index") }}?' + params.toString();
            },
            
            // Sorting function
            sortBy(column) {
                if (this.sortColumn === column) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortDirection = 'asc';
                    this.sortColumn = column;
                }
                this.applyFilters();
            }
        };
    }
</script>
@endpush

@php
// Helper function to format file size
if (!function_exists('formatFileSize')) {
    function formatFileSize($bytes) {
        if ($bytes == 0) return '0 Bytes';
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return number_format($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
@endphp

<style>
[x-cloak] { display: none !important; }
</style>
<!-- Marriage Registrar Marriage List -->
<div x-data="marriageRegistrarTable()" x-init="initTable()" class="space-y-5 sm:space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                All Marriage Records
            </h3>
        </div>
        <div class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6">
            <!-- Table Controls -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <!-- Search -->
                    <div class="relative">
                        <input 
                            x-model="searchQuery"
                            @keyup.debounce.300ms="filterTable()"
                            type="text"
                            placeholder="Search marriages..."
                            class="w-full sm:w-64 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                        />
                        <svg class="absolute right-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M9.16667 3.33334C5.94501 3.33334 3.33334 5.94501 3.33334 9.16668C3.33334 12.3883 5.94501 15 9.16667 15C12.3883 15 15 12.3883 15 9.16668C15 5.94501 12.3883 3.33334 9.16667 3.33334ZM1.66667 9.16668C1.66667 5.02455 5.02455 1.66668 9.16667 1.66668C13.3088 1.66668 16.6667 5.02455 16.6667 9.16668C16.6667 13.3088 13.3088 16.6667 9.16667 16.6667C5.02455 16.6667 1.66667 13.3088 1.66667 9.16668Z" fill="#9CA3AF"/>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M13.2858 13.2857C13.6112 12.9603 14.1388 12.9603 14.4642 13.2857L18.0892 16.9107C18.4147 17.2361 18.4147 17.7638 18.0892 18.0892C17.7638 18.4147 17.2362 18.4147 16.9108 18.0892L13.2858 14.4642C12.9603 14.1388 12.9603 13.6112 13.2858 13.2857Z" fill="#9CA3AF"/>
                        </svg>
                    </div>

                    <!-- Year Filter -->
                    <select 
                        x-model="yearFilter"
                        @change="filterTable()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                    >
                        <option value="">All Years</option>
                        @foreach(range(date('Y'), date('Y') - 10) as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>

                    <!-- Month Filter -->
                    <select 
                        x-model="monthFilter"
                        @change="filterTable()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                    >
                        <option value="">All Months</option>
                        @foreach([
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ] as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>

                    <!-- Status Filter -->
                    <select 
                        x-model="statusFilter"
                        @change="filterTable()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                    >
                        <option value="all">All Records</option>
                        <option value="pending">Pending Review</option>
                        <option value="completed">Completed</option>
                        <option value="verified">Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                    
                    <!-- Quick Stats -->
                    <div class="flex items-center gap-2 ml-4">
                        <span class="bg-green-50 text-green-700 text-theme-xs dark:bg-green-500/15 dark:text-green-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                            Verified: {{ $stats['verified'] ?? 0 }}
                        </span>
                        <span class="bg-yellow-50 text-yellow-700 text-theme-xs dark:bg-yellow-500/15 dark:text-yellow-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                            Pending: {{ $stats['pending'] ?? 0 }}
                        </span>
                        <span class="bg-blue-50 text-blue-700 text-theme-xs dark:bg-blue-500/15 dark:text-blue-400 inline-flex items-center gap-1 rounded-full px-2.5 py-1 font-medium">
                            Queue: {{ $stats['verification_queue'] ?? 0 }}
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <button
                        @click="exportData()"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
                    >
                        Export CSV
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 11.5V13.5C2 14.0523 2.44772 14.5 3 14.5H13C13.5523 14.5 14 14.0523 14 13.5V11.5M4.5 8L8 11.5M8 11.5L11.5 8M8 11.5V1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    
                    <button
                        @click="refreshData()"
                        class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
                    >
                        Refresh
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13.5 8C13.5 11.0376 11.0376 13.5 8 13.5C4.96243 13.5 2.5 11.0376 2.5 8C2.5 4.96243 4.96243 2.5 8 2.5C9.45844 2.5 10.7858 3.07778 11.7735 4.02314M13.5 8L11.7735 4.02314M13.5 8L10.5 8C10.2239 8 10 7.77614 10 7.5V4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[1400px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800/50">
                                <th class="px-5 py-3 text-left">
                                    <button @click="sortBy('certificate_serial')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Certificate
                                        <svg :class="{'rotate-180': sortColumn === 'certificate_serial' && sortDirection === 'desc'}" 
                                              class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </button>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <button @click="sortBy('marriage_date')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Date
                                        <svg :class="{'rotate-180': sortColumn === 'marriage_date' && sortDirection === 'desc'}" 
                                              class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </button>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <button @click="sortBy('year')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Year/Month
                                        <svg :class="{'rotate-180': sortColumn === 'year' && sortDirection === 'desc'}" 
                                              class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </button>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <button @click="sortBy('county')" class="flex items-center gap-1 font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        County/Type
                                        <svg :class="{'rotate-180': sortColumn === 'county' && sortDirection === 'desc'}" 
                                              class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </button>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Spouses & Parents
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Witnesses
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Status
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Completion
                                    </p>
                                </th>
                                <th class="px-5 py-3 text-left">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">
                                        Actions
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="filteredMarriages.length === 0">
                                <tr>
                                    <td colspan="9" class="px-5 py-8 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No marriage records found.</p>
                                            <p x-show="searchQuery || statusFilter !== 'all' || yearFilter || monthFilter" class="text-xs text-gray-400 dark:text-gray-500">
                                                Try adjusting your search or filter
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            
                            <template x-for="marriage in filteredMarriages" :key="marriage.id">
                                <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50">
                                    <!-- Certificate Serial -->
                                    <td class="px-5 py-4">
                                        <div>
                                            <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                <template x-if="marriage.certificate_serial">
                                                    <span x-text="marriage.certificate_serial" class="font-mono"></span>
                                                </template>
                                                <template x-if="!marriage.certificate_serial">
                                                    <span class="text-gray-400 italic">Not entered</span>
                                                </template>
                                            </span>
                                            <span class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                <template x-if="marriage.created_by">
                                                    Created by: <span x-text="marriage.created_by.name"></span>
                                                </template>
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Marriage Date -->
                                    <td class="px-5 py-4">
                                        <div class="space-y-1">
                                            <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                <template x-if="marriage.marriage_date">
                                                    <span x-text="formatDate(marriage.marriage_date)"></span>
                                                </template>
                                                <template x-if="!marriage.marriage_date">
                                                    <span class="text-gray-400 italic">Not set</span>
                                                </template>
                                            </p>
                                            <template x-if="marriage.reg_date">
                                                <p class="text-gray-400 text-theme-xs dark:text-gray-500">
                                                    Reg: <span x-text="formatDate(marriage.reg_date)"></span>
                                                </p>
                                            </template>
                                            <template x-if="marriage.venue">
                                                <p class="text-gray-400 text-theme-xs dark:text-gray-500">
                                                    <span x-text="marriage.venue"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </td>
                                    
                                    <!-- Year/Month -->
                                    <td class="px-5 py-4">
                                        <div class="space-y-1">
                                            <template x-if="marriage.year">
                                                <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                    <span x-text="marriage.year"></span>
                                                </p>
                                            </template>
                                            <template x-if="marriage.month">
                                                <p class="text-gray-400 text-theme-xs dark:text-gray-500">
                                                    <span x-text="getMonthName(marriage.month)"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </td>
                                    
                                    <!-- County & Marriage Type -->
                                    <td class="px-5 py-4">
                                        <div class="space-y-2">
                                            <template x-if="marriage.county">
                                                <div>
                                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                        <span x-text="marriage.county"></span>
                                                    </p>
                                                    <template x-if="marriage.sub_county">
                                                        <p class="text-gray-400 text-theme-xs dark:text-gray-500">
                                                            <span x-text="marriage.sub_county"></span>
                                                        </p>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="marriage.marriage_type">
                                                <span class="px-2 py-1 text-xs rounded-full bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400 inline-block font-medium"
                                                      x-text="marriage.marriage_type">
                                                </span>
                                            </template>
                                        </div>
                                    </td>
                                    
                                    <!-- Spouses & Parents -->
                                    <td class="px-5 py-4">
                                        <div class="space-y-3">
                                            <template x-if="marriage.spouses && marriage.spouses.length > 0">
                                                <div class="space-y-2">
                                                    <template x-for="spouse in marriage.spouses" :key="spouse.id">
                                                        <div class="space-y-1">
                                                            <div class="flex items-start gap-2">
                                                                <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300" 
                                                                          x-text="spouse.name.charAt(0).toUpperCase()"></span>
                                                                </div>
                                                                <div>
                                                                    <p class="text-gray-700 text-theme-xs font-medium dark:text-gray-300"
                                                                       x-text="spouse.name"></p>
                                                                    <template x-if="spouse.occupation">
                                                                        <p class="text-gray-500 text-theme-xs dark:text-gray-400"
                                                                           x-text="spouse.occupation"></p>
                                                                    </template>
                                                                    <template x-if="spouse.father_name || spouse.mother_name">
                                                                        <div class="mt-1">
                                                                            <template x-if="spouse.father_name">
                                                                                <p class="text-gray-400 text-theme-xs dark:text-gray-500">
                                                                                    Father: <span x-text="spouse.father_name"></span>
                                                                                    <template x-if="spouse.father_occupation">
                                                                                        (<span x-text="spouse.father_occupation"></span>)
                                                                                    </template>
                                                                                </p>
                                                                            </template>
                                                                            <template x-if="spouse.mother_name">
                                                                                <p class="text-gray-400 text-theme-xs dark:text-gray-500">
                                                                                    Mother: <span x-text="spouse.mother_name"></span>
                                                                                    <template x-if="spouse.mother_occupation">
                                                                                        (<span x-text="spouse.mother_occupation"></span>)
                                                                                    </template>
                                                                                </p>
                                                                            </template>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!marriage.spouses || marriage.spouses.length === 0">
                                                <p class="text-gray-400 italic text-theme-xs">No spouse information</p>
                                            </template>
                                        </div>
                                    </td>
                                    
                                    <!-- Witnesses -->
                                    <td class="px-5 py-4">
                                        <div class="space-y-2">
                                            <template x-if="marriage.witnesses && marriage.witnesses.length > 0">
                                                <div class="space-y-1">
                                                    <template x-for="witness in marriage.witnesses" :key="witness.id">
                                                        <div>
                                                            <p class="text-gray-700 text-theme-xs font-medium dark:text-gray-300"
                                                               x-text="witness.name"></p>
                                                            <template x-if="witness.side">
                                                                <p class="text-gray-400 text-theme-xs dark:text-gray-500">
                                                                    <span x-text="witness.side.charAt(0).toUpperCase() + witness.side.slice(1)"></span> side
                                                                </p>
                                                            </template>
                                                            <template x-if="witness.address">
                                                                <p class="text-gray-400 text-theme-xs dark:text-gray-500 truncate max-w-[150px]"
                                                                   x-text="witness.address"></p>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="!marriage.witnesses || marriage.witnesses.length === 0">
                                                <p class="text-gray-400 italic text-theme-xs">No witnesses</p>
                                            </template>
                                            <template x-if="marriage.witnesses && marriage.witnesses.length > 0">
                                                <div class="flex -space-x-2 mt-2">
                                                    <template x-for="(witness, index) in marriage.witnesses.slice(0, 4)" :key="witness.id">
                                                        <div class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900 bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-blue-600 dark:text-blue-300" 
                                                                  x-text="witness.name.charAt(0).toUpperCase()"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="marriage.witnesses.length > 4">
                                                        <div class="w-6 h-6 overflow-hidden border-2 border-white rounded-full dark:border-gray-900 bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-blue-600 dark:text-blue-300" 
                                                                  x-text="'+' + (marriage.witnesses.length - 4)"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                    
                                    <!-- Status -->
                                    <td class="px-5 py-4">
                                        <div class="space-y-2">
                                            <div class="flex flex-wrap gap-1">
                                                <!-- System Status -->
                                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                                      :class="{
                                                        'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400': marriage.system_status === 'Completed',
                                                        'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400': marriage.system_status === 'Pending',
                                                        'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400': marriage.system_status === 'Approved',
                                                        'bg-gray-50 text-gray-700 dark:bg-gray-500/15 dark:text-gray-400': !['Completed', 'Pending', 'Approved'].includes(marriage.system_status)
                                                      }"
                                                      x-text="marriage.system_status">
                                                </span>
                                                
                                                <!-- Verification Status -->
                                                <span class="px-2 py-1 text-xs rounded-full font-medium"
                                                      :class="{
                                                        'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400': marriage.verification_status === 'Verified',
                                                        'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/15 dark:text-yellow-400': marriage.verification_status === 'Unverified',
                                                        'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400': marriage.verification_status === 'Rejected'
                                                      }"
                                                      x-text="marriage.verification_status">
                                                </span>
                                            </div>
                                            
                                            <!-- Quick Verify Buttons -->
                                            <template x-if="marriage.system_status === 'Completed' && marriage.verification_status === 'Unverified'">
                                                <div class="flex gap-1 mt-2">
                                                    <button @click="quickVerify(marriage.id, 'Verified')"
                                                            class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 transition-colors w-full"
                                                            title="Mark as Verified">
                                                        ✓ Verify
                                                    </button>
                                                    <button @click="quickVerify(marriage.id, 'Rejected')"
                                                            class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition-colors w-full"
                                                            title="Reject Marriage">
                                                        ✗ Reject
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                    
                                    <!-- Completion -->
                                    <td class="px-5 py-4">
                                        <div>
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                    <div class="h-2 rounded-full transition-all duration-300"
                                                         :class="{
                                                            'bg-green-500': marriage.completion_rate >= 80,
                                                            'bg-yellow-500': marriage.completion_rate >= 50 && marriage.completion_rate < 80,
                                                            'bg-red-500': marriage.completion_rate < 50
                                                         }"
                                                         :style="'width: ' + marriage.completion_rate + '%'">
                                                    </div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap"
                                                      x-text="marriage.completion_rate + '%'">
                                                </span>
                                            </div>
                                            <div class="space-y-1">
                                                <p class="text-gray-500 text-theme-xs dark:text-gray-400">
                                                    <span x-text="marriage.spouses_count"></span> spouse(s)
                                                </p>
                                                <p class="text-gray-500 text-theme-xs dark:text-gray-400">
                                                    <span x-text="marriage.witnesses_count"></span> witness(es)
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Actions -->
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col gap-2">
                                            <a :href="'/marriages/' + marriage.id"
                                               class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03] justify-center">
                                                View Details
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            
                                            <template x-if="marriage.system_status === 'Completed' && marriage.verification_status === 'Unverified'">
                                                <a :href="'/marriages/' + marriage.id + '/edit?verify=true'"
                                                   class="inline-flex items-center gap-1 rounded-lg bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 shadow-theme-xs ring-1 ring-green-200 transition hover:bg-green-100 dark:bg-green-500/15 dark:text-green-400 dark:ring-green-500/20 justify-center">
                                                    Full Verify
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </a>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination/Info -->
                <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3 dark:border-gray-800">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Showing <span x-text="filteredMarriages.length"></span> of <span x-text="allMarriages.length"></span> records
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="prevPage()" :disabled="currentPage === 1"
                                :class="{'opacity-50 cursor-not-allowed': currentPage === 1}"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300"
                              x-text="currentPage"></span>
                        <button @click="nextPage()" :disabled="currentPage * pageSize >= filteredMarriages.length"
                                :class="{'opacity-50 cursor-not-allowed': currentPage * pageSize >= filteredMarriages.length}"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pre-process the data in PHP and pass it as JSON -->
@php
    $processedMarriages = $marriages->map(function($marriage) {
        // Calculate completion rate
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
        $totalFields += 2;
        $completedFields += min($marriage->spouses->count(), 2);

        // Witnesses (2 required)
        $totalFields += 2;
        $completedFields += min($marriage->witnesses->count(), 2);

        $completionRate = $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;

        // Get verification status name from category relationship
        $verificationStatus = $marriage->verificationStatus ? $marriage->verificationStatus->name : 'Unverified';

        // Process spouses with parent information
        $processedSpouses = $marriage->spouses->map(function($spouse) {
            return [
                'id' => $spouse->id,
                'name' => $spouse->name,
                'gender' => $spouse->gender,
                'age' => $spouse->age,
                'occupation' => $spouse->occupation,
                'residence' => $spouse->residence,
                'father_name' => $spouse->father_name,
                'father_occupation' => $spouse->father_occupation,
                'father_residence' => $spouse->father_residence,
                'mother_name' => $spouse->mother_name,
                'mother_occupation' => $spouse->mother_occupation,
                'mother_residence' => $spouse->mother_residence,
                'address' => $spouse->address,
            ];
        })->toArray();

        // Process witnesses
        $processedWitnesses = $marriage->witnesses->map(function($witness) {
            return [
                'id' => $witness->id,
                'name' => $witness->name,
                'side' => $witness->side,
                'address' => $witness->address,
                'id_type' => $witness->id_type,
                'id_number' => $witness->id_number,
            ];
        })->toArray();

        return [
            'id' => $marriage->id,
            'certificate_serial' => $marriage->certificate_serial,
            'marriage_date' => $marriage->marriage_date,
            'reg_date' => $marriage->reg_date,
            'venue' => $marriage->venue,
            'county' => $marriage->county,
            'sub_county' => $marriage->sub_county,
            'year' => $marriage->year,
            'month' => $marriage->month,
            'system_status' => $marriage->system_status,
            'verification_status' => $verificationStatus,
            'verification_status_id' => $marriage->verification_status_id,
            'created_by' => [
                'name' => $marriage->createdBy->name ?? 'System'
            ],
            'spouses' => $processedSpouses,
            'spouses_count' => $marriage->spouses->count(),
            'witnesses' => $processedWitnesses,
            'witnesses_count' => $marriage->witnesses->count(),
            'completion_rate' => $completionRate,
            'marriage_type' => $marriage->marriageType ? $marriage->marriageType->name : null
        ];
    });
@endphp

<script>
function marriageRegistrarTable() {
    return {
        // Data - Use the pre-processed PHP data
        allMarriages: @json($processedMarriages),
        filteredMarriages: [],
        searchQuery: '',
        yearFilter: '',
        monthFilter: '',
        statusFilter: 'all',
        sortColumn: 'marriage_date',
        sortDirection: 'desc',
        currentPage: 1,
        pageSize: 10,
        
        // Methods
        initTable() {
            this.filterTable();
        },
        
        filterTable() {
            let filtered = this.allMarriages;
            
            // Apply search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(marriage => 
                    (marriage.certificate_serial && marriage.certificate_serial.toLowerCase().includes(query)) ||
                    (marriage.venue && marriage.venue.toLowerCase().includes(query)) ||
                    (marriage.county && marriage.county.toLowerCase().includes(query)) ||
                    marriage.spouses.some(spouse => spouse.name.toLowerCase().includes(query)) ||
                    marriage.witnesses.some(witness => witness.name.toLowerCase().includes(query)) ||
                    marriage.spouses.some(spouse => 
                        (spouse.father_name && spouse.father_name.toLowerCase().includes(query)) ||
                        (spouse.mother_name && spouse.mother_name.toLowerCase().includes(query))
                    )
                );
            }
            
            // Apply year filter
            if (this.yearFilter) {
                filtered = filtered.filter(marriage => 
                    marriage.year && marriage.year.toString() === this.yearFilter
                );
            }
            
            // Apply month filter
            if (this.monthFilter) {
                filtered = filtered.filter(marriage => 
                    marriage.month && marriage.month.toString() === this.monthFilter
                );
            }
            
            // Apply status filter
            if (this.statusFilter !== 'all') {
                filtered = filtered.filter(marriage => {
                    switch(this.statusFilter) {
                        case 'pending':
                            return marriage.system_status === 'Pending';
                        case 'completed':
                            return marriage.system_status === 'Completed' && marriage.verification_status !== 'Unverified';
                        case 'verified':
                            return marriage.verification_status === 'Verified';
                        case 'unverified':
                            return marriage.verification_status === 'Unverified';
                        default:
                            return true;
                    }
                });
            }
            
            // Apply sorting
            filtered.sort((a, b) => {
                let aValue = a[this.sortColumn];
                let bValue = b[this.sortColumn];
                
                // Handle dates
                if (this.sortColumn === 'marriage_date') {
                    aValue = aValue ? new Date(aValue) : new Date(0);
                    bValue = bValue ? new Date(bValue) : new Date(0);
                }
                
                // Handle null values
                if (aValue === null || aValue === undefined) aValue = '';
                if (bValue === null || bValue === undefined) bValue = '';
                
                if (this.sortDirection === 'asc') {
                    return aValue > bValue ? 1 : -1;
                } else {
                    return aValue < bValue ? 1 : -1;
                }
            });
            
            this.filteredMarriages = filtered;
            this.currentPage = 1; // Reset to first page after filtering
        },
        
        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            this.filterTable();
        },
        
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        },
        
        getMonthName(monthNumber) {
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            return months[monthNumber - 1] || '';
        },
        
        quickVerify(marriageId, status) {
            if (!confirm(`Are you sure you want to mark this marriage as ${status}?`)) {
                return;
            }
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/marriages/${marriageId}`;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            form.appendChild(methodInput);
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'verification_status';
            statusInput.value = status;
            form.appendChild(statusInput);
            
            const quickVerifyInput = document.createElement('input');
            quickVerifyInput.type = 'hidden';
            quickVerifyInput.name = 'quick_verify';
            quickVerifyInput.value = 'true';
            form.appendChild(quickVerifyInput);
            
            document.body.appendChild(form);
            form.submit();
        },
        
        exportData() {
            // Convert filtered data to CSV with all new fields
            const headers = [
                'Certificate Serial', 'Marriage Date', 'Registration Date', 'Venue',
                'County', 'Sub County', 'Year', 'Month', 'System Status', 
                'Verification Status', 'Marriage Type', 'Spouses Count',
                'Witnesses Count', 'Completion Rate', 'Husband Name', 'Husband Occupation',
                'Husband Father', 'Husband Mother', 'Wife Name', 'Wife Occupation',
                'Wife Father', 'Wife Mother', 'Witnesses'
            ];
            
            const csvData = this.filteredMarriages.map(marriage => {
                // Get husband and wife info
                let husband = marriage.spouses.find(s => s.gender === 'male');
                let wife = marriage.spouses.find(s => s.gender === 'female');
                
                // Get witnesses names
                const witnesses = marriage.witnesses.map(w => w.name).join('; ');
                
                return [
                    marriage.certificate_serial || '',
                    marriage.marriage_date ? this.formatDate(marriage.marriage_date) : '',
                    marriage.reg_date ? this.formatDate(marriage.reg_date) : '',
                    marriage.venue || '',
                    marriage.county || '',
                    marriage.sub_county || '',
                    marriage.year || '',
                    marriage.month ? this.getMonthName(marriage.month) : '',
                    marriage.system_status,
                    marriage.verification_status,
                    marriage.marriage_type || '',
                    marriage.spouses_count,
                    marriage.witnesses_count,
                    marriage.completion_rate + '%',
                    husband ? husband.name : '',
                    husband ? husband.occupation || '' : '',
                    husband ? husband.father_name || '' : '',
                    husband ? husband.mother_name || '' : '',
                    wife ? wife.name : '',
                    wife ? wife.occupation || '' : '',
                    wife ? wife.father_name || '' : '',
                    wife ? wife.mother_name || '' : '',
                    witnesses
                ];
            });
            
            const csvContent = [
                headers.join(','),
                ...csvData.map(row => row.map(cell => `"${cell}"`).join(','))
            ].join('\n');
            
            // Download CSV
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `marriages_${new Date().toISOString().split('T')[0]}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        
        refreshData() {
            window.location.reload();
        },
        
        nextPage() {
            if (this.currentPage * this.pageSize < this.filteredMarriages.length) {
                this.currentPage++;
            }
        },
        
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        }
    };
}
</script>
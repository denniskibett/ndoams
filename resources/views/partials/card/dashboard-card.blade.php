<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12">
        <!-- Dashboard Metric Cards - 5 Cards Layout -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-5 gap-4 md:gap-6">
            
            <!-- Total Marriages Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total Marriages
                </p>
                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['total_marriages'] ?? 0 }}
                        </h4>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                            {{ $cardData['today_marriages'] ?? 0 }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            today
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total PDF Files Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    PDF Files
                </p>
                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['total_pdfs'] ?? 0 }}
                        </h4>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                            {{ $cardData['today_pdfs'] ?? 0 }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            today
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Pages Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total PDF Pages
                </p>
                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['total_pages'] ?? 0 }}
                        </h4>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-600 dark:bg-purple-500/15 dark:text-purple-500">
                            {{ $cardData['today_pages'] ?? 0 }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            today
                        </span>
                    </div>
                </div>
            </div>

            <!-- Storage Used Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Storage Used
                </p>
                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['storage_used'] ?? '0 MB' }}
                        </h4>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $cardData['avg_pdf_size'] ?? 0 }} MB avg
                        </span>
                    </div>
                </div>
            </div>

            <!-- Completion Rate Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Completion Rate
                </p>
                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['completion_rate'] ?? 0 }}%
                        </h4>
                    </div>
                    <div class="flex items-center gap-1">
                        @php
                            // Use raw values (no commas)
                            $totalPagesRaw = $cardData['total_pages_raw'] ?? 0;
                            $pagesWithMarriagesRaw = $cardData['pages_with_marriages_raw'] ?? 0;
                            
                            $linkedPercentage = $totalPagesRaw > 0 
                                ? round(($pagesWithMarriagesRaw / $totalPagesRaw) * 100, 1)
                                : 0;
                        @endphp
                        <span class="flex items-center gap-1 rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-500/15 dark:text-orange-500">
                            {{ $linkedPercentage }}%
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            linked
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
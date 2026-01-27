<div class="grid grid-cols-12 gap-4 md:gap-6">
    <div class="col-span-12">
        <!-- Metric Cards Group - Adjusted breakpoints -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-5 gap-4 md:gap-6">
            <!-- Total PDFs Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Total PDFs
                </p>

                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['total_records'] }}
                        </h4>
                    </div>

                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                            {{ $cardData['today_records'] }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            today
                        </span>
                    </div>
                </div>
            </div>

            <!-- Data Clerks Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Data Clerks
                </p>

                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['data_clerks'] }}
                        </h4>
                    </div>

                    <div class="flex items-center gap-1">
                        <span class="flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                            Active
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
                            {{ $cardData['storage_used_mb'] }} MB
                        </h4>
                    </div>

                    <div class="flex items-center gap-1">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Total
                        </span>
                    </div>
                </div>
            </div>

            <!-- Linked PDFs Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Linked PDFs
                </p>

                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['linked_pdfs'] }}
                        </h4>
                    </div>

                    <div class="flex items-center gap-1">
                        @php
                            $linkedPercentage = $cardData['total_pdfs'] > 0 
                                ? round(($cardData['linked_pdfs'] / $cardData['total_pdfs']) * 100, 1)
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

            <!-- Completion Rate Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Completion Rate
                </p>

                <div class="mt-3 flex items-end justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90">
                            {{ $cardData['completion_rate'] }}%
                        </h4>
                    </div>

                    <div class="flex items-center gap-1">
                        @php
                            $completionChange = 0; // Default value
                            if (isset($cardData['previous_completion_rate'])) {
                                $completionChange = $cardData['completion_rate'] - $cardData['previous_completion_rate'];
                            }
                            $isPositive = $completionChange >= 0;
                        @endphp
                        <span class="flex items-center gap-1 rounded-full {{ $isPositive ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-500' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-500' }} px-2 py-0.5 text-xs font-medium">
                            {{ $isPositive ? '+' : '' }}{{ number_format($completionChange, 1) }}%
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            vs last
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
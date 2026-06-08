{{-- resources/views/partials/card/unified-dashboard-cards.blade.php --}}
@php
    $user = Auth::user();
    $roleName = $user->role->name ?? 'user';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6 mb-6">
    
    {{-- ===== DATA CLERK CARDS ===== --}}
    @if($roleName === 'data_clerk')
        <!-- My Assigned Pages -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Assigned to Me</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['my_assigned'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                    {{ number_format($cardData['my_assigned_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">new today</span>
            </div>
        </div>

        <!-- My Completed Pages -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['my_completed'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                    {{ number_format($cardData['my_completed_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">today</span>
            </div>
        </div>

        <!-- My Pending Pages -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">In Progress</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['my_pending'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Need completion</span>
            </div>
        </div>

        <!-- My Progress -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">My Progress</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['my_progress'] ?? 0) }}%
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-2 rounded-full bg-purple-600" style="width: {{ $cardData['my_progress'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- My Marriages Entered -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Marriages Entered</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_marriages'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-500">
                    {{ number_format($cardData['marriages_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">entered today</span>
            </div>
        </div>
    @endif

    {{-- ===== MARRIAGE TELLER CARDS ===== --}}
    @if($roleName === 'marriage_teller')
        <!-- Team Total -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Team Total</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['team_total'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                    {{ number_format($cardData['team_new_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">new today</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 ml-auto">{{ $cardData['active_clerks'] ?? 0 }} clerks</span>
            </div>
        </div>

        <!-- Team Completed -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['team_completed'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                    {{ number_format($cardData['team_completed_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">completed today</span>
            </div>
        </div>

        <!-- Team In Progress -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">In Progress</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['team_pending'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Currently being worked on</span>
            </div>
        </div>

        <!-- Team Progress -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Team Progress</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['completion_rate'] ?? 0) }}%
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-2 rounded-full bg-purple-600" style="width: {{ $cardData['completion_rate'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Active Clerks -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Clerks</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['active_clerks'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Working under you</span>
            </div>
        </div>
    @endif

    {{-- ===== REGISTRAR CARDS ===== --}}
    @if($roleName === 'marriage_registrar')
        <!-- Verification Queue -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Verification Queue</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['verification_queue'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-yellow-50 px-2 py-0.5 text-xs font-medium text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-500">
                    {{ number_format($cardData['queue_new_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">new today</span>
            </div>
        </div>

        <!-- Verified Today -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Verified Today</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['verified_today'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Completed today</span>
            </div>
        </div>

        <!-- Total Verified -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Verified</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['verified_total'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">All time verified records</span>
            </div>
        </div>

        <!-- Pending Publication -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pending Publication</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['pending_verification'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900">
                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Ready to publish</span>
            </div>
        </div>

        <!-- Total Marriages -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Marriages</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_marriages'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-purple-50 px-2 py-0.5 text-xs font-medium text-purple-600 dark:bg-purple-500/15 dark:text-purple-500">
                    {{ number_format($cardData['marriages_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">new today</span>
            </div>
        </div>
    @endif

    {{-- ===== ADMIN CARDS ===== --}}
    @if($roleName === 'admin')
        <!-- Total PDFs -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total PDFs</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_pdfs'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                    {{ number_format($cardData['pdfs_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">uploaded today</span>
            </div>
        </div>

        <!-- Storage Used -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Storage Used</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ $cardData['storage_formatted'] ?? '0 MB' }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-cyan-100 dark:bg-cyan-900">
                    <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Total storage used</span>
            </div>
        </div>

        <!-- Total Marriages -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Marriages</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_marriages'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-500">
                    {{ number_format($cardData['marriages_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">new today</span>
            </div>
        </div>

        <!-- Published Documents -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Published</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['published_total'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600 dark:bg-green-500/15 dark:text-green-500">
                    {{ number_format($cardData['published_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">published today</span>
            </div>
        </div>

        <!-- Active Staff -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Staff</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format(($cardData['active_clerks'] ?? 0) + ($cardData['active_tellers'] ?? 0)) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900">
                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ number_format($cardData['active_clerks'] ?? 0) }} clerks, {{ number_format($cardData['active_tellers'] ?? 0) }} tellers
                </span>
            </div>
        </div>
    @endif

    {{-- ===== ATTORNEY GENERAL CARDS ===== --}}
    @if($roleName === 'attorney_general')
        <!-- Total Published PDFs -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Published PDFs</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($roleCharts['total_published_pdfs'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                    {{ number_format($cardData['published_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">published today</span>
            </div>
        </div>

        <!-- Total Published Pages -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Published Pages</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($roleCharts['total_published_pages'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-cyan-100 dark:bg-cyan-900">
                    <svg class="h-6 w-6 text-cyan-600 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-cyan-50 px-2 py-0.5 text-xs font-medium text-cyan-600 dark:bg-cyan-500/15 dark:text-cyan-500">
                    {{ number_format($cardData['pages_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">published today</span>
            </div>
        </div>

        <!-- Counties Covered -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Counties Covered</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ count($roleCharts['counties_vs_pages'] ?? []) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900">
                    <svg class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">counties with published records</span>
            </div>
        </div>

        <!-- Marriage Types -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Marriage Types</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ count($roleCharts['marriage_types_distribution'] ?? []) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">distinct marriage types</span>
            </div>
        </div>

        <!-- Total Marriages -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Marriages</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['total_marriages'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900">
                    <svg class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-600 dark:bg-rose-500/15 dark:text-rose-500">
                    {{ number_format($cardData['marriages_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">new today</span>
            </div>
        </div>
    @endif

    {{-- ===== DEFAULT USER CARDS ===== --}}
    @if(!in_array($roleName, ['data_clerk', 'marriage_teller', 'marriage_registrar', 'attorney_general', 'admin']))
        <!-- Available Records -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Available Records</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">
                        {{ number_format($cardData['published_total'] ?? 0) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-500/15 dark:text-blue-500">
                    {{ number_format($cardData['published_today'] ?? 0) }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">new today</span>
            </div>
        </div>
    @endif

</div>
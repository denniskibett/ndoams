<!-- resources/views/partials/card/marriages-card.blade.php -->
<script>
// Simple role-based card permissions
window.dashboardPermissions = {
    'data_clerk': ['my_uploads', 'my_completed', 'my_pending', 'progress', 'total_assigned'],
    'marriage_teller': ['team_total', 'active_clerks', 'data_clerk_images', 'pending_review', 'team_completed'],
    'marriage_registrar': ['verification_queue', 'verified_today', 'total_verified', 'rejection_rate', 'pending_verification'],
    'admin': ['team_total', 'verified', 'pending', 'rejected', 'total_images'],
    'user': ['total_records', 'verified', 'pending', 'rejected', 'completion_rate']
};
</script>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6"
     x-data="{
        userRole: '{{ auth()->user()->role->name }}',
        hasPermission(card) {
            const permissions = window.dashboardPermissions[this.userRole] || [];
            return permissions.includes(card);
        }
     }">
    
    <!-- Data Clerk Cards (5 cards) -->
    <div x-show="hasPermission('my_uploads')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">My Uploads</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['my_uploads'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">This month</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('my_completed')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Completed</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['my_completed'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Ready for review</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('my_pending')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Pending</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['my_pending'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Need basic info</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('progress')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Progress</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['progress'] ?? '0%' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Monthly target</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('total_assigned')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Assigned</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_assigned'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total assigned</p>
            </div>
        </div>
    </div>

    <!-- Marriage Teller Cards (5 cards) -->
    <div x-show="hasPermission('team_total')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Total Records</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['team_total'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">All marriages</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('active_clerks')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900">
                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Data Clerks</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_clerks'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total clerks</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('data_clerk_images')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Clerk Images</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['data_clerk_images'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Uploaded by clerks</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('pending_review')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Pending Review</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending_review'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Awaiting details</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('team_completed')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Completed</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['team_completed'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Ready for verification</p>
            </div>
        </div>
    </div>

    <!-- Admin/Registrar Cards (5 cards) -->
    <div x-show="hasPermission('verification_queue')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Queue</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['verification_queue'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Awaiting approval</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('verified_today')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Today</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['verified_today'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Verified today</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('total_verified')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Total Verified</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_verified'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">By you</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('rejection_rate')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Rejection Rate</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['rejection_rate'] ?? '0%' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Quality control</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('pending_verification')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Pending</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Awaiting action</p>
            </div>
        </div>
    </div>

    <!-- Admin Cards (5 cards) -->

    <div x-show="hasPermission('verified')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Verified</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['verified'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Approved records</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('pending')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Pending</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Awaiting action</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('rejected')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Rejected</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['rejected'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Failed verification</p>
            </div>
        </div>
    </div>

    <div x-show="hasPermission('total_images')" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Total Images</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_images'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">All images</p>
            </div>
        </div>
    </div>
</div>
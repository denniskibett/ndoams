<div class="grid grid-cols-1 gap-6 mt-6 mb-8">

    <!-- Users by Role & Activity Timeline -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Users by Role - Vertical Bar Chart -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Users by Role</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">System user distribution</p>
                </div>
                <div class="flex items-center gap-3">
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="exportUsersByRoleChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="exportUsersByRoleChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                            <button onclick="exportUsersByRoleChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="usersByRoleChart" 
                         data-roles='@json($roleCharts['users_by_role'] ?? [])'
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Activity Timeline - Area Chart with Filter -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Activity Timeline</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">System activity over time</p>
                </div>
                <div class="flex items-center gap-3">
                    <select id="activity-timeline-filter" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800">
                        <option value="7">Last 7 Days</option>
                        <option value="14" selected>Last 14 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="exportActivityTimelineChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="exportActivityTimelineChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                            <button onclick="exportActivityTimelineChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="activityTimelineChart" 
                         data-activity='@json($roleCharts['activity_timeline'] ?? [])'
                         data-current-days="14"
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Trends (6 months) - Dual Axis Chart with Filter -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Upload Trends</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">System usage over time</p>
            </div>
            <div class="flex items-center gap-3">
                <select id="upload-trends-filter" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800">
                    <option value="3">Last 3 Months</option>
                    <option value="6" selected>Last 6 Months</option>
                    <option value="12">Last 12 Months</option>
                    <option value="24">Last 24 Months</option>
                </select>
                <div x-data="{openDropDown: false}" class="relative h-fit">
                    <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                        </svg>
                    </button>
                    <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                        <button onclick="exportUploadTrendsChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                        <button onclick="exportUploadTrendsChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                        <button onclick="exportUploadTrendsChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                <div id="uploadTrendsChart" 
                     data-trends='@json($roleCharts['upload_trends'] ?? [])'
                     data-current-months="6"
                     class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                     style="height: 420px;"></div>
            </div>
        </div>
    </div>

    <!-- Top Performing Clerks - Table & Vertical Bar Chart Side by Side -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Top Performing Clerks Table -->
        <div class="lg:col-span-3">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Performing Clerks</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Highest completion rates</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div x-data="{openDropDown: false}" class="relative h-fit">
                            <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                                </svg>
                            </button>
                            <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                                <button onclick="exportTopClerksTable()" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar mt-4">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="top-clerks-table">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-800">
                                    <th class="py-3 text-left text-sm font-semibold text-gray-800">Name</th>
                                    <th class="py-3 text-right text-sm font-semibold text-gray-800">Assigned</th>
                                    <th class="py-3 text-right text-sm font-semibold text-gray-800">Completed</th>
                                    <th class="py-3 text-right text-sm font-semibold text-gray-800">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roleCharts['top_clerks'] ?? [] as $clerk)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-3 text-sm text-gray-600">{{ $clerk['name'] }}</td>
                                    <td class="py-3 text-right text-sm text-gray-600">{{ number_format($clerk['assigned']) }}</td>
                                    <td class="py-3 text-right text-sm text-gray-600">{{ number_format($clerk['completed']) }}</td>
                                    <td class="py-3 text-right text-sm font-semibold text-gray-800">{{ $clerk['rate'] }}%</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-3 text-center text-gray-500">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>    

        <!-- Top Clerks Chart - VERTICAL Bar Chart (exactly like Marriage Teller) -->
        <div class="lg:col-span-9">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Clerk Performance Chart</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Completion rate by clerk (vertical bars)</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div x-data="{openDropDown: false}" class="relative h-fit">
                            <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                                <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                                </svg>
                            </button>
                            <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                                <button onclick="exportTopClerksChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                                <button onclick="exportTopClerksChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                                <button onclick="exportTopClerksChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                        <div id="topClerksChart" 
                            data-names='@json(collect($roleCharts['top_clerks'] ?? [])->pluck('name')->toArray())'
                            data-rates='@json(collect($roleCharts['top_clerks'] ?? [])->pluck('rate')->toArray())'
                            class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                            style="height: 400px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle Activity Timeline filter change
document.getElementById('activity-timeline-filter')?.addEventListener('change', async function(e) {
    const days = e.target.value;
    const chartContainer = document.getElementById('activityTimelineChart');
    
    if (window.activityTimelineChart) {
        chartContainer.style.opacity = '0.5';
    }
    
    try {
        const response = await fetch(`/dashboard/admin/activity-timeline?days=${days}`);
        const data = await response.json();
        
        if (data.success && window.activityTimelineChart) {
            if (window.activityTimelineChart.updateChartData) {
                window.activityTimelineChart.updateChartData(data.labels, data.uploads, data.marriages);
            } else if (window.activityTimelineChart.updateOptions) {
                window.activityTimelineChart.updateOptions({
                    xaxis: { categories: data.labels },
                    series: [
                        { data: data.uploads },
                        { data: data.marriages }
                    ]
                });
            }
            chartContainer.setAttribute('data-activity', JSON.stringify(data.activityData));
            chartContainer.setAttribute('data-current-days', days);
        }
    } catch (error) {
        console.error('Error fetching activity timeline data:', error);
    } finally {
        if (window.activityTimelineChart) {
            setTimeout(() => { chartContainer.style.opacity = '1'; }, 200);
        }
    }
});

// Handle Upload Trends filter change
document.getElementById('upload-trends-filter')?.addEventListener('change', async function(e) {
    const months = e.target.value;
    const chartContainer = document.getElementById('uploadTrendsChart');
    
    if (window.uploadTrendsChart) {
        chartContainer.style.opacity = '0.5';
    }
    
    try {
        const response = await fetch(`/dashboard/admin/upload-trends?months=${months}`);
        const data = await response.json();
        
        if (data.success && window.uploadTrendsChart) {
            if (window.uploadTrendsChart.updateChartData) {
                window.uploadTrendsChart.updateChartData(data.months, data.uploads, data.storage);
            } else if (window.uploadTrendsChart.updateOptions) {
                window.uploadTrendsChart.updateOptions({
                    xaxis: { categories: data.months },
                    series: [
                        { data: data.uploads },
                        { data: data.storage }
                    ]
                });
            }
            chartContainer.setAttribute('data-trends', JSON.stringify(data.trendsData));
            chartContainer.setAttribute('data-current-months', months);
        }
    } catch (error) {
        console.error('Error fetching upload trends data:', error);
    } finally {
        if (window.uploadTrendsChart) {
            setTimeout(() => { chartContainer.style.opacity = '1'; }, 200);
        }
    }
});
</script>
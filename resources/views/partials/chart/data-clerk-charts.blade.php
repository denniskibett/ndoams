
<div class="grid grid-cols-1 gap-6 mt-6 mb-8">    
    <!-- Completion Rate & Status Distribution - 2 columns row -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Completion Rate Donut Chart -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Completion Rate</h3>
            </div>
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="completionRateChart" 
                         data-rate="{{ $roleCharts['completion_rate'] ?? 0 }}"
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 320px;"></div>
                </div>
            </div>
        </div>

        <!-- Status Distribution Bar Chart -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Status Distribution</h3>
                
                <div class="flex items-center gap-3">
                    {{-- FILTER DROPDOWN for Status Distribution --}}
                    <select
                        id="status-interval-filter"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800"
                    >
                        <option value="all">All Time</option>
                        <option value="7days">Last 7 Days</option>
                        <option value="30days">Last 30 Days</option>
                        <option value="90days">Last 90 Days</option>
                    </select>

                    {{-- Ellipsis Menu --}}
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button
                            @click="openDropDown = !openDropDown"
                            :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'"
                            class="transition-colors"
                        >
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                            </svg>
                        </button>

                        <div
                            x-show="openDropDown"
                            @click.outside="openDropDown = false"
                            x-cloak
                            class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                        >
                            <button onclick="exportStatusChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Save as PNG
                            </button>
                            <button onclick="exportStatusChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Save as SVG
                            </button>
                            <button onclick="exportStatusChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Export CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="statusDistributionChart" 
                         data-statuses='@json(array_keys($roleCharts['status_distribution'] ?? []))'
                         data-values='@json(array_values($roleCharts['status_distribution'] ?? []))'
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Productivity Chart (with filters and download options) -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Daily Productivity</h3>

            <div class="flex items-center gap-3">
                {{-- FILTER DROPDOWN for Daily Productivity --}}
                <select
                    id="productivity-interval-filter"
                    class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800"
                >
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>

                {{-- Ellipsis Menu --}}
                <div x-data="{openDropDown: false}" class="relative h-fit">
                    <button
                        @click="openDropDown = !openDropDown"
                        :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'"
                        class="transition-colors"
                    >
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                        </svg>
                    </button>

                    <div
                        x-show="openDropDown"
                        @click.outside="openDropDown = false"
                        x-cloak
                        class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark"
                    >
                        <button onclick="exportProductivityChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                            Save as PNG
                        </button>
                        <button onclick="exportProductivityChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                            Save as SVG
                        </button>
                        <button onclick="exportProductivityChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                            Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                <div id="dailyProductivityChart" 
                     data-labels='@json($roleCharts['daily_productivity']->pluck('label') ?? [])'
                     data-counts='@json($roleCharts['daily_productivity']->pluck('count') ?? [])'
                     data-current-filter="daily"
                     class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                     style="height: 380px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Format status names for better display
const formatStatusName = (status) => {
    const statusMap = {
        'pending': 'Pending Review',
        'in_progress': 'In Progress',
        'review_needed': 'Needs Review',
        'completed': 'Completed',
        'assigned': 'Assigned',
        'skipped': 'Skipped'
    };
    return statusMap[status] || status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Handle Status Distribution filter change
document.getElementById('status-interval-filter')?.addEventListener('change', async function(e) {
    const interval = e.target.value;
    const chartContainer = document.getElementById('statusDistributionChart');
    
    if (window.statusChart) {
        chartContainer.style.opacity = '0.5';
    }
    
    try {
        const response = await fetch(`/dashboard/data-clerk/status-distribution?interval=${interval}`);
        const data = await response.json();
        
        if (data.success) {
            if (window.statusChart && window.statusChart.updateChartData) {
                // Format status names for display
                const formattedStatuses = data.statuses.map(s => formatStatusName(s));
                window.statusChart.updateChartData(formattedStatuses, data.values);
                
                chartContainer.setAttribute('data-statuses', JSON.stringify(data.statuses));
                chartContainer.setAttribute('data-values', JSON.stringify(data.values));
            }
        }
    } catch (error) {
        console.error('Error fetching status data:', error);
    } finally {
        if (window.statusChart) {
            setTimeout(() => { chartContainer.style.opacity = '1'; }, 200);
        }
    }
});

// Handle Daily Productivity filter change (Daily, Weekly, Monthly, Yearly)
document.getElementById('productivity-interval-filter')?.addEventListener('change', async function(e) {
    const interval = e.target.value;
    const chartContainer = document.getElementById('dailyProductivityChart');
    
    if (window.productivityChart) {
        chartContainer.style.opacity = '0.5';
    }
    
    try {
        const response = await fetch(`/dashboard/data-clerk/daily-productivity?interval=${interval}`);
        const data = await response.json();
        
        if (data.success) {
            if (window.productivityChart && window.productivityChart.updateChartData) {
                window.productivityChart.updateChartData(data.labels, data.counts);
                
                chartContainer.setAttribute('data-labels', JSON.stringify(data.labels));
                chartContainer.setAttribute('data-counts', JSON.stringify(data.counts));
                chartContainer.setAttribute('data-current-filter', interval);
            }
        }
    } catch (error) {
        console.error('Error fetching productivity data:', error);
    } finally {
        if (window.productivityChart) {
            setTimeout(() => { chartContainer.style.opacity = '1'; }, 200);
        }
    }
});

// Export functions for Status Chart
window.exportStatusChart = function(type) {
    if (!window.statusChart) {
        console.error("Status chart not initialized");
        return;
    }

    const chartElement = document.querySelector("#statusDistributionChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.statusChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `status-distribution.${type}`;
            a.click();
        }).catch(error => console.error("Error exporting chart:", error));
    }

    if (type === "csv") {
        try {
            const statuses = JSON.parse(chartElement.dataset.statuses || "[]");
            const values = JSON.parse(chartElement.dataset.values || "[]");
            
            let csv = "Status,Pages\n";
            statuses.forEach((status, i) => {
                csv += `${formatStatusName(status)},${values[i]}\n`;
            });
            
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "status-distribution.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
        }
    }
};

// Export functions for Productivity Chart
window.exportProductivityChart = function(type) {
    if (!window.productivityChart) {
        console.error("Productivity chart not initialized");
        return;
    }

    const chartElement = document.querySelector("#dailyProductivityChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.productivityChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `daily-productivity.${type}`;
            a.click();
        }).catch(error => console.error("Error exporting chart:", error));
    }

    if (type === "csv") {
        try {
            const labels = JSON.parse(chartElement.dataset.labels || "[]");
            const counts = JSON.parse(chartElement.dataset.counts || "[]");
            
            let csv = "Date,Pages Completed\n";
            labels.forEach((label, i) => {
                csv += `${label},${counts[i]}\n`;
            });
            
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "daily-productivity.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
        }
    }
};
</script>
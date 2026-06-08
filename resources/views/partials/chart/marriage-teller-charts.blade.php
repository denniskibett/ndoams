@if(isset($roleCharts['no_data']) && $roleCharts['no_data'])
    <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center dark:border-gray-800 dark:bg-white/[0.03]">
        <p class="text-gray-500">No clerks assigned yet. Please contact administrator.</p>
    </div>
@else
<div class="grid grid-cols-1 gap-6 mt-6 mb-8">

    <!-- Clerk Performance & Team Weekly Trend - 2 columns row -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Clerk Performance Horizontal Bar Chart -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Clerk Performance</h3>
                
                <div class="flex items-center gap-3">
                    {{-- Ellipsis Menu for Clerk Performance --}}
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
                            <button onclick="exportClerkChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Save as PNG
                            </button>
                            <button onclick="exportClerkChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Save as SVG
                            </button>
                            <button onclick="exportClerkChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Export CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="clerkPerformanceChart" 
                         data-clerks='@json($roleCharts['clerks'] ?? [])'
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 400px;"></div>
                </div>
            </div>
        </div>

        <!-- Weekly Team Trend Line Chart -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Team Weekly Trend</h3>
                
                <div class="flex items-center gap-3">
                    {{-- FILTER DROPDOWN for Weekly Trend --}}
                    <select
                        id="weekly-trend-filter"
                        class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800"
                    >
                        <option value="4">Last 4 Weeks</option>
                        <option value="8">Last 8 Weeks</option>
                        <option value="12">Last 12 Weeks</option>
                        <option value="24">Last 24 Weeks</option>
                    </select>

                    {{-- Ellipsis Menu for Weekly Trend --}}
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
                            <button onclick="exportTrendChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Save as PNG
                            </button>
                            <button onclick="exportTrendChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Save as SVG
                            </button>
                            <button onclick="exportTrendChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                                Export CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="weeklyTrendChart" 
                         data-trend='@json($roleCharts['weekly_trend'] ?? [])'
                         data-current-weeks="4"
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle Weekly Trend filter change with AJAX
document.getElementById('weekly-trend-filter')?.addEventListener('change', async function(e) {
    const weeks = e.target.value;
    const chartContainer = document.getElementById('weeklyTrendChart');
    
    // Show loading state
    if (window.weeklyTrendChart) {
        chartContainer.style.opacity = '0.5';
    }
    
    try {
        // Fetch new chart data
        const response = await fetch(`/dashboard/marriage-teller/weekly-trend?weeks=${weeks}`);
        const data = await response.json();
        
        if (data.success && data.weeks && data.counts) {
            // Update the chart with new data
            if (window.weeklyTrendChart && window.weeklyTrendChart.updateOptions) {
                const hasData = data.weeks && data.weeks.length > 0;
                const xCats = hasData ? data.weeks : ['No Data'];
                const yData = hasData ? data.counts : [0];
                
                window.weeklyTrendChart.updateOptions({
                    xaxis: { categories: xCats },
                    series: [{ data: yData }]
                });
                
                // Update markers based on data presence
                window.weeklyTrendChart.updateOptions({
                    markers: { size: hasData ? 6 : 0 },
                    dataLabels: { enabled: hasData }
                });
                
                // Update the data attributes for export functionality
                const trendData = data.trendData || [];
                chartContainer.setAttribute('data-trend', JSON.stringify(trendData));
                chartContainer.setAttribute('data-current-weeks', weeks);
            }
        }
    } catch (error) {
        console.error('Error fetching weekly trend data:', error);
    } finally {
        // Remove loading state
        if (window.weeklyTrendChart) {
            setTimeout(() => {
                chartContainer.style.opacity = '1';
            }, 200);
        }
    }
});

// Export functions for Clerk Performance Chart
window.exportClerkChart = function(type) {
    if (!window.clerkPerformanceChart) {
        console.error("Clerk performance chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#clerkPerformanceChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.clerkPerformanceChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `clerk-performance.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const clerks = JSON.parse(chartElement.dataset.clerks || "[]");
            let csv = "Clerk Name,Assigned Pages,Completed Pages,Review Needed,Completion Rate (%)\n";
            
            if (clerks.length) {
                clerks.forEach((clerk) => {
                    csv += `"${clerk.name}",${clerk.assigned},${clerk.completed},${clerk.review_needed || 0},${clerk.completion_rate}\n`;
                });
            } else {
                csv += '"No Data",0,0,0,0\n';
            }
            
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "clerk-performance.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};

// Export functions for Weekly Trend Chart
window.exportTrendChart = function(type) {
    if (!window.weeklyTrendChart) {
        console.error("Weekly trend chart not initialized");
        alert("Chart not ready yet. Please wait a moment.");
        return;
    }

    const chartElement = document.querySelector("#weeklyTrendChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.weeklyTrendChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            a.href = type === "png" ? imgURI : svgURI;
            a.download = `weekly-trend.${type}`;
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
            alert("Error exporting chart. Please try again.");
        });
    }

    if (type === "csv") {
        try {
            const trend = JSON.parse(chartElement.dataset.trend || "[]");
            let csv = "Week,Pages Completed\n";
            
            if (trend.length) {
                trend.forEach((item) => {
                    csv += `${item.week},${item.count}\n`;
                });
            } else {
                csv += "No Data,0\n";
            }
            
            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "weekly-trend.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
            alert("Error exporting CSV. Please try again.");
        }
    }
};
</script>
@endif
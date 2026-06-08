<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6"
>
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            PDF Pages Uploaded
        </h3>

        <div class="flex items-center gap-3">
            {{-- FILTER DROPDOWN --}}
            <select
                id="interval-filter"
                class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800"
            >
                <option value="daily" {{ ($chartData['interval'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ ($chartData['interval'] ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ ($chartData['interval'] ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
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
                    <button onclick="exportChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                        Save as PNG
                    </button>
                    <button onclick="exportChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                        Save as SVG
                    </button>
                    <button onclick="exportChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                        Export CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
            <div
                id="uploadsBarChart"
                data-dates='@json($chartData["dates"] ?? [])'
                data-counts='@json($chartData["counts"] ?? [])'
                data-interval='{{ $chartData["interval"] ?? "daily" }}'
                class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
            ></div>
        </div>
    </div>
</div>

<script>
// Handle interval change without page reload
document.getElementById('interval-filter')?.addEventListener('change', async function(e) {
    const interval = e.target.value;
    const chartContainer = document.getElementById('uploadsBarChart');
    
    // Show loading state
    if (window.uploadChart) {
        chartContainer.style.opacity = '0.5';
    }
    
    try {
        // Fetch new chart data
        const response = await fetch(`/dashboard/chart-data?interval=${interval}`);
        const data = await response.json();
        
        if (data.success && data.chartData) {
            // Update the chart with new data
            if (window.uploadChart && window.uploadChart.updateChartData) {
                window.uploadChart.updateChartData(data.chartData.dates, data.chartData.counts);
                
                // Update the data attributes for export functionality
                chartContainer.setAttribute('data-dates', JSON.stringify(data.chartData.dates));
                chartContainer.setAttribute('data-counts', JSON.stringify(data.chartData.counts));
                chartContainer.setAttribute('data-interval', data.chartData.interval);
            }
        }
    } catch (error) {
        console.error('Error fetching chart data:', error);
    } finally {
        // Remove loading state
        if (window.uploadChart) {
            setTimeout(() => {
                chartContainer.style.opacity = '1';
            }, 200);
        }
    }
});

// Export chart function
window.exportChart = function(type) {
    if (!window.uploadChart) {
        console.error("Chart not initialized");
        return;
    }

    const chartElement = document.querySelector("#uploadsBarChart");
    if (!chartElement) return;

    if (type === "png" || type === "svg") {
        window.uploadChart.dataURI().then(({ imgURI, svgURI }) => {
            const a = document.createElement("a");
            if (type === "png") {
                a.href = imgURI;
                a.download = "pdf-pages-chart.png";
            } else {
                a.href = svgURI;
                a.download = "pdf-pages-chart.svg";
            }
            a.click();
        }).catch(error => {
            console.error("Error exporting chart:", error);
        });
    }

    if (type === "csv") {
        try {
            const dates = JSON.parse(chartElement.dataset.dates || "[]");
            const counts = JSON.parse(chartElement.dataset.counts || "[]");

            let csv = "Date,PDF Pages\n";
            dates.forEach((date, i) => {
                csv += `${date},${counts[i]}\n`;
            });

            const blob = new Blob([csv], { type: "text/csv" });
            const a = document.createElement("a");
            a.href = URL.createObjectURL(blob);
            a.download = "pdf-pages-data.csv";
            a.click();
            URL.revokeObjectURL(a.href);
        } catch (e) {
            console.error("Error exporting CSV:", e);
        }
    }
};
</script>
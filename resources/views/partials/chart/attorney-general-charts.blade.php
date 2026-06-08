<div class="grid grid-cols-1 gap-6 mt-6 mb-8">

    <!-- Counties vs PDF Pages - Vertical Bar Chart -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Counties vs PDF Pages</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Top 10 counties by published pages</p>
            </div>
            <div class="flex items-center gap-3">
                <div x-data="{openDropDown: false}" class="relative h-fit">
                    <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                        </svg>
                    </button>
                    <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                        <button onclick="if(window.countiesPagesChart) exportCountiesVsPagesChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                        <button onclick="if(window.countiesPagesChart) exportCountiesVsPagesChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                        <button onclick="if(window.countiesPagesChart) exportCountiesVsPagesChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                <div id="countiesPagesChart" 
                     data-counties='@json($roleCharts['counties_vs_pages'] ?? [])'
                     class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                     style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <!-- Marriage Types Distribution & Yearly Trend -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Marriage Types Distribution - Donut Chart -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Marriage Types Distribution</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Published records by type</p>
                </div>
                <div class="flex items-center gap-3">
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="if(window.marriageTypesChart) exportMarriageTypesChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="if(window.marriageTypesChart) exportMarriageTypesChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                            <button onclick="if(window.marriageTypesChart) exportMarriageTypesChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="marriageTypesChart" 
                         data-types='@json($roleCharts['marriage_types_distribution'] ?? [])'
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 350px; width: 100%;"></div>
                </div>
            </div>
        </div>

        <!-- Yearly Publication Trend - Line Chart -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Yearly Publication Trend</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Last 5 years</p>
                </div>
                <div class="flex items-center gap-3">
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="if(window.yearsTrendChart) exportYearsTrendChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="if(window.yearsTrendChart) exportYearsTrendChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                            <button onclick="if(window.yearsTrendChart) exportYearsTrendChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="yearlyTrendChart" 
                         data-years='@json($roleCharts['years_trend'] ?? [])'
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 350px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- County Marriage Statistics - Horizontal Bar Chart -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">County Marriage Statistics</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Top 10 counties by marriage count</p>
            </div>
            <div class="flex items-center gap-3">
                <div x-data="{openDropDown: false}" class="relative h-fit">
                    <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                        </svg>
                    </button>
                    <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                        <button onclick="if(window.countyMarriagesChart) exportCountyMarriagesChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                        <button onclick="if(window.countyMarriagesChart) exportCountyMarriagesChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                        <button onclick="if(window.countyMarriagesChart) exportCountyMarriagesChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                <div id="countyMarriagesChart" 
                     data-marriages='@json($roleCharts['county_marriages'] ?? [])'
                     class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                     style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Export functions for Attorney General charts
window.exportCountiesVsPagesChart = function(format) {
    if (window.countiesPagesChart) {
        if (format === 'csv') {
            const chartElement = document.querySelector("#countiesPagesChart");
            if (chartElement && chartElement.dataset.counties) {
                try {
                    const counties = JSON.parse(chartElement.dataset.counties);
                    if (counties && counties.length > 0) {
                        let csvContent = "County,Pages\n";
                        counties.forEach(row => {
                            csvContent += `${row.county},${row.pages}\n`;
                        });
                        const blob = new Blob([csvContent], { type: 'text/csv' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'counties-vs-pages.csv';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        return;
                    }
                } catch (e) {
                    console.error('Error exporting CSV:', e);
                }
                alert('No data to export');
            }
        } else {
            window.countiesPagesChart.exportToImage({ format: format === 'png' ? 'png' : 'svg' });
        }
    } else {
        alert('Chart not ready for export');
    }
};

window.exportMarriageTypesChart = function(format) {
    if (window.marriageTypesChart) {
        if (format === 'csv') {
            const chartElement = document.querySelector("#marriageTypesChart");
            if (chartElement && chartElement.dataset.types) {
                try {
                    const types = JSON.parse(chartElement.dataset.types);
                    if (types && types.length > 0) {
                        let csvContent = "Marriage Type,Count\n";
                        types.forEach(row => {
                            csvContent += `${row.type},${row.count}\n`;
                        });
                        const blob = new Blob([csvContent], { type: 'text/csv' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'marriage-types-distribution.csv';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        return;
                    }
                } catch (e) {
                    console.error('Error exporting CSV:', e);
                }
                alert('No data to export');
            }
        } else {
            window.marriageTypesChart.exportToImage({ format: format === 'png' ? 'png' : 'svg' });
        }
    } else {
        alert('Chart not ready for export');
    }
};

window.exportYearsTrendChart = function(format) {
    if (window.yearsTrendChart) {
        if (format === 'csv') {
            const chartElement = document.querySelector("#yearlyTrendChart");
            if (chartElement && chartElement.dataset.years) {
                try {
                    const years = JSON.parse(chartElement.dataset.years);
                    if (years && years.length > 0) {
                        let csvContent = "Year,Uploads,Pages\n";
                        years.forEach(row => {
                            csvContent += `${row.year},${row.uploads},${row.pages}\n`;
                        });
                        const blob = new Blob([csvContent], { type: 'text/csv' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'yearly-trend.csv';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        return;
                    }
                } catch (e) {
                    console.error('Error exporting CSV:', e);
                }
                alert('No data to export');
            }
        } else {
            window.yearsTrendChart.exportToImage({ format: format === 'png' ? 'png' : 'svg' });
        }
    } else {
        alert('Chart not ready for export');
    }
};

window.exportCountyMarriagesChart = function(format) {
    if (window.countyMarriagesChart) {
        if (format === 'csv') {
            const chartElement = document.querySelector("#countyMarriagesChart");
            if (chartElement && chartElement.dataset.marriages) {
                try {
                    const marriages = JSON.parse(chartElement.dataset.marriages);
                    if (marriages && marriages.length > 0) {
                        let csvContent = "County,Marriages\n";
                        marriages.forEach(row => {
                            csvContent += `${row.county},${row.marriages}\n`;
                        });
                        const blob = new Blob([csvContent], { type: 'text/csv' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'county-marriages.csv';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        URL.revokeObjectURL(url);
                        return;
                    }
                } catch (e) {
                    console.error('Error exporting CSV:', e);
                }
                alert('No data to export');
            }
        } else {
            window.countyMarriagesChart.exportToImage({ format: format === 'png' ? 'png' : 'svg' });
        }
    } else {
        alert('Chart not ready for export');
    }
};
</script>
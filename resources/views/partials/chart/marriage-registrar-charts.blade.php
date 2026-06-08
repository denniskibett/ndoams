<div class="grid grid-cols-1 gap-6 mt-6 mb-8">
    <!-- Verification Queue & Pages by Type - Row with 3/9 columns -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Verification Queue Status - Donut Chart (3 columns) -->
        <div class="lg:col-span-3 overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Verification Queue</h3>
                
                <div class="flex items-center gap-3">
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="exportVerificationQueueChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="exportVerificationQueueChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                            <button onclick="exportVerificationQueueChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[300px] pl-2 xl:min-w-full">
                    <div id="verificationQueueChart" 
                         data-queue='@json($roleCharts['verification_queue'] ?? [])'
                         class="-ml-5 h-full min-w-[300px] pl-2 xl:min-w-full"
                         style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Pages by Marriage Type Needing Verification - Vertical Bar Chart (9 columns) -->
        <div class="lg:col-span-9 overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pages by Type (Needs Review)</h3>
                
                <div class="flex items-center gap-3">
                    <div x-data="{openDropDown: false}" class="relative h-fit">
                        <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                            <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                            </svg>
                        </button>
                        <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                            <button onclick="exportPagesByTypeChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                            <button onclick="exportPagesByTypeChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                            <button onclick="exportPagesByTypeChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                    <div id="pagesByTypeChart" 
                         data-types='@json($roleCharts['pages_by_type'] ?? [])'
                         class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                         style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Verification Trend - Area Chart (Full Width) -->
    <div class="col-span-full overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Verification Trend</h3>
            
            <div class="flex items-center gap-3">
                <select id="verification-trend-filter" class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-gray-800">
                    <option value="7">Last 7 Days</option>
                    <option value="14" selected>Last 14 Days</option>
                    <option value="30">Last 30 Days</option>
                    <option value="60">Last 60 Days</option>
                </select>

                <div x-data="{openDropDown: false}" class="relative h-fit">
                    <button @click="openDropDown = !openDropDown" class="transition-colors text-gray-400 hover:text-gray-700 dark:hover:text-white">
                        <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.2441 6C10.2441 5.0335 11.0276 4.25 11.9941 4.25H12.0041C12.9706 4.25 13.7541 5.0335 13.7541 6C13.7541 6.9665 12.9706 7.75 12.0041 7.75H11.9941C11.0276 7.75 10.2441 6.9665 10.2441 6ZM10.2441 18C10.2441 17.0335 11.0276 16.25 11.9941 16.25H12.0041C12.9706 16.25 13.7541 17.0335 13.7541 18C13.7541 18.9665 12.9706 19.75 12.0041 19.75H11.9941C11.0276 19.75 10.2441 18.9665 10.2441 18ZM11.9941 10.25C11.0276 10.25 10.2441 11.0335 10.2441 12C10.2441 12.9665 11.0276 13.75 11.9941 13.75H12.0041C12.9706 13.75 13.7541 12.9665 13.7541 12C13.7541 11.0335 12.9706 10.25 12.0041 10.25H11.9941Z" />
                        </svg>
                    </button>
                    <div x-show="openDropDown" @click.outside="openDropDown = false" x-cloak class="absolute right-0 z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 top-full rounded-2xl shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark">
                        <button onclick="exportVerificationTrendChart('png')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as PNG</button>
                        <button onclick="exportVerificationTrendChart('svg')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Save as SVG</button>
                        <button onclick="exportVerificationTrendChart('csv')" class="flex w-full px-3 py-2 font-medium text-left text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">Export CSV</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <div class="-ml-5 min-w-[650px] pl-2 xl:min-w-full">
                <div id="verificationTrendChart" 
                     data-trend='@json($roleCharts['verification_trend'] ?? [])'
                     data-current-days="14"
                     class="-ml-5 h-full min-w-[650px] pl-2 xl:min-w-full"
                     style="height: 380px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle Verification Trend filter change
document.getElementById('verification-trend-filter')?.addEventListener('change', async function(e) {
    const days = e.target.value;
    const chartContainer = document.getElementById('verificationTrendChart');
    
    if (window.verificationTrendChart) {
        chartContainer.style.opacity = '0.5';
    }
    
    try {
        const response = await fetch(`/dashboard/marriage-registrar/verification-trend?days=${days}`);
        const data = await response.json();
        
        if (data.success && window.verificationTrendChart && window.verificationTrendChart.updateOptions) {
            window.verificationTrendChart.updateOptions({
                xaxis: { categories: data.labels },
                series: [{ data: data.counts }]
            });
            chartContainer.setAttribute('data-trend', JSON.stringify(data.trendData));
            chartContainer.setAttribute('data-current-days', days);
        }
    } catch (error) {
        console.error('Error fetching verification trend data:', error);
    } finally {
        if (window.verificationTrendChart) {
            setTimeout(() => { chartContainer.style.opacity = '1'; }, 200);
        }
    }
});
</script>
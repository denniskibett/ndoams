import ApexCharts from "apexcharts";

let isInitialized = false;

const marriageTellerCharts = () => {
    if (isInitialized) {
        console.log('Marriage Teller charts already initialized');
        return;
    }
    
    console.log('Initializing Marriage Teller charts...');
    
    // Debug: Check if containers exist
    console.log('Weekly trend element exists:', !!document.querySelector("#weeklyTrendChart"));
    console.log('Clerk performance element exists:', !!document.querySelector("#clerkPerformanceChart"));
    
    // Get colors from meta tags or use defaults
    const primaryColor = document.querySelector('meta[name="primary-color"]')?.content || '#3A57E8';
    const secondaryColor = document.querySelector('meta[name="secondary-color"]')?.content || '#08B1BA';
    
    // Calculate nice round number for Y-axis max dynamically (same as uploads bar chart)
    const calculateYAxisMax = (max) => {
        if (max === 0) return 5;
        
        const magnitude = Math.pow(10, Math.floor(Math.log10(max)));
        const normalized = max / magnitude;
        
        let step;
        if (normalized <= 1) step = 1;
        else if (normalized <= 2) step = 2;
        else if (normalized <= 5) step = 5;
        else step = 10;
        
        return Math.ceil(max / (step * magnitude)) * (step * magnitude);
    };
    
    // ============================================
    // Clerk Performance VERTICAL Bar Chart (like uploads bar chart)
    // ============================================
    const clerkPerformanceElement = document.querySelector("#clerkPerformanceChart");
    if (clerkPerformanceElement) {
        let clerks = [];
        try {
            clerks = JSON.parse(clerkPerformanceElement.dataset.clerks || '[]');
        } catch (e) {
            console.error('Error parsing clerk data:', e);
            clerks = [];
        }
        
        console.log('Clerk performance data:', clerks);
        
        // Prepare data with fallback for empty data
        const hasData = clerks && clerks.length > 0;
        const clerkNames = hasData ? clerks.map(c => c.name) : ['No Data'];
        const completionRates = hasData ? clerks.map(c => c.completion_rate) : [0];
        
        const maxRate = Math.max(...completionRates, 1);
        const yAxisMax = Math.min(100, calculateYAxisMax(maxRate));
        
        // Using EXACT same styling as uploads-bar-chart.js
        const performanceOptions = {
            series: [{ name: "Completion Rate %", data: completionRates }],
            colors: [secondaryColor],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "bar",
                height: 400,
                toolbar: { show: false },
                animations: { enabled: true, speed: 800 },
            },
            plotOptions: {
                bar: {
                    horizontal: false,  // VERTICAL bars (like uploads chart)
                    columnWidth: "39%",  // Same as uploads chart
                    borderRadius: 5,
                    borderRadiusApplication: "end",
                    dataLabels: { position: 'top' },
                },
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif', colors: ['#333'], fontWeight: '600' },
                formatter: function(val) { return val.toFixed(1) + '%'; },
            },
            xaxis: { 
                categories: clerkNames,
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                labels: {
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    rotate: 0,
                    trim: true,
                }
            },
            yaxis: { 
                min: 0,
                max: yAxisMax,
                tickAmount: 5,
                forceNiceScale: true,
                decimalsInFloat: 0,
                title: { 
                    text: "Completion Rate (%)",
                    style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                },
                labels: {
                    formatter: (value) => Math.round(value).toString(),
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    align: 'right',
                    padding: 15,
                },
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                crosshairs: {
                    show: true,
                    position: 'back',
                    stroke: { color: '#b6b6b6', width: 1, dashArray: 3 },
                },
            },
            grid: { 
                show: true,
                borderColor: "#e5e7eb",
                strokeDashArray: 5,
                position: "back",
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { left: 10, right: 10 }
            },
            legend: {
                show: true,
                position: "top",
                horizontalAlign: "left",
            },
            tooltip: { 
                y: { formatter: (val) => val.toFixed(1) + '%' } 
            },
            states: {
                hover: { filter: { type: 'lighten', value: 0.1 } },
            },
        };
        
        // Make sure container has dimensions
        if (clerkPerformanceElement.style.height === '' || clerkPerformanceElement.style.height === '0px') {
            clerkPerformanceElement.style.height = '400px';
        }
        if (clerkPerformanceElement.style.width === '' || clerkPerformanceElement.style.width === '0px') {
            clerkPerformanceElement.style.width = '100%';
        }
        
        // Destroy existing chart if it exists
        if (window.clerkPerformanceChart && typeof window.clerkPerformanceChart.destroy === 'function') {
            window.clerkPerformanceChart.destroy();
        }
        window.clerkPerformanceChart = new ApexCharts(clerkPerformanceElement, performanceOptions);
        window.clerkPerformanceChart.render();
        console.log('Clerk performance chart rendered' + (hasData ? ' with data' : ' with fallback (no data)'));
    }
    
    // ============================================
    // Team Weekly Trend Chart - Line Chart
    // ============================================
    const weeklyTrendElement = document.querySelector("#weeklyTrendChart");
    if (weeklyTrendElement) {
        let trendData = [];
        try {
            trendData = JSON.parse(weeklyTrendElement.dataset.trend || '[]');
        } catch (e) {
            console.error('Error parsing trend data:', e);
            trendData = [];
        }
        
        console.log('Weekly trend data:', trendData);
        
        // Prepare data with fallback for empty data
        const hasData = trendData && trendData.length > 0;
        const weeks = hasData ? trendData.map(t => t.week) : ['No Data'];
        const counts = hasData ? trendData.map(t => t.count) : [0];
        
        const maxCount = Math.max(...counts, 1);
        const yAxisMax = calculateYAxisMax(maxCount);
        
        // Using EXACT same styling as uploads-bar-chart.js for consistency
        const trendOptions = {
            series: [{ name: "Pages Completed", data: counts }],
            colors: [primaryColor],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "line",
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false },
                animations: { enabled: true, speed: 800 },
            },
            stroke: { curve: "smooth", width: 3 },
            markers: { 
                size: hasData ? 6 : 0,
                colors: [primaryColor], 
                strokeColors: '#fff', 
                strokeWidth: 2,
                hover: { size: 8 }
            },
            xaxis: { 
                categories: weeks,
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                labels: {
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    rotate: 0,
                    trim: true,
                }
            },
            yaxis: { 
                min: 0,
                max: yAxisMax,
                tickAmount: 5,
                forceNiceScale: true,
                decimalsInFloat: 0,
                title: { 
                    text: "Pages Completed",
                    style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                },
                labels: {
                    formatter: (value) => Math.round(value).toString(),
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    align: 'right',
                    padding: 15,
                },
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                crosshairs: {
                    show: true,
                    position: 'back',
                    stroke: { color: '#b6b6b6', width: 1, dashArray: 3 },
                },
            },
            dataLabels: { 
                enabled: hasData,
                style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif', colors: ['#333'], fontWeight: '600' },
                offsetY: -10,
                formatter: (val) => val.toLocaleString()
            },
            tooltip: { 
                y: { formatter: (val) => val.toLocaleString() + " pages" } 
            },
            grid: { 
                show: true,
                borderColor: "#e5e7eb",
                strokeDashArray: 5,
                position: "back",
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { left: 10, right: 10 }
            },
            legend: {
                show: true,
                position: "top",
                horizontalAlign: "left",
            },
            states: {
                hover: { filter: { type: 'lighten', value: 0.1 } },
            },
        };
        
        // Make sure container has dimensions
        if (weeklyTrendElement.style.height === '' || weeklyTrendElement.style.height === '0px') {
            weeklyTrendElement.style.height = '350px';
        }
        if (weeklyTrendElement.style.width === '' || weeklyTrendElement.style.width === '0px') {
            weeklyTrendElement.style.width = '100%';
        }
        
        // Destroy existing chart if it exists
        if (window.weeklyTrendChart && typeof window.weeklyTrendChart.destroy === 'function') {
            window.weeklyTrendChart.destroy();
        }
        window.weeklyTrendChart = new ApexCharts(weeklyTrendElement, trendOptions);
        window.weeklyTrendChart.render();
        console.log('Weekly trend chart rendered' + (hasData ? ' with data' : ' with fallback (no data)'));
    }
    
    isInitialized = true;
};

export default marriageTellerCharts;
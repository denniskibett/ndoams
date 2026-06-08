import ApexCharts from "apexcharts";

let isInitialized = false;

const dataClerkCharts = () => {
    // Prevent double initialization
    if (isInitialized) {
        console.log('Data Clerk charts already initialized, skipping...');
        return;
    }
    
    console.log('Initializing Data Clerk charts...');
    
    const primaryColor = document.querySelector('meta[name="primary-color"]')?.content || '#3A57E8';
    const secondaryColor = document.querySelector('meta[name="secondary-color"]')?.content || '#08B1BA';
    
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
    
    // Calculate nice round number for Y-axis max dynamically
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
    
    // Completion Rate Donut Chart
    const completionChartElement = document.querySelector("#completionRateChart");
    if (completionChartElement) {
        const completionRate = parseFloat(completionChartElement.dataset.rate || 0);
        
        const completionOptions = {
            series: [completionRate, 100 - completionRate],
            colors: [primaryColor, "#e5e7eb"],
            chart: {
                type: "donut",
                height: 280,
                toolbar: { show: false },
                fontFamily: "Outfit, sans-serif",
            },
            labels: ["Completed", "Pending"],
            legend: { 
                position: "bottom",
                fontFamily: "Outfit, sans-serif",
            },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: "65%",
                        labels: {
                            show: true,
                            name: { 
                                show: true, 
                                fontSize: '14px', 
                                fontFamily: 'Outfit, sans-serif' 
                            },
                            value: {
                                show: true,
                                fontSize: '24px',
                                fontWeight: 'bold',
                                fontFamily: 'Outfit, sans-serif',
                                formatter: (val) => `${Math.round(parseFloat(val))}%`,
                            },
                            total: {
                                show: true,
                                label: "Completion",
                                fontSize: '14px',
                                fontFamily: 'Outfit, sans-serif',
                                formatter: () => `${completionRate}%`,
                            },
                        },
                    },
                },
            },
        };
        
        if (window.completionChart) window.completionChart.destroy();
        window.completionChart = new ApexCharts(completionChartElement, completionOptions);
        window.completionChart.render();
        console.log('Completion rate chart rendered');
    }
    
    // Status Distribution Bar Chart (with formatted names and proper Y-axis)
    const statusChartElement = document.querySelector("#statusDistributionChart");
    if (statusChartElement) {
        let statuses = JSON.parse(statusChartElement.dataset.statuses || "[]");
        const values = JSON.parse(statusChartElement.dataset.values || "[]");
        
        // Format status names for display
        const formattedStatuses = statuses.map(s => formatStatusName(s));
        
        // Ensure we have valid data
        if (!formattedStatuses.length || !values.length) return;
        
        const maxCount = Math.max(...values, 1);
        const yAxisMax = calculateYAxisMax(maxCount);
        
        const statusOptions = {
            series: [{ name: "Pages", data: values }],
            colors: [primaryColor],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "bar",
                height: 350,
                toolbar: { show: false },
                animations: { enabled: true, speed: 800 },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "39%",
                    borderRadius: 5,
                    borderRadiusApplication: "end",
                    dataLabels: { position: 'top' },
                },
            },
            dataLabels: {
                enabled: false,
                offsetY: -20,
                style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif', colors: ['#333'], fontWeight: '600' },
                formatter: function(val) { return val.toLocaleString(); },
            },
            xaxis: {
                categories: formattedStatuses,
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                labels: {
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    rotate: 0,
                    trim: true,
                },
            },
            yaxis: {
                min: 0,
                max: yAxisMax,
                tickAmount: 5,
                forceNiceScale: true,
                decimalsInFloat: 0,
                title: {
                    text: "Number of Pages",
                    style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' },
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
                padding: { left: 10, right: 10 },
            },
            legend: {
                show: true,
                position: "top",
                horizontalAlign: "left",
            },
            tooltip: {
                y: { formatter: (val) => Math.round(val).toLocaleString() + " pages" },
            },
            states: {
                hover: { filter: { type: 'lighten', value: 0.1 } },
            },
        };
        
        // Make sure container has dimensions
        if (statusChartElement.style.height === '' || statusChartElement.style.height === '0px') {
            statusChartElement.style.height = '350px';
        }
        if (statusChartElement.style.width === '' || statusChartElement.style.width === '0px') {
            statusChartElement.style.width = '100%';
        }
        
        if (window.statusChart) window.statusChart.destroy();
        window.statusChart = new ApexCharts(statusChartElement, statusOptions);
        window.statusChart.render();
        console.log('Status distribution chart rendered');
        
        // Add update method for dynamic data
        window.statusChart.updateChartData = (newStatuses, newValues) => {
            const newFormattedStatuses = newStatuses.map(s => formatStatusName(s));
            const newMaxCount = Math.max(...newValues, 1);
            const newYAxisMax = calculateYAxisMax(newMaxCount);
            
            window.statusChart.updateOptions({
                xaxis: { categories: newFormattedStatuses },
                yaxis: { max: newYAxisMax },
            });
            window.statusChart.updateSeries([{ data: newValues }]);
        };
    }
    
    // Daily Productivity Bar Chart (with proper Y-axis title)
    const productivityChartElement = document.querySelector("#dailyProductivityChart");
    if (productivityChartElement) {
        const labels = JSON.parse(productivityChartElement.dataset.labels || "[]");
        const counts = JSON.parse(productivityChartElement.dataset.counts || "[]");
        
        // Ensure we have valid data
        if (!labels.length || !counts.length) return;
        
        const maxCount = Math.max(...counts, 1);
        const yAxisMax = calculateYAxisMax(maxCount);
        
        const productivityOptions = {
            series: [{ name: "Pages Completed", data: counts }],
            colors: [secondaryColor],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "bar",
                height: 380,
                toolbar: { show: false },
                animations: { enabled: true, speed: 800 },
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "39%",
                    borderRadius: 5,
                    borderRadiusApplication: "end",
                    dataLabels: { position: 'top' },
                },
            },
            dataLabels: {
                enabled: false,
                offsetY: -20,
                style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif', colors: ['#333'], fontWeight: '600' },
                formatter: function(val) { return val.toLocaleString(); },
            },
            xaxis: {
                categories: labels,
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                labels: {
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    rotate: 0,
                    trim: true,
                },
            },
            yaxis: {
                min: 0,
                max: yAxisMax,
                tickAmount: 5,
                forceNiceScale: true,
                decimalsInFloat: 0,
                title: {
                    text: "Number of Pages Completed",
                    style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' },
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
                padding: { left: 10, right: 10 },
            },
            legend: {
                show: true,
                position: "top",
                horizontalAlign: "left",
            },
            tooltip: {
                y: { formatter: (val) => Math.round(val).toLocaleString() + " pages" },
            },
            states: {
                hover: { filter: { type: 'lighten', value: 0.1 } },
            },
        };
        
        // Make sure container has dimensions
        if (productivityChartElement.style.height === '' || productivityChartElement.style.height === '0px') {
            productivityChartElement.style.height = '380px';
        }
        if (productivityChartElement.style.width === '' || productivityChartElement.style.width === '0px') {
            productivityChartElement.style.width = '100%';
        }
        
        if (window.productivityChart) window.productivityChart.destroy();
        window.productivityChart = new ApexCharts(productivityChartElement, productivityOptions);
        window.productivityChart.render();
        
        // Add update method for dynamic data (matching uploads-bar-chart pattern)
        window.productivityChart.updateChartData = (newLabels, newCounts) => {
            const newMaxCount = Math.max(...newCounts, 1);
            const newYAxisMax = calculateYAxisMax(newMaxCount);
            
            window.productivityChart.updateOptions({
                xaxis: { categories: newLabels },
                yaxis: {
                    max: newYAxisMax,
                    title: { text: "Number of Pages Completed" },
                    labels: { formatter: (value) => Math.round(value).toString() }
                },
            });
            window.productivityChart.updateSeries([{ name: "Pages Completed", data: newCounts }]);
        };
        
        console.log('Daily productivity chart rendered');
    }
    
    isInitialized = true;
};

export default dataClerkCharts;
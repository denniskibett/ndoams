import ApexCharts from "apexcharts";

let isInitialized = false;

const adminCharts = () => {
    if (isInitialized) {
        console.log('Admin charts already initialized');
        return;
    }
    
    console.log('Initializing Admin charts...');
    
    const primaryColor = document.querySelector('meta[name="primary-color"]')?.content || '#3A57E8';
    const secondaryColor = document.querySelector('meta[name="secondary-color"]')?.content || '#08B1BA';
    
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
    // Users by Role - Vertical Bar Chart
    // ============================================
    const usersByRoleElement = document.querySelector("#usersByRoleChart");
    if (usersByRoleElement) {
        let users = [];
        try {
            users = JSON.parse(usersByRoleElement.dataset.roles || '[]');
        } catch (e) {
            console.error('Error parsing users data:', e);
            users = [];
        }
        
        const hasData = users && users.length > 0;
        const categories = hasData ? users.map(u => u.role) : ['No Data'];
        const values = hasData ? users.map(u => u.count) : [0];
        
        const maxCount = Math.max(...values, 1);
        const yAxisMax = calculateYAxisMax(maxCount);
        
        const usersOptions = {
            series: [{ name: 'Users', data: values }],
            colors: [primaryColor],
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false },
                fontFamily: "Outfit, sans-serif",
                animations: { enabled: true, speed: 800 }
            },
            plotOptions: {
                bar: {
                    borderRadius: 5,
                    columnWidth: '39%',
                    borderRadiusApplication: "end",
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif', colors: ['#333'], fontWeight: '600' },
                formatter: (val) => val.toLocaleString()
            },
            xaxis: {
                categories: categories,
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                labels: {
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    rotate: -45,
                    trim: true,
                }
            },
            yaxis: {
                min: 0,
                max: yAxisMax,
                tickAmount: 5,
                forceNiceScale: true,
                title: {
                    text: "Number of Users",
                    style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                },
                labels: {
                    formatter: (value) => Math.round(value).toString(),
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' }
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
            legend: { show: false },
            tooltip: { y: { formatter: (val) => val.toLocaleString() + " users" } }
        };
        
        if (usersByRoleElement.style.height === '' || usersByRoleElement.style.height === '0px') {
            usersByRoleElement.style.height = '350px';
        }
        if (usersByRoleElement.style.width === '' || usersByRoleElement.style.width === '0px') {
            usersByRoleElement.style.width = '100%';
        }
        
        if (window.usersByRoleChart && typeof window.usersByRoleChart.destroy === 'function') {
            window.usersByRoleChart.destroy();
        }
        window.usersByRoleChart = new ApexCharts(usersByRoleElement, usersOptions);
        window.usersByRoleChart.render();
        console.log('Users by role chart rendered');
    }
    
    // ============================================
    // Activity Timeline - Area Chart with dynamic updates
    // ============================================
    const activityTimelineElement = document.querySelector("#activityTimelineChart");
    if (activityTimelineElement) {
        let activity = [];
        try {
            activity = JSON.parse(activityTimelineElement.dataset.activity || '[]');
        } catch (e) {
            console.error('Error parsing activity data:', e);
            activity = [];
        }
        
        const hasData = activity && activity.length > 0;
        const labels = hasData ? activity.map(a => a.label) : ['No Data'];
        const uploads = hasData ? activity.map(a => a.uploads) : [0];
        const marriages = hasData ? activity.map(a => a.marriages) : [0];
        
        const maxUploads = Math.max(...uploads, 1);
        const maxMarriages = Math.max(...marriages, 1);
        const yAxisMax = Math.max(calculateYAxisMax(maxUploads), calculateYAxisMax(maxMarriages));
        
        const activityOptions = {
            series: [
                { name: 'PDF Uploads', data: uploads },
                { name: 'Marriages', data: marriages }
            ],
            colors: [primaryColor, secondaryColor],
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false },
                zoom: { enabled: false },
                fontFamily: "Outfit, sans-serif",
                animations: { enabled: true, speed: 800 }
            },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: {
                    enabled: true,
                    opacityFrom: 0.55,
                    opacityTo: 0,
                },
            },
            markers: {
                size: hasData ? 4 : 0,
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: { size: 6 }
            },
            xaxis: {
                categories: labels,
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                title: {
                    text: "Date",
                    style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                },
                labels: {
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    rotate: 0,
                }
            },
            yaxis: {
                min: 0,
                max: yAxisMax,
                tickAmount: 5,
                title: {
                    text: "Count",
                    style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                },
                labels: {
                    formatter: (value) => Math.round(value).toString(),
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' }
                },
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                crosshairs: {
                    show: true,
                    position: 'back',
                    stroke: { color: '#b6b6b6', width: 1, dashArray: 3 },
                },
            },
            dataLabels: { enabled: false },
            tooltip: { shared: true, intersect: false },
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
                fontFamily: "Outfit, sans-serif",
            }
        };
        
        if (activityTimelineElement.style.height === '' || activityTimelineElement.style.height === '0px') {
            activityTimelineElement.style.height = '350px';
        }
        if (activityTimelineElement.style.width === '' || activityTimelineElement.style.width === '0px') {
            activityTimelineElement.style.width = '100%';
        }
        
        if (window.activityTimelineChart && typeof window.activityTimelineChart.destroy === 'function') {
            window.activityTimelineChart.destroy();
        }
        window.activityTimelineChart = new ApexCharts(activityTimelineElement, activityOptions);
        window.activityTimelineChart.render();
        console.log('Activity timeline chart rendered');
        
        // Add update method for dynamic filtering
        window.activityTimelineChart.updateChartData = (newLabels, newUploads, newMarriages) => {
            const newMaxUploads = Math.max(...newUploads, 1);
            const newMaxMarriages = Math.max(...newMarriages, 1);
            const newYAxisMax = Math.max(calculateYAxisMax(newMaxUploads), calculateYAxisMax(newMaxMarriages));
            
            window.activityTimelineChart.updateOptions({
                xaxis: { categories: newLabels },
                yaxis: { max: newYAxisMax }
            });
            window.activityTimelineChart.updateSeries([
                { name: 'PDF Uploads', data: newUploads },
                { name: 'Marriages', data: newMarriages }
            ]);
        };
    }
    
    // ============================================
    // Upload Trends - Dual Axis Chart with dynamic updates
    // ============================================
    const uploadTrendsElement = document.querySelector("#uploadTrendsChart");
    if (uploadTrendsElement) {
        let trends = [];
        try {
            trends = JSON.parse(uploadTrendsElement.dataset.trends || '[]');
        } catch (e) {
            console.error('Error parsing trends data:', e);
            trends = [];
        }
        
        const hasData = trends && trends.length > 0;
        const months = hasData ? trends.map(t => t.month) : ['No Data'];
        const uploads = hasData ? trends.map(t => t.uploads) : [0];
        const storage = hasData ? trends.map(t => t.size_mb) : [0];
        
        const maxUploads = Math.max(...uploads, 1);
        const yAxisMaxUploads = calculateYAxisMax(maxUploads);
        
        const trendsOptions = {
            series: [
                { name: 'PDF Uploads', data: uploads, type: 'column' },
                { name: 'Storage (MB)', data: storage, type: 'line' }
            ],
            chart: {
                type: 'line',
                height: 420,
                toolbar: { show: false },
                stacked: false,
                fontFamily: "Outfit, sans-serif",
                animations: { enabled: true, speed: 800 }
            },
            colors: [primaryColor, secondaryColor],
            plotOptions: {
                bar: {
                    columnWidth: '39%',
                    borderRadius: 5,
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: { fontSize: '11px', fontFamily: 'Outfit, sans-serif', colors: ['#333'], fontWeight: '600' },
                formatter: (val) => val.toLocaleString()
            },
            xaxis: {
                categories: months,
                axisBorder: { show: true, color: '#e5e7eb' },
                axisTicks: { show: true, color: '#e5e7eb' },
                labels: {
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                    rotate: -45,
                }
            },
            yaxis: [
                {
                    title: { 
                        text: 'Number of Uploads',
                        style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                    },
                    min: 0,
                    max: yAxisMaxUploads,
                    tickAmount: 5,
                    labels: {
                        formatter: (value) => Math.round(value).toString(),
                        style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' }
                    }
                },
                {
                    opposite: true,
                    title: { 
                        text: 'Storage (MB)',
                        style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                    },
                    min: 0,
                    labels: {
                        formatter: (value) => value.toFixed(1),
                        style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' }
                    }
                }
            ],
            stroke: { width: [0, 3], curve: 'smooth' },
            markers: { size: [0, 4], colors: [primaryColor, secondaryColor] },
            grid: {
                show: true,
                borderColor: "#e5e7eb",
                strokeDashArray: 5,
                position: "back",
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: { left: 10, right: 10 }
            },
            tooltip: { 
                shared: true,
                y: [
                    { formatter: (val) => val.toLocaleString() + " uploads" },
                    { formatter: (val) => val.toFixed(1) + " MB" }
                ]
            },
            legend: {
                show: true,
                position: "top",
                horizontalAlign: "left",
                fontFamily: "Outfit, sans-serif",
            }
        };
        
        if (uploadTrendsElement.style.height === '' || uploadTrendsElement.style.height === '0px') {
            uploadTrendsElement.style.height = '420px';
        }
        if (uploadTrendsElement.style.width === '' || uploadTrendsElement.style.width === '0px') {
            uploadTrendsElement.style.width = '100%';
        }
        
        if (window.uploadTrendsChart && typeof window.uploadTrendsChart.destroy === 'function') {
            window.uploadTrendsChart.destroy();
        }
        window.uploadTrendsChart = new ApexCharts(uploadTrendsElement, trendsOptions);
        window.uploadTrendsChart.render();
        console.log('Upload trends chart rendered');
        
        // Add update method for dynamic filtering
        window.uploadTrendsChart.updateChartData = (newMonths, newUploads, newStorage) => {
            const newMaxUploads = Math.max(...newUploads, 1);
            const newYAxisMaxUploads = calculateYAxisMax(newMaxUploads);
            
            window.uploadTrendsChart.updateOptions({
                xaxis: { categories: newMonths },
                yaxis: [{ max: newYAxisMaxUploads }]
            });
            window.uploadTrendsChart.updateSeries([
                { name: 'PDF Uploads', data: newUploads, type: 'column' },
                { name: 'Storage (MB)', data: newStorage, type: 'line' }
            ]);
        };
    }
    
// ============================================
// Top Clerks Chart - VERTICAL Bar Chart (exactly like Marriage Teller)
// ============================================
const topClerksElement = document.querySelector("#topClerksChart");
if (topClerksElement) {
    let names = [];
    let rates = [];
    try {
        names = JSON.parse(topClerksElement.dataset.names || '[]');
        rates = JSON.parse(topClerksElement.dataset.rates || '[]');
    } catch (e) {
        console.error('Error parsing top clerks data:', e);
        names = [];
        rates = [];
    }
    
    const hasData = names && names.length > 0;
    const clerkNames = hasData ? names : ['No Data'];
    const completionRates = hasData ? rates : [0];
    
    const maxRate = Math.max(...completionRates, 1);
    const yAxisMax = Math.min(100, calculateYAxisMax(maxRate));
    
    // VERTICAL BAR CHART - EXACTLY matching Marriage Teller chart
    const topClerksOptions = {
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
                horizontal: false,  // VERTICAL bars (exactly like Marriage Teller)
                columnWidth: "39%",  // Same as uploads chart and Marriage Teller
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
    
    if (topClerksElement.style.height === '' || topClerksElement.style.height === '0px') {
        topClerksElement.style.height = '400px';
    }
    if (topClerksElement.style.width === '' || topClerksElement.style.width === '0px') {
        topClerksElement.style.width = '100%';
    }
    
    if (window.topClerksChart && typeof window.topClerksChart.destroy === 'function') {
        window.topClerksChart.destroy();
    }
    window.topClerksChart = new ApexCharts(topClerksElement, topClerksOptions);
    window.topClerksChart.render();
    console.log('Top clerks VERTICAL chart rendered');
}
    
    isInitialized = true;
};

export default adminCharts;
import ApexCharts from "apexcharts";

let isInitialized = false;

const marriageRegistrarCharts = () => {
    if (isInitialized) {
        console.log('Marriage Registrar charts already initialized');
        return;
    }
    
    console.log('Initializing Marriage Registrar charts...');
    
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
    // Verification Queue - Donut Chart
    // ============================================
    const queueElement = document.querySelector("#verificationQueueChart");
    if (queueElement) {
        let queue = {};
        try {
            queue = JSON.parse(queueElement.dataset.queue || '{}');
        } catch (e) {
            console.error('Error parsing queue data:', e);
            queue = {};
        }
        
        const hasData = Object.keys(queue).length > 0;
        const labels = hasData ? Object.keys(queue).map(k => k.replace(/_/g, ' ').toUpperCase()) : ['No Data'];
        const values = hasData ? Object.values(queue) : [1];
        
        const queueOptions = {
            series: values,
            chart: { 
                type: 'donut', 
                height: 320, 
                toolbar: { show: false },
                fontFamily: "Outfit, sans-serif",
            },
            labels: labels,
            colors: [secondaryColor, primaryColor, '#f59e0b', '#10b981', '#8b5cf6', '#ef4444'],
            legend: { 
                position: 'bottom',
                fontFamily: "Outfit, sans-serif",
            },
            plotOptions: { 
                pie: { 
                    donut: { 
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '14px', fontFamily: 'Outfit, sans-serif' },
                            value: { show: true, fontSize: '24px', fontWeight: 'bold', fontFamily: 'Outfit, sans-serif' },
                            total: { show: true, label: 'Total', fontSize: '14px', fontFamily: 'Outfit, sans-serif' }
                        }
                    } 
                } 
            },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: (val) => val.toLocaleString() + ' pages' } }
        };
        
        if (queueElement.style.height === '' || queueElement.style.height === '0px') {
            queueElement.style.height = '350px';
        }
        if (queueElement.style.width === '' || queueElement.style.width === '0px') {
            queueElement.style.width = '100%';
        }
        
        if (window.verificationQueueChart && typeof window.verificationQueueChart.destroy === 'function') {
            window.verificationQueueChart.destroy();
        }
        window.verificationQueueChart = new ApexCharts(queueElement, queueOptions);
        window.verificationQueueChart.render();
        console.log('Verification queue chart rendered');
    }
    
    // ============================================
    // Pages by Type - Vertical Bar Chart
    // ============================================
    const pagesByTypeElement = document.querySelector("#pagesByTypeChart");
    if (pagesByTypeElement) {
        let types = {};
        try {
            types = JSON.parse(pagesByTypeElement.dataset.types || '{}');
        } catch (e) {
            console.error('Error parsing types data:', e);
            types = {};
        }
        
        const hasData = Object.keys(types).length > 0;
        const categories = hasData ? Object.keys(types) : ['No Data'];
        const values = hasData ? Object.values(types) : [0];
        
        const maxCount = Math.max(...values, 1);
        const yAxisMax = calculateYAxisMax(maxCount);
        
        const typeOptions = {
            series: [{ name: 'Pages', data: values }],
            chart: { 
                type: 'bar', 
                height: 350, 
                toolbar: { show: false },
                fontFamily: "Outfit, sans-serif",
                animations: { enabled: true, speed: 800 }
            },
            colors: [secondaryColor],
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
                    text: "Number of Pages",
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
            tooltip: { y: { formatter: (val) => val.toLocaleString() + " pages" } }
        };
        
        if (pagesByTypeElement.style.height === '' || pagesByTypeElement.style.height === '0px') {
            pagesByTypeElement.style.height = '350px';
        }
        if (pagesByTypeElement.style.width === '' || pagesByTypeElement.style.width === '0px') {
            pagesByTypeElement.style.width = '100%';
        }
        
        if (window.pagesByTypeChart && typeof window.pagesByTypeChart.destroy === 'function') {
            window.pagesByTypeChart.destroy();
        }
        window.pagesByTypeChart = new ApexCharts(pagesByTypeElement, typeOptions);
        window.pagesByTypeChart.render();
        console.log('Pages by type chart rendered');
    }
    
    // ============================================
    // Verification Trend - Area Chart (with Y-axis visible)
    // ============================================
    const verificationTrendElement = document.querySelector("#verificationTrendChart");
    if (verificationTrendElement) {
        let trend = [];
        try {
            trend = JSON.parse(verificationTrendElement.dataset.trend || '[]');
        } catch (e) {
            console.error('Error parsing trend data:', e);
            trend = [];
        }
        
        const hasData = trend && trend.length > 0;
        const labels = hasData ? trend.map(t => t.label) : ['No Data'];
        const counts = hasData ? trend.map(t => t.count) : [0];
        
        const maxCount = Math.max(...counts, 1);
        const yAxisMax = calculateYAxisMax(maxCount);
        
        const trendOptions = {
            series: [{ name: "Verifications", data: counts }],
            colors: [primaryColor],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "area",
                height: 380,
                toolbar: { show: false },
                zoom: { enabled: false },
            },
            fill: {
                type: "gradient",
                gradient: {
                    enabled: true,
                    opacityFrom: 0.55,
                    opacityTo: 0,
                },
            },
            stroke: {
                curve: "smooth",
                width: 2,
            },
            markers: {
                size: hasData ? 4 : 0,
                colors: [primaryColor],
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: { size: 6 },
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
                forceNiceScale: true,
                title: { 
                    text: "Number of Verifications",
                    style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                },
                labels: {
                    formatter: (value) => Math.round(value).toString(),
                    style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
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
            tooltip: { y: { formatter: (val) => val.toLocaleString() + " verifications" } },
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
        };
        
        if (verificationTrendElement.style.height === '' || verificationTrendElement.style.height === '0px') {
            verificationTrendElement.style.height = '380px';
        }
        if (verificationTrendElement.style.width === '' || verificationTrendElement.style.width === '0px') {
            verificationTrendElement.style.width = '100%';
        }
        
        if (window.verificationTrendChart && typeof window.verificationTrendChart.destroy === 'function') {
            window.verificationTrendChart.destroy();
        }
        window.verificationTrendChart = new ApexCharts(verificationTrendElement, trendOptions);
        window.verificationTrendChart.render();
        console.log('Verification trend chart rendered');
    }
    
    isInitialized = true;
};

export default marriageRegistrarCharts;
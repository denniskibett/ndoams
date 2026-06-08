import ApexCharts from "apexcharts";

let isInitialized = false;

const attorneyGeneralCharts = () => {
    if (isInitialized) {
        console.log('Attorney General charts already initialized');
        return;
    }
    
    console.log('Initializing Attorney General charts...');
    
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
    // Counties vs Pages - Vertical Bar Chart
    // ============================================
    const countiesElement = document.querySelector("#countiesPagesChart");
    if (countiesElement) {
        try {
            let counties = [];
            try {
                const countiesData = countiesElement.dataset.counties;
                if (countiesData && countiesData.trim() !== '') {
                    counties = JSON.parse(countiesData);
                }
            } catch (e) {
                console.error('Error parsing counties data:', e);
                counties = [];
            }
            
            const hasData = counties && Array.isArray(counties) && counties.length > 0;
            const countyNames = hasData ? counties.map(c => c.county || 'Unknown') : ['No Data Available'];
            const pagesCount = hasData ? counties.map(c => parseInt(c.pages) || 0) : [0];
            
            const maxCount = Math.max(...pagesCount, 1);
            const yAxisMax = calculateYAxisMax(maxCount);
            
            const countiesOptions = {
                series: [{ name: 'PDF Pages', data: pagesCount }],
                colors: [primaryColor],
                chart: {
                    type: 'bar',
                    height: 400,
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
                    enabled: hasData,
                    offsetY: -20,
                    style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif', colors: ['#333'], fontWeight: '600' },
                    formatter: (val) => val > 0 ? val.toLocaleString() : ''
                },
                xaxis: {
                    categories: countyNames,
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
                legend: { show: false },
                tooltip: { y: { formatter: (val) => val > 0 ? val.toLocaleString() + " pages" : "No data" } },
                noData: {
                    text: 'No data available',
                    align: 'center',
                    verticalAlign: 'middle',
                    style: { fontSize: '14px', fontFamily: 'Outfit, sans-serif', color: '#64748b' }
                }
            };
            
            if (countiesElement.style.height === '' || countiesElement.style.height === '0px') {
                countiesElement.style.height = '400px';
            }
            if (countiesElement.style.width === '' || countiesElement.style.width === '0px') {
                countiesElement.style.width = '100%';
            }
            
            if (window.countiesPagesChart && typeof window.countiesPagesChart.destroy === 'function') {
                window.countiesPagesChart.destroy();
            }
            window.countiesPagesChart = new ApexCharts(countiesElement, countiesOptions);
            window.countiesPagesChart.render();
            console.log('Counties vs pages chart rendered');
        } catch (error) {
            console.error('Error rendering counties chart:', error);
            countiesElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #ef4444;">Error loading chart data</div>';
        }
    }
    
    // ============================================
    // Marriage Types Distribution - Donut Chart
    // ============================================
    const typesElement = document.querySelector("#marriageTypesChart");
    if (typesElement) {
        try {
            let types = [];
            try {
                const typesData = typesElement.dataset.types;
                if (typesData && typesData.trim() !== '') {
                    types = JSON.parse(typesData);
                }
            } catch (e) {
                console.error('Error parsing types data:', e);
                types = [];
            }
            
            const hasData = types && Array.isArray(types) && types.length > 0;
            const labels = hasData ? types.map(t => t.type || 'Unknown') : ['No Data Available'];
            const values = hasData ? types.map(t => parseInt(t.count) || 0) : [1];
            
            const typesOptions = {
                series: values,
                chart: {
                    type: 'donut',
                    height: 350,
                    toolbar: { show: false },
                    fontFamily: "Outfit, sans-serif",
                },
                labels: labels,
                colors: [primaryColor, secondaryColor, '#f59e0b', '#10b981', '#8b5cf6', '#ef4444', '#ec489a', '#06b6d4'],
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
                tooltip: { y: { formatter: (val) => val > 0 ? val.toLocaleString() + " records" : "No data" } }
            };
            
            if (typesElement.style.height === '' || typesElement.style.height === '0px') {
                typesElement.style.height = '350px';
            }
            if (typesElement.style.width === '' || typesElement.style.width === '0px') {
                typesElement.style.width = '100%';
            }
            
            if (window.marriageTypesChart && typeof window.marriageTypesChart.destroy === 'function') {
                window.marriageTypesChart.destroy();
            }
            window.marriageTypesChart = new ApexCharts(typesElement, typesOptions);
            window.marriageTypesChart.render();
            console.log('Marriage types chart rendered');
        } catch (error) {
            console.error('Error rendering marriage types chart:', error);
            typesElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #ef4444;">Error loading chart data</div>';
        }
    }
    
    // ============================================
    // Years Trend - Line Chart with Area
    // ============================================
    const yearsElement = document.querySelector("#yearlyTrendChart");
    if (yearsElement) {
        try {
            let years = [];
            try {
                const yearsData = yearsElement.dataset.years;
                if (yearsData && yearsData.trim() !== '') {
                    years = JSON.parse(yearsData);
                }
            } catch (e) {
                console.error('Error parsing years data:', e);
                years = [];
            }
            
            const hasData = years && Array.isArray(years) && years.length > 0;
            const yearLabels = hasData ? years.map(y => y.year) : ['No Data'];
            const uploads = hasData ? years.map(y => parseInt(y.uploads) || 0) : [0];
            const pages = hasData ? years.map(y => parseInt(y.pages) || 0) : [0];
            
            const maxUploads = Math.max(...uploads, 1);
            const maxPages = Math.max(...pages, 1);
            const yAxisMax = Math.max(calculateYAxisMax(maxUploads), calculateYAxisMax(maxPages));
            
            const yearsOptions = {
                series: [
                    { name: 'PDF Uploads', data: uploads },
                    { name: 'Total Pages', data: pages }
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
                    size: hasData ? 5 : 0,
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: { size: 7 }
                },
                xaxis: {
                    categories: yearLabels,
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' },
                    title: {
                        text: "Year",
                        style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                    },
                    labels: {
                        style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
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
            
            if (yearsElement.style.height === '' || yearsElement.style.height === '0px') {
                yearsElement.style.height = '350px';
            }
            if (yearsElement.style.width === '' || yearsElement.style.width === '0px') {
                yearsElement.style.width = '100%';
            }
            
            if (window.yearsTrendChart && typeof window.yearsTrendChart.destroy === 'function') {
                window.yearsTrendChart.destroy();
            }
            window.yearsTrendChart = new ApexCharts(yearsElement, yearsOptions);
            window.yearsTrendChart.render();
            console.log('Yearly trend chart rendered');
        } catch (error) {
            console.error('Error rendering yearly trend chart:', error);
            yearsElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #ef4444;">Error loading chart data</div>';
        }
    }
    
    // ============================================
    // County Marriages - Horizontal Bar Chart
    // ============================================
    const marriagesElement = document.querySelector("#countyMarriagesChart");
    if (marriagesElement) {
        try {
            let marriages = [];
            try {
                const marriagesData = marriagesElement.dataset.marriages;
                if (marriagesData && marriagesData.trim() !== '') {
                    marriages = JSON.parse(marriagesData);
                }
            } catch (e) {
                console.error('Error parsing marriages data:', e);
                marriages = [];
            }
            
            const hasData = marriages && Array.isArray(marriages) && marriages.length > 0;
            let countyNames = [];
            let marriageCounts = [];
            
            if (hasData) {
                countyNames = marriages.map(m => m.county || 'Unknown County');
                marriageCounts = marriages.map(m => parseInt(m.marriages) || 0);
            } else {
                countyNames = ['No Data Available'];
                marriageCounts = [0];
            }
            
            const maxCount = Math.max(...marriageCounts, 1);
            const yAxisMax = calculateYAxisMax(maxCount);
            
            const marriagesOptions = {
                series: [{ name: 'Marriages', data: marriageCounts }],
                colors: [secondaryColor],
                chart: {
                    type: 'bar',
                    height: 400,
                    toolbar: { show: false },
                    fontFamily: "Outfit, sans-serif",
                    animations: { enabled: true, speed: 800 }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '60%',
                        borderRadius: 8,
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: hasData,
                    formatter: (val) => val > 0 ? val.toLocaleString() : '',
                    offsetX: 10,
                    style: { fontSize: '12px', fontFamily: 'Outfit, sans-serif', colors: ['#333'], fontWeight: '600' }
                },
                xaxis: {
                    categories: countyNames,
                    title: {
                        text: "Number of Marriages",
                        style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                    },
                    labels: {
                        formatter: (value) => Math.round(value).toString(),
                        style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' }
                    },
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' },
                    min: 0,
                    max: yAxisMax,
                    tickAmount: 5
                },
                yaxis: {
                    title: {
                        text: "County",
                        style: { fontSize: "13px", fontFamily: 'Outfit, sans-serif', fontWeight: 500, color: '#64748b' }
                    },
                    labels: {
                        style: { fontSize: "12px", fontFamily: 'Outfit, sans-serif', colors: '#64748b' },
                        trim: true,
                        maxWidth: 120
                    },
                    axisBorder: { show: true, color: '#e5e7eb' },
                    axisTicks: { show: true, color: '#e5e7eb' }
                },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 5,
                    xaxis: { lines: { show: true } },
                    padding: { left: 10, right: 10 }
                },
                tooltip: { 
                    y: { 
                        formatter: (val) => val > 0 ? val.toLocaleString() + " marriages" : "No data" 
                    } 
                },
                legend: { show: false },
                noData: {
                    text: 'No marriage data available',
                    align: 'center',
                    verticalAlign: 'middle',
                    style: { fontSize: '14px', fontFamily: 'Outfit, sans-serif', color: '#64748b' }
                }
            };
            
            if (marriagesElement.style.height === '' || marriagesElement.style.height === '0px') {
                marriagesElement.style.height = '400px';
            }
            if (marriagesElement.style.width === '' || marriagesElement.style.width === '0px') {
                marriagesElement.style.width = '100%';
            }
            
            if (window.countyMarriagesChart && typeof window.countyMarriagesChart.destroy === 'function') {
                window.countyMarriagesChart.destroy();
            }
            window.countyMarriagesChart = new ApexCharts(marriagesElement, marriagesOptions);
            window.countyMarriagesChart.render();
            console.log('County marriages chart rendered successfully');
        } catch (error) {
            console.error('Error rendering county marriages chart:', error);
            marriagesElement.innerHTML = '<div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #ef4444;">Error loading chart data</div>';
        }
    }
    
    isInitialized = true;
};

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
                            csvContent += `"${row.county}",${row.pages}\n`;
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
                            csvContent += `"${row.type}",${row.count}\n`;
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
                            csvContent += `"${row.county}",${row.marriages}\n`;
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

export default attorneyGeneralCharts;
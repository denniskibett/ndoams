import ApexCharts from "apexcharts";

const uploadsBarChart = () => {
    const chartElement = document.querySelector("#uploadsBarChart");

    if (!chartElement) return;

    // Get data from data attributes
    let dates = [];
    let counts = [];
    
    try {
        dates = JSON.parse(chartElement.dataset.dates || "[]");
        counts = JSON.parse(chartElement.dataset.counts || "[]");
    } catch (e) {
        console.error("Error parsing chart data:", e);
        return;
    }

    // Don't render if no data
    if (!dates.length || !counts.length) {
        console.log("No chart data available");
        return;
    }

    const options = {
        series: [
            {
                name: "PDF Pages",
                data: counts,
            },
        ],
        colors: ["#465fff"],
        chart: {
            fontFamily: "Outfit, sans-serif",
            type: "bar",
            height: 180,
            toolbar: {
                show: false,
            },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "39%",
                borderRadius: 5,
                borderRadiusApplication: "end",
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            show: true,
            width: 4,
            colors: ["transparent"],
        },
        xaxis: {
            categories: dates,
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        legend: {
            show: true,
            position: "top",
            horizontalAlign: "left",
            fontFamily: "Outfit",
            markers: {
                radius: 99,
            },
        },
        yaxis: {
            title: false,
            labels: {
                formatter: function(val) {
                    return Math.round(val);
                }
            }
        },
        grid: {
            yaxis: {
                lines: {
                    show: true,
                },
            },
        },
        fill: {
            opacity: 1,
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " pages";
                },
            },
        },
    };

    // Destroy existing chart if it exists
    if (window.uploadChartInstance) {
        window.uploadChartInstance.destroy();
    }

    // Create new chart
    window.uploadChartInstance = new ApexCharts(chartElement, options);
    window.uploadChartInstance.render();
};

export default uploadsBarChart;
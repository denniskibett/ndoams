import ApexCharts from "apexcharts";

let isInitialized = false;

const uploadsBarChart = () => {
  // Prevent double initialization
  if (isInitialized) {
    console.log('Chart already initialized, skipping...');
    return;
  }
  
  const chartElement = document.querySelector("#uploadsBarChart");
  if (!chartElement) return;

  const dates = JSON.parse(chartElement.dataset.dates || "[]");
  const counts = JSON.parse(chartElement.dataset.counts || "[]");

  // Ensure we have valid data
  if (!dates.length || !counts.length) return;

  // Mark as initialized
  isInitialized = true;

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

  const maxCount = Math.max(...counts, 1);
  const yAxisMax = calculateYAxisMax(maxCount);

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
      height: 250,
      toolbar: { show: false },
      animations: {
        enabled: true,
        speed: 800,
      },
    },

    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: "39%",
        borderRadius: 5,
        borderRadiusApplication: "end",
        dataLabels: {
          position: 'top', // Position labels at the top of bars
        },
      },
    },

    dataLabels: {
      enabled: false, // Enable data labels
      offsetY: -20, // Position above the bar
      style: {
        fontSize: '12px',
        colors: ['#333'],
        fontWeight: '600',
      },
      formatter: function(val) {
        return val.toLocaleString();
      },
    },

    xaxis: {
      categories: dates,
      axisBorder: { show: true, color: '#e5e7eb' },
      axisTicks: { show: true, color: '#e5e7eb' },
      labels: {
        style: {
          fontSize: "12px",
          colors: '#64748b',
        },
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
      labels: {
        formatter: (value) => {
          return Math.round(value).toString();
        },
        style: {
          fontSize: "12px",
          colors: '#64748b',
        },
        align: 'right',
        padding: 5,
      },
      axisBorder: { show: true, color: '#e5e7eb' },
      axisTicks: { show: true, color: '#e5e7eb' },
      crosshairs: {
        show: true,
        position: 'back',
        stroke: {
          color: '#b6b6b6',
          width: 1,
          dashArray: 3,
        },
      },
    },

    grid: {
      show: true,
      borderColor: "#e5e7eb",
      strokeDashArray: 5,
      position: "back",
      xaxis: {
        lines: {
          show: false,
        },
      },
      yaxis: {
        lines: {
          show: true,
        },
      },
      padding: {
        left: 10,
        right: 10,
      },
    },

    legend: {
      show: true,
      position: "top",
      horizontalAlign: "left",
    },

    tooltip: {
      y: {
        formatter: (val) => Math.round(val).toLocaleString() + " pages",
      },
    },

    states: {
      hover: {
        filter: {
          type: 'lighten',
          value: 0.1,
        },
      },
    },
  };

  // Make sure container is visible and has dimensions
  if (chartElement.style.height === '' || chartElement.style.height === '0px') {
    chartElement.style.height = '250px';
  }
  if (chartElement.style.width === '' || chartElement.style.width === '0px') {
    chartElement.style.width = '100%';
  }

  // Destroy existing chart if it exists
  if (window.uploadChart) {
    window.uploadChart.destroy();
  }

  // Create chart
  window.uploadChart = new ApexCharts(chartElement, options);
  window.uploadChart.render();
  
  // Add method to update chart data dynamically
  window.uploadChart.updateChartData = (newDates, newCounts) => {
    const newMaxCount = Math.max(...newCounts, 1);
    const newYAxisMax = calculateYAxisMax(newMaxCount);
    
    window.uploadChart.updateOptions({
      xaxis: {
        categories: newDates,
      },
      yaxis: {
        max: newYAxisMax,
        labels: {
          formatter: (value) => {
            return Math.round(value).toString();
          }
        }
      },
    });

    window.uploadChart.updateSeries([{
      name: "PDF Pages",
      data: newCounts,
    }]);
  };
};

export default uploadsBarChart;
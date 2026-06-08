import ApexCharts from "apexcharts";

const dailyUploadsBarChart = () => {
  const chartElement = document.querySelector("#chartDailyUploadsBar");
  
  if (!chartElement) {
    console.log("Chart element not found");
    return;
  }

  // Get data from data attributes
  const dates = JSON.parse(chartElement.dataset.dates || '[]');
  const counts = JSON.parse(chartElement.dataset.counts || '[]');
  const primaryColor = chartElement.dataset.primaryColor || '#3A57E8';

  console.log('Bar chart data:', { dates, counts }); // Debug log

  // If no data, hide chart
  if (!dates.length || !counts.length) {
    chartElement.style.display = 'none';
    const parent = chartElement.closest('.overflow-hidden');
    if (parent) {
      const emptyDiv = document.createElement('div');
      emptyDiv.className = 'py-12 text-center text-gray-500 dark:text-gray-400';
      emptyDiv.textContent = 'No data available to display';
      parent.appendChild(emptyDiv);
    }
    return;
  }

  // Find the maximum value to set appropriate y-axis max
  const maxCount = Math.max(...counts);
  // Set y-axis max to either 10 or the max value rounded up to nearest 10
  const yAxisMax = maxCount <= 10 ? 10 : Math.ceil(maxCount / 10) * 10;

  // Chart options
  const chartOptions = {
    series: [
      {
        name: "Pages",
        data: counts,
      },
    ],
    colors: [primaryColor],
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
        borderRadiusWhenStacked: 'all',
        colors: {
            ranges: [{
                from: 0,
                to: 0,
                color: 'transparent' // Make zero values transparent
            }]
        }
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
      labels: {
        rotate: -45,
        rotateAlways: false,
        style: {
          fontSize: '10px',
          fontFamily: 'Outfit, sans-serif',
          colors: '#6B7280',
        },
      },
    },
    yaxis: {
      show: true,
      min: 0,
      max: yAxisMax,
      tickAmount: 5,
      title: {
        text: "Pages",
        style: {
          fontSize: '11px',
          fontFamily: 'Outfit, sans-serif',
          fontWeight: 400,
          color: '#6B7280',
        },
      },
      labels: {
        style: {
          fontSize: '10px',
          colors: ['#6B7280'],
        },
        formatter: function (val) {
          return Math.round(val);
        },
      },
      axisBorder: {
        show: false,
      },
      axisTicks: {
        show: false,
      },
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
      x: {
        show: false,
      },
      y: {
        formatter: function (val) {
          return val + " pages";
        },
      },
    },
    legend: {
      show: true,
      position: "top",
      horizontalAlign: "left",
      fontFamily: "Outfit",
      markers: {
        radius: 99,
        fillColors: [primaryColor],
      },
      itemMargin: {
        horizontal: 10,
        vertical: 0,
      },
    },
    states: {
      hover: {
        filter: {
          type: 'darken',
          value: 0.9,
        },
      },
    },
  };

  // Render chart
  const chart = new ApexCharts(chartElement, chartOptions);
  chart.render();
  
  // Store chart instance on the element for later access
  chartElement.chart = chart;
};

// Make download function globally available
window.downloadChart = function(type) {
  const chartElement = document.querySelector("#chartDailyUploadsBar");
  
  if (!chartElement || !chartElement.chart) {
    console.log("Chart not initialized");
    return;
  }

  const chart = chartElement.chart;

  if (type === "png") {
    chart.dataURI().then(({ imgURI }) => {
      const link = document.createElement("a");
      link.href = imgURI;
      link.download = "daily-uploads.png";
      link.click();
    });
  }

  if (type === "svg") {
    chart.dataURI().then(({ svgURI }) => {
      const link = document.createElement("a");
      link.href = svgURI;
      link.download = "daily-uploads.svg";
      link.click();
    });
  }

  if (type === "csv") {
    const dates = JSON.parse(chartElement.dataset.dates);
    const counts = JSON.parse(chartElement.dataset.counts);

    let csv = "Date,Pages\n";
    dates.forEach((d, i) => {
      csv += `${d},${counts[i]}\n`;
    });

    const blob = new Blob([csv], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);

    const a = document.createElement("a");
    a.href = url;
    a.download = "daily-uploads.csv";
    a.click();
    
    window.URL.revokeObjectURL(url);
  }
};

export default dailyUploadsBarChart;
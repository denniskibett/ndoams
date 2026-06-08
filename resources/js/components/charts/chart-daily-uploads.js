import ApexCharts from "apexcharts";

const dailyUploadsChart = () => {
  const chartElement = document.querySelector("#chartDailyUploads");
  
  if (!chartElement) return;
  
  // Get data from data attributes
  const dates = JSON.parse(chartElement.dataset.dates || '[]');
  const counts = JSON.parse(chartElement.dataset.counts || '[]');
  const primaryColor = chartElement.dataset.primaryColor || '#3A57E8';
  const secondaryColor = chartElement.dataset.secondaryColor || '#08B1BA';
  
  const chartOptions = {
    series: [
      {
        name: "Pages Uploaded",
        data: counts,
      },
    ],
    colors: [primaryColor],
    chart: {
      fontFamily: "Outfit, sans-serif",
      type: "area",
      height: 220,
      toolbar: {
        show: false,
      },
      sparkline: {
        enabled: false,
      },
      background: 'transparent',
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
      curve: "smooth",
      width: 2,
      colors: [primaryColor],
    },
    fill: {
      type: "gradient",
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.5,
        opacityTo: 0.2,
        stops: [0, 90, 100],
        colorStops: [
          {
            offset: 0,
            color: primaryColor,
            opacity: 0.5
          },
          {
            offset: 100,
            color: primaryColor,
            opacity: 0.1
          }
        ]
      },
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
      title: {
        text: "Number of Pages",
        style: {
          fontSize: '12px',
          fontFamily: 'Outfit, sans-serif',
          color: '#6B7280',
        },
      },
      min: 0,
      labels: {
        formatter: function (val) {
          return Math.round(val);
        },
        style: {
          colors: '#6B7280',
        },
      },
    },
    legend: {
      show: true,
      position: "top",
      horizontalAlign: "left",
      fontFamily: "Outfit",
      labels: {
        colors: '#6B7280',
      },
      markers: {
        radius: 99,
        fillColors: [primaryColor],
      },
    },
    grid: {
      borderColor: "#e5e7eb",
      strokeDashArray: 5,
      yaxis: {
        lines: {
          show: true,
        },
      },
      xaxis: {
        lines: {
          show: false,
        },
      },
    },
    tooltip: {
      theme: 'light',
      x: {
        format: 'dd MMM yyyy',
      },
      y: {
        formatter: function (val) {
          return val + " pages";
        },
      },
      style: {
        fontSize: '12px',
        fontFamily: 'Outfit, sans-serif',
      },
    },
    markers: {
      size: 3,
      colors: ["#ffffff"],
      strokeColors: primaryColor,
      strokeWidth: 2,
      hover: {
        size: 5,
      },
    },
  };

  const chart = new ApexCharts(chartElement, chartOptions);
  chart.render();
};

export default dailyUploadsChart;
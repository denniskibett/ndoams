import ApexCharts from "apexcharts";

const completionDonutChart = () => {
  const chartElement = document.querySelector("#completionDonutChart");
  if (!chartElement) return;

  const completed = parseInt(chartElement.dataset.completed || 0);
  const pending = parseInt(chartElement.dataset.pending || 0);

  const options = {
    series: [completed, pending],
    chart: { type: 'donut', height: 280, toolbar: { show: false } },
    labels: ['Completed', 'Pending'],
    colors: ['#10b981', '#f59e0b'],
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
    plotOptions: { pie: { donut: { size: '65%' } } }
  };

  const chart = new ApexCharts(chartElement, options);
  chart.render();
};

// Auto-initialize when DOM is ready (same pattern as uploads-bar-chart)
document.addEventListener("DOMContentLoaded", completionDonutChart);
export default completionDonutChart;
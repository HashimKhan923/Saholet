import Chart from 'chart.js/auto';

window.Chart = Chart;

Chart.defaults.font.family = "'Nunito', sans-serif";
Chart.defaults.color = '#64748b';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;

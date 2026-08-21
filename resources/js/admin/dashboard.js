import Chart from 'chart.js/auto';
import $ from 'jquery';

/**
 * Grocery Admin - Dashboard Analytics & Chart.js Controllers
 */

window.Admin = window.Admin || {};

window.Admin.initDashboard = function (salesDataset, orderStatusDataset) {
    const $salesCanvas = $('#salesOverviewChart');
    const $orderStatusCanvas = $('#orderStatusDonutChart');

    // Clean up previous instances if any
    if (window._salesChartInstance) {
        window._salesChartInstance.destroy();
        window._salesChartInstance = null;
    }
    if (window._orderStatusChartInstance) {
        window._orderStatusChartInstance.destroy();
        window._orderStatusChartInstance = null;
    }

    // 1. Initialize Sales Overview Line Chart
    if ($salesCanvas.length && salesDataset) {
        const ctx = $salesCanvas[0].getContext('2d');

        // Create emerald gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(22, 163, 74, 0.28)');
        gradient.addColorStop(1, 'rgba(22, 163, 74, 0.00)');

        const initialRange = '7days';
        const initialData = salesDataset[initialRange] || { labels: [], revenue: [], orders: [] };

        window._salesChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: initialData.labels,
                datasets: [
                    {
                        label: 'Sales ($)',
                        data: initialData.revenue,
                        borderColor: '#16a34a',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#16a34a',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Orders',
                        data: initialData.orders,
                        borderColor: '#0284c7',
                        borderWidth: 2,
                        borderDash: [4, 4],
                        fill: false,
                        tension: 0.35,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0284c7',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#cbd5e1',
                        padding: 12,
                        borderRadius: 10,
                        boxPadding: 4,
                        usePointStyle: true,
                        callbacks: {
                            label: function (context) {
                                if (context.dataset.label === 'Sales ($)') {
                                    return ` Revenue: $${context.raw.toLocaleString()}`;
                                }
                                return ` Orders: ${context.raw} orders`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, family: 'Instrument Sans' }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: {
                            color: '#f1f5f9',
                        },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 11, family: 'Instrument Sans' },
                            callback: function (val) {
                                return '$' + (val >= 1000 ? (val / 1000) + 'k' : val);
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: false,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });

        // Timeframe tabs click handler
        $(document).off('click', '[data-sales-range]').on('click', '[data-sales-range]', function (e) {
            e.preventDefault();
            const range = $(this).data('sales-range');
            const data = salesDataset[range];

            if (!data || !window._salesChartInstance) return;

            $('[data-sales-range]').removeClass('bg-white text-slate-900 shadow-xs font-semibold').addClass('text-slate-600 hover:text-slate-900');
            $(this).addClass('bg-white text-slate-900 shadow-xs font-semibold').removeClass('text-slate-600');

            window._salesChartInstance.data.labels = data.labels;
            window._salesChartInstance.data.datasets[0].data = data.revenue;
            window._salesChartInstance.data.datasets[1].data = data.orders;
            window._salesChartInstance.update();
        });
    }

    // 2. Initialize Order Status Donut Chart
    if ($orderStatusCanvas.length && orderStatusDataset) {
        const ctx = $orderStatusCanvas[0].getContext('2d');
        const breakdown = orderStatusDataset;

        const labels = ['Delivered', 'Processing', 'Pending', 'Cancelled'];
        const values = [
            breakdown.delivered ? breakdown.delivered.count : 0,
            breakdown.processing ? breakdown.processing.count : 0,
            breakdown.pending ? breakdown.pending.count : 0,
            breakdown.cancelled ? breakdown.cancelled.count : 0
        ];
        const colors = ['#16a34a', '#0284c7', '#f59e0b', '#e11d48'];

        window._orderStatusChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        borderRadius: 8,
                        callbacks: {
                            label: function (context) {
                                const total = values.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.raw / total) * 100).toFixed(1) : 0;
                                return ` ${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
};

// Auto-run if global datasets are already present
$(function () {
    if (window.salesChartDataset && window.orderStatusDataset) {
        window.Admin.initDashboard(window.salesChartDataset, window.orderStatusDataset);
    }
});

export default window.Admin;

document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de producción semanal
    const prodChart = document.getElementById('productionChart');
    if (prodChart) {
        new Chart(prodChart, {
            type: 'bar',
            data: {
                labels: JSON.parse(prodChart.dataset.labels || '[]'),
                datasets: [{
                    label: 'Planificado',
                    data: JSON.parse(prodChart.dataset.planned || '[]'),
                    backgroundColor: 'rgba(15, 52, 96, 0.7)',
                    borderColor: 'rgba(15, 52, 96, 1)',
                    borderWidth: 1
                }, {
                    label: 'Real',
                    data: JSON.parse(prodChart.dataset.actual || '[]'),
                    backgroundColor: 'rgba(233, 69, 96, 0.7)',
                    borderColor: 'rgba(233, 69, 96, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Gráfico de OEE
    const oeeChart = document.getElementById('oeeChart');
    if (oeeChart) {
        new Chart(oeeChart, {
            type: 'doughnut',
            data: {
                labels: ['Disponibilidad', 'Rendimiento', 'Calidad'],
                datasets: [{
                    data: JSON.parse(oeeChart.dataset.values || '[0,0,0]'),
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Gráfico de tendencia de producción
    const trendChart = document.getElementById('trendChart');
    if (trendChart) {
        new Chart(trendChart, {
            type: 'line',
            data: {
                labels: JSON.parse(trendChart.dataset.dates || '[]'),
                datasets: [{
                    label: 'Producción',
                    data: JSON.parse(trendChart.dataset.values || '[]'),
                    fill: true,
                    backgroundColor: 'rgba(15, 52, 96, 0.1)',
                    borderColor: 'rgba(15, 52, 96, 1)',
                    tension: 0.4,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Gráfico de materiales / stock
    const stockChart = document.getElementById('stockChart');
    if (stockChart) {
        new Chart(stockChart, {
            type: 'bar',
            data: {
                labels: JSON.parse(stockChart.dataset.names || '[]'),
                datasets: [{
                    label: 'Stock Actual',
                    data: JSON.parse(stockChart.dataset.stock || '[]'),
                    backgroundColor: 'rgba(233, 69, 96, 0.7)',
                    borderColor: 'rgba(233, 69, 96, 1)',
                    borderWidth: 1
                }, {
                    label: 'Punto Reorden',
                    data: JSON.parse(stockChart.dataset.reorder || '[]'),
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});

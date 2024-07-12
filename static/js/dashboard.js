document.addEventListener('DOMContentLoaded', function() {
    fetch('/get_dashboard_data', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error); });
        }
        return response.json();
    })
    .then(data => {
        updateDashboard(data);
    })
    .catch(error => {
        console.error('Error fetching dashboard data:', error);
    });

    function updateDashboard(data) {
        const fraudChartCtx = document.getElementById('fraudChart').getContext('2d');
        const typeChartCtx = document.getElementById('typeChart').getContext('2d');
        const typeFraudChartCtx = document.getElementById('typeFraudChart').getContext('2d');

        new Chart(fraudChartCtx, {
            type: 'doughnut',
            data: {
                labels: ['Fraudulent', 'Non-fraudulent'],
                datasets: [{
                    data: [data.fraudulent_count, data.non_fraudulent_count],
                    backgroundColor: ['#FF6384', '#36A2EB'],
                    hoverBackgroundColor: ['#FF6384', '#36A2EB']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Fraudulent vs Non-Fraudulent Transactions',
                        color: 'black'
                    },
                    legend: {
                        labels: {
                            color: 'black'
                        }
                    }
                }
            }
        });

        new Chart(typeChartCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(data.type_analysis),
                datasets: [{
                    label: 'Transactions by Type',
                    data: Object.values(data.type_analysis),
                    backgroundColor: '#4BC0C0'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Number of Transactions by Type',
                        color: 'black'
                    },
                    legend: {
                        labels: {
                            color: 'black'
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Transaction Type',
                            color: 'black'
                        },
                        ticks: {
                            color: 'black'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Transactions',
                            color: 'black'
                        },
                        ticks: {
                            color: 'black'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(typeFraudChartCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(data.type_fraud_analysis),
                datasets: [{
                    label: 'Fraudulent',
                    data: Object.values(data.type_fraud_analysis).map(v => v[1] || 0),
                    backgroundColor: '#FF6384'
                }, {
                    label: 'Non-Fraudulent',
                    data: Object.values(data.type_fraud_analysis).map(v => v[0] || 0),
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Fraudulent vs Non-Fraudulent Transactions by Type',
                        color: 'black'
                    },
                    legend: {
                        labels: {
                            color: 'black'
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Transaction Type',
                            color: 'black'
                        },
                        ticks: {
                            color: 'black'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Number of Transactions',
                            color: 'black'
                        },
                        ticks: {
                            color: 'black'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    document.getElementById('downloadReportButton').addEventListener('click', function() {
        window.location.href = '/download_report';
    });
});

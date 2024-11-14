function initializeCharts(data) {
    // Backlinks Status Distribution Chart
    const backlinkCtx = document.getElementById('backlinksChart').getContext('2d');
    new Chart(backlinkCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Pending', 'Removed'],
            datasets: [{
                data: data.backlinks_distribution,
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(234, 179, 8, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderColor: [
                    'rgb(34, 197, 94)',
                    'rgb(234, 179, 8)',
                    'rgb(239, 68, 68)'
                ],
                borderWidth: 1
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

    // User Registration Trend Chart
    const userCtx = document.getElementById('usersChart').getContext('2d');
    new Chart(userCtx, {
        type: 'line',
        data: {
            labels: data.registration_dates,
            datasets: [{
                label: 'New Users',
                data: data.registration_counts,
                fill: true,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
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
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}
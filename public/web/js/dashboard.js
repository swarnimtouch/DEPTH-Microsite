$(document).ready(function() {
    const profileInfo = $('.profile-info');
    const searchInput = $('#dashboardSearch');
    const searchableCards = $('.stat-card, .activity-item, .live-session-card, .chart-container').closest('.col-xl-3, .activity-item, .col-xl-8, .col-12');

    profileInfo.on('click', function(event) {
        event.stopPropagation();
        $(this).toggleClass('active');
    });

    $(document).on('click', function() {
        profileInfo.removeClass('active');
    });

    searchInput.on('input', function() {
        const query = $(this).val().trim().toLowerCase();

        searchableCards.each(function() {
            const item = $(this);
            const text = item.text().toLowerCase();
            item.toggle(!query || text.includes(query));
        });
    });

    const ctx = document.getElementById('quizChart');
    const chartData = window.dashboardQuizChart || { labels: [], scores: [] };
    
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels.length ? chartData.labels : ['No feedback submitted'],
                datasets: [
                    {
                        label: 'Your Score',
                        data: chartData.scores.length ? chartData.scores : [0],
                        backgroundColor: '#1ca477', 
                        borderRadius: 4, 
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false 
                    },
                    tooltip: {
                        backgroundColor: '#0b493c',
                        padding: 10,
                        titleFont: { family: 'Poppins', size: 13 },
                        bodyFont: { family: 'Poppins', size: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: 'Poppins', size: 11 },
                            color: '#a0aec0',
                            stepSize: 20
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: 'Poppins', size: 12 },
                            color: '#64748b'
                        }
                    }
                }
            }
        });
    }
});

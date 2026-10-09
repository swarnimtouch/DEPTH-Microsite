$(document).ready(function() {
    
    const ctx = document.getElementById('historyChart');
    
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Feedback 1', 'Feedback 2', 'Feedback 3', 'Feedback 4', 'Feedback 5', 'Feedback 6', 'Feedback 7'],
                datasets: [{
                    label: 'Score',
                    data: [75, 82, 70, 88, 85, 92, 95],
                    borderColor: '#1ca477', 
                    borderWidth: 2.5,
                    backgroundColor: 'rgba(28, 164, 119, 0.1)',
                    fill: true,
                    tension: 0.4, 
                    pointBackgroundColor: '#1ca477',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
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
                        bodyFont: { family: 'Poppins', size: 12 },
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Score: ' + context.parsed.y + '%';
                            }
                        }
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
                            font: { family: 'Poppins', size: 11 },
                            color: '#a0aec0'
                        }
                    }
                }
            }
        });
    }

    $('.custom-filter-pills .nav-link').click(function(e) {
        e.preventDefault();
        $('.custom-filter-pills .nav-link').removeClass('active bg-primary-green text-white border-0').addClass('bg-white text-muted border');
        
        $(this).removeClass('bg-white text-muted border').addClass('active bg-primary-green text-white border-0');
    });
});

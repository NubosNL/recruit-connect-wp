(function($) {
    'use strict';

    class RecruitDashboard {
        constructor() {
            this.charts = {};
            this.initializeCharts();
            this.initializeEventListeners();
            this.loadDashboardData();
        }

        initializeCharts() {
            // Applications Over Time Chart
            const applicationsCtx = document.getElementById('applicationsChart').getContext('2d');
            this.charts.applications = new Chart(applicationsCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: rcwpDashboard.strings.applications,
                        data: [],
                        borderColor: '#007bff',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Top Vacancies Chart
            const vacanciesCtx = document.getElementById('topVacanciesChart').getContext('2d');
            this.charts.vacancies = new Chart(vacanciesCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: rcwpDashboard.strings.applications,
                        data: [],
                        backgroundColor: '#28a745'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        initializeEventListeners() {
            $('#rcwp-export-btn').on('click', () => this.handleExport());

            $('.view-application').on('click', (e) => {
                const applicationId = $(e.currentTarget).data('id');
                this.viewApplication(applicationId);
            });
        }

        async loadDashboardData() {
            try {
                const response = await $.ajax({
                    url: rcwpDashboard.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'rcwp_get_dashboard_stats',
                        nonce: rcwpDashboard.nonce
                    }
                });

                if (response.success) {
                    this.updateCharts(response.data);
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }

        updateCharts(data) {
            // Update Applications Chart
            this.charts.applications.data.labels = data.applications_chart.labels;
            this.charts.applications.data.datasets[0].data = data.applications_chart.datasets[0].data;
            this.charts.applications.update();

            // Update Top Vacancies Chart
            this.charts.vacancies.data.labels = data.top_vacancies.labels;
            this.charts.vacancies.data.datasets[0].data = data.top_vacancies.datasets[0].data;
            this.charts.vacancies.update();
        }

        async handleExport() {
            const exportType = $('#rcwp-export-type').val();
            const dateFrom = $('#rcwp-export-date-from').val();
            const dateTo = $('#rcwp-export-date-to').val();

            try {
                const response = await $.ajax({
                    url: rcwpDashboard.ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'rcwp_export_applications',
                        nonce: rcwpDashboard.nonce,
                        export_type: exportType,
                        date_from: dateFrom,
                        date_to: dateTo
                    }
                });

                if (response.success) {
                    const blob = new Blob([response.data], { type: 'text/csv' });
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `rcwp-${exportType}-${new Date().toISOString().split('T')[0]}.csv`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                } else {
                    alert(rcwpDashboard.strings.exportError);
                }
            } catch (error) {
                console.error('Export error:', error);
                alert(rcwpDashboard.strings.exportError);
            }
        }

        async viewApplication(applicationId) {
            // Implementation for viewing application details
            // This could open a modal or redirect to a details page
        }
    }

    // Initialize dashboard when document is ready
    $(document).ready(() => {
        new RecruitDashboard();
    });

})(jQuery);

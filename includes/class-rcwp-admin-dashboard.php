<?php
class RCWP_Admin_Dashboard {
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
        add_action('admin_menu', array($this, 'add_dashboard_page'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));
        add_action('wp_ajax_rcwp_get_dashboard_stats', array($this, 'get_dashboard_stats'));
        add_action('wp_ajax_rcwp_export_applications', array($this, 'export_applications'));
    }

    public function add_dashboard_page() {
        add_submenu_page(
            'recruit-connect-wp',
            __('Dashboard', 'recruit-connect-wp'),
            __('Dashboard', 'recruit-connect-wp'),
            'manage_options',
            'rcwp-dashboard',
            array($this, 'render_dashboard_page')
        );
    }

    public function enqueue_dashboard_assets($hook) {
        if ('recruit-connect_page_rcwp-dashboard' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'rcwp-dashboard-css',
            RCWP_PLUGIN_URL . 'admin/css/dashboard.css',
            array(),
            RCWP_VERSION
        );

        wp_enqueue_script(
            'rcwp-chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js',
            array(),
            '3.7.0'
        );

        wp_enqueue_script(
            'rcwp-dashboard-js',
            RCWP_PLUGIN_URL . 'admin/js/dashboard.js',
            array('jquery', 'rcwp-chart-js'),
            RCWP_VERSION,
            true
        );

        wp_localize_script('rcwp-dashboard-js', 'rcwpDashboard', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rcwp-dashboard-nonce'),
            'strings' => array(
                'exportSuccess' => __('Export completed successfully!', 'recruit-connect-wp'),
                'exportError' => __('Error during export. Please try again.', 'recruit-connect-wp')
            )
        ));
    }

    public function render_dashboard_page() {
        $stats = $this->get_overview_stats();
        ?>
        <div class="wrap rcwp-dashboard">
            <h1><?php _e('Recruit Connect Dashboard', 'recruit-connect-wp'); ?></h1>

            <div class="rcwp-dashboard-grid">
                <!-- Overview Cards -->
                <div class="rcwp-stats-cards">
                    <div class="rcwp-stat-card">
                        <h3><?php _e('Active Vacancies', 'recruit-connect-wp'); ?></h3>
                        <div class="stat-value"><?php echo esc_html($stats['active_vacancies']); ?></div>
                    </div>
                    <div class="rcwp-stat-card">
                        <h3><?php _e('Total Applications', 'recruit-connect-wp'); ?></h3>
                        <div class="stat-value"><?php echo esc_html($stats['total_applications']); ?></div>
                    </div>
                    <div class="rcwp-stat-card">
                        <h3><?php _e('This Month', 'recruit-connect-wp'); ?></h3>
                        <div class="stat-value"><?php echo esc_html($stats['applications_this_month']); ?></div>
                    </div>
                    <div class="rcwp-stat-card">
                        <h3><?php _e('Success Rate', 'recruit-connect-wp'); ?></h3>
                        <div class="stat-value"><?php echo esc_html($stats['success_rate']); ?>%</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="rcwp-charts-grid">
                    <div class="rcwp-chart-card">
                        <h3><?php _e('Applications Over Time', 'recruit-connect-wp'); ?></h3>
                        <canvas id="applicationsChart"></canvas>
                    </div>
                    <div class="rcwp-chart-card">
                        <h3><?php _e('Top Vacancies', 'recruit-connect-wp'); ?></h3>
                        <canvas id="topVacanciesChart"></canvas>
                    </div>
                </div>

                <!-- Recent Applications -->
                <div class="rcwp-recent-applications">
                    <h3><?php _e('Recent Applications', 'recruit-connect-wp'); ?></h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Date', 'recruit-connect-wp'); ?></th>
                                <th><?php _e('Applicant', 'recruit-connect-wp'); ?></th>
                                <th><?php _e('Vacancy', 'recruit-connect-wp'); ?></th>
                                <th><?php _e('Status', 'recruit-connect-wp'); ?></th>
                                <th><?php _e('Actions', 'recruit-connect-wp'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $this->render_recent_applications(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Export Section -->
                <div class="rcwp-export-section">
                    <h3><?php _e('Export Data', 'recruit-connect-wp'); ?></h3>
                    <div class="rcwp-export-controls">
                        <select id="rcwp-export-type">
                            <option value="applications"><?php _e('Applications', 'recruit-connect-wp'); ?></option>
                            <option value="vacancies"><?php _e('Vacancies', 'recruit-connect-wp'); ?></option>
                            <option value="statistics"><?php _e('Statistics', 'recruit-connect-wp'); ?></option>
                        </select>
                        <input type="date" id="rcwp-export-date-from" />
                        <input type="date" id="rcwp-export-date-to" />
                        <button class="button button-primary" id="rcwp-export-btn">
                            <?php _e('Export', 'recruit-connect-wp'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_overview_stats() {
        global $wpdb;

        $active_vacancies = wp_count_posts('vacancy')->publish;

        $total_applications = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}rcwp_applications"
        );

        $applications_this_month = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}rcwp_applications 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        ));

        $successful_applications = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}rcwp_applications 
            WHERE status = %s",
            'submitted'
        ));

        $success_rate = $total_applications > 0
            ? round(($successful_applications / $total_applications) * 100, 1)
            : 0;

        return array(
            'active_vacancies' => $active_vacancies,
            'total_applications' => $total_applications,
            'applications_this_month' => $applications_this_month,
            'success_rate' => $success_rate
        );
    }

    private function render_recent_applications() {
        global $wpdb;

        $applications = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}rcwp_applications 
            ORDER BY created_at DESC 
            LIMIT 10"
        );

        foreach ($applications as $application) {
            $vacancy = get_post($application->vacancy_id);
            ?>
            <tr>
                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($application->created_at))); ?></td>
                <td><?php echo esc_html($application->first_name . ' ' . $application->last_name); ?></td>
                <td><?php echo $vacancy ? esc_html($vacancy->post_title) : __('Deleted Vacancy', 'recruit-connect-wp'); ?></td>
                <td>
                    <span class="rcwp-status-badge status-<?php echo esc_attr($application->status); ?>">
                        <?php echo esc_html(ucfirst($application->status)); ?>
                    </span>
                </td>
                <td>
                    <button class="button button-small view-application"
                            data-id="<?php echo esc_attr($application->id); ?>">
                        <?php _e('View', 'recruit-connect-wp'); ?>
                    </button>
                </td>
            </tr>
            <?php
        }
    }

    public function get_dashboard_stats() {
        check_ajax_referer('rcwp-dashboard-nonce', 'nonce');

        $stats = array(
            'applications_chart' => $this->get_applications_chart_data(),
            'top_vacancies' => $this->get_top_vacancies_data()
        );

        wp_send_json_success($stats);
    }

    private function get_applications_chart_data() {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM {$wpdb->prefix}rcwp_applications 
            WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY) 
            GROUP BY DATE(created_at) 
            ORDER BY date"
        );

        $data = array(
            'labels' => array(),
            'datasets' => array(
                array(
                    'label' => __('Applications', 'recruit-connect-wp'),
                    'data' => array()
                )
            )
        );

        foreach ($results as $row) {
            $data['labels'][] = date_i18n(get_option('date_format'), strtotime($row->date));
            $data['datasets'][0]['data'][] = (int) $row->count;
        }

        return $data;
    }

    private function get_top_vacancies_data() {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT vacancy_id, COUNT(*) as count 
            FROM {$wpdb->prefix}rcwp_applications 
            GROUP BY vacancy_id 
            ORDER BY count DESC 
            LIMIT 5"
        );

        $data = array(
            'labels' => array(),
            'datasets' => array(
                array(
                    'label' => __('Applications', 'recruit-connect-wp'),
                    'data' => array()
                )
            )
        );

        foreach ($results as $row) {
            $vacancy = get_post($row->vacancy_id);
            if ($vacancy) {
                $data['labels'][] = $vacancy->post_title;
                $data['datasets'][0]['data'][] = (int) $row->count;
            }
        }

        return $data;
    }

    public function export_applications() {
        check_ajax_referer('rcwp-dashboard-nonce', 'nonce');

        $export_type = $_POST['export_type'] ?? 'applications';
        $date_from = $_POST['date_from'] ?? '';
        $date_to = $_POST['date_to'] ?? '';

        try {
            $data = $this->get_export_data($export_type, $date_from, $date_to);
            $filename = "rcwp-{$export_type}-" . date('Y-m-d') . '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys(reset($data)));

            foreach ($data as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function get_export_data($type, $date_from, $date_to) {
        global $wpdb;

        $where = array('1=1');
        $prepare = array();

        if ($date_from) {
            $where[] = 'created_at >= %s';
            $prepare[] = $date_from;
        }
        if ($date_to) {
            $where[] = 'created_at <= %s';
            $prepare[] = $date_to;
        }

        $where_clause = implode(' AND ', $where);

        switch ($type) {
            case 'applications':
                $query = "SELECT * FROM {$wpdb->prefix}rcwp_applications WHERE $where_clause";
                break;

            case 'vacancies':
                // Add vacancy export logic
                break;

            case 'statistics':
                // Add statistics export logic
                break;

            default:
                throw new Exception(__('Invalid export type', 'recruit-connect-wp'));
        }

        return $wpdb->get_results($wpdb->prepare($query, $prepare), ARRAY_A);
    }
}

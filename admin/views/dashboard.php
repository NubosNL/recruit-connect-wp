<?php
/**
 * Admin dashboard view
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get statistics
$stats = RCWP_Admin_Dashboard::get_statistics();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="rcwp-dashboard-wrapper">
        <!-- Statistics Cards -->
        <div class="rcwp-stats-grid">
            <div class="rcwp-stat-card">
                <h3><?php _e('Total Vacancies', 'recruit-connect-wp'); ?></h3>
                <div class="stat-number"><?php echo esc_html($stats['total_vacancies']); ?></div>
            </div>
            <div class="rcwp-stat-card">
                <h3><?php _e('Active Vacancies', 'recruit-connect-wp'); ?></h3>
                <div class="stat-number"><?php echo esc_html($stats['active_vacancies']); ?></div>
            </div>
            <div class="rcwp-stat-card">
                <h3><?php _e('Applications (30 days)', 'recruit-connect-wp'); ?></h3>
                <div class="stat-number"><?php echo esc_html($stats['recent_applications']); ?></div>
            </div>
            <div class="rcwp-stat-card">
                <h3><?php _e('Last Sync', 'recruit-connect-wp'); ?></h3>
                <div class="stat-number"><?php echo esc_html($stats['last_sync']); ?></div>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="rcwp-dashboard-section">
            <h2><?php _e('Recent Applications', 'recruit-connect-wp'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Applicant', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Vacancy', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Status', 'recruit-connect-wp'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_applications_list'] as $application) : ?>
                        <tr>
                            <td><?php echo esc_html($application->created_at); ?></td>
                            <td><?php echo esc_html($application->applicant_name); ?></td>
                            <td><?php echo esc_html($application->vacancy_title); ?></td>
                            <td><?php echo esc_html($application->status); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- System Status -->
        <div class="rcwp-dashboard-section">
            <h2><?php _e('System Status', 'recruit-connect-wp'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <tbody>
                    <tr>
                        <td><?php _e('XML Feed Status', 'recruit-connect-wp'); ?></td>
                        <td>
                            <?php if ($stats['xml_feed_status']) : ?>
                                <span class="status-ok"><?php _e('Connected', 'recruit-connect-wp'); ?></span>
                            <?php else : ?>
                                <span class="status-error"><?php _e('Error', 'recruit-connect-wp'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('License Status', 'recruit-connect-wp'); ?></td>
                        <td>
                            <?php if ($stats['license_status']) : ?>
                                <span class="status-ok"><?php _e('Active', 'recruit-connect-wp'); ?></span>
                            <?php else : ?>
                                <span class="status-error"><?php _e('Inactive', 'recruit-connect-wp'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php _e('Next Scheduled Sync', 'recruit-connect-wp'); ?></td>
                        <td><?php echo esc_html($stats['next_sync']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Recent Logs -->
        <div class="rcwp-dashboard-section">
            <h2><?php _e('Recent Logs', 'recruit-connect-wp'); ?></h2>
            <div class="rcwp-logs-wrapper">
                <?php foreach ($stats['recent_logs'] as $log) : ?>
                    <div class="log-entry <?php echo esc_attr($log->level); ?>">
                        <span class="log-time"><?php echo esc_html($log->created_at); ?></span>
                        <span class="log-level"><?php echo esc_html(ucfirst($log->level)); ?></span>
                        <span class="log-message"><?php echo esc_html($log->message); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

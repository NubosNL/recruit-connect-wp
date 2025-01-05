<?php
if (!defined('ABSPATH')) exit;

// Get statistics
$total_vacancies = wp_count_posts('vacancy')->publish;
$recent_applications = get_posts(array(
	'post_type' => 'vacancy',
	'posts_per_page' => 5,
	'orderby' => 'date',
	'order' => 'DESC'
));
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="rcwp-dashboard-wrapper">
        <!-- Statistics Cards -->
        <div class="rcwp-stats-grid">
            <div class="rcwp-stat-card">
                <h3><?php _e('Total Vacancies', 'recruit-connect-wp'); ?></h3>
                <div class="stat-number"><?php echo esc_html($total_vacancies); ?></div>
            </div>
            <!-- Add more stat cards as needed -->
        </div>

        <!-- Recent Vacancies -->
        <div class="rcwp-dashboard-section">
            <h2><?php _e('Recent Vacancies', 'recruit-connect-wp'); ?></h2>
			<?php if ($recent_applications): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                    <tr>
                        <th><?php _e('Title', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Date', 'recruit-connect-wp'); ?></th>
                        <th><?php _e('Status', 'recruit-connect-wp'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php foreach ($recent_applications as $vacancy): ?>
                        <tr>
                            <td>
                                <a href="<?php echo get_edit_post_link($vacancy->ID); ?>">
									<?php echo esc_html($vacancy->post_title); ?>
                                </a>
                            </td>
                            <td><?php echo get_the_date('', $vacancy->ID); ?></td>
                            <td><?php echo get_post_status($vacancy->ID); ?></td>
                        </tr>
					<?php endforeach; ?>
                    </tbody>
                </table>
			<?php else: ?>
                <p><?php _e('No vacancies found.', 'recruit-connect-wp'); ?></p>
			<?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="rcwp-dashboard-section">
            <h2><?php _e('Quick Actions', 'recruit-connect-wp'); ?></h2>
            <div class="rcwp-quick-actions">
                <a href="<?php echo admin_url('admin.php?page=recruit-connect-wp-settings'); ?>" class="button button-primary">
					<?php _e('Configure Settings', 'recruit-connect-wp'); ?>
                </a>
                <a href="#" class="button" id="rcwp-sync-now">
					<?php _e('Sync Vacancies', 'recruit-connect-wp'); ?>
                </a>
            </div>
        </div>
    </div>
</div>

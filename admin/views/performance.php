<?php
if (!defined('ABSPATH')) exit;

$performance_metrics = $this->performance->get_metrics();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="rcwp-performance-wrapper">
        <!-- Performance Metrics -->
        <div class="rcwp-metrics-grid">
            <div class="rcwp-metric-card">
                <h3><?php _e('Average Load Time', 'recruit-connect-wp'); ?></h3>
                <div class="metric-value"><?php echo esc_html($performance_metrics['average_load_time']); ?>s</div>
            </div>
            <div class="rcwp-metric-card">
                <h3><?php _e('Cache Hit Rate', 'recruit-connect-wp'); ?></h3>
                <div class="metric-value"><?php echo esc_html($performance_metrics['cache_hit_rate']); ?>%</div>
            </div>
            <!-- Add more metric cards -->
        </div>

        <!-- Performance Settings -->
        <div class="rcwp-settings-section">
            <h2><?php _e('Performance Settings', 'recruit-connect-wp'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('rcwp_performance_settings');
                do_settings_sections('rcwp_performance_settings');
                submit_button();
                ?>
            </form>
        </div>
    </div>
</div>

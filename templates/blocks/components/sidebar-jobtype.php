<?php
/**
 * Job Type sidebar component
 */
if (!defined('ABSPATH')) exit;

$jobtype = get_post_meta($args['vacancy_id'], '_vacancy_jobtype', true);
if ($jobtype): ?>
    <div class="sidebar-section">
        <h3><?php _e('Job Type', 'recruit-connect-wp'); ?></h3>
        <p><?php echo esc_html($jobtype); ?></p>
    </div>
<?php endif; ?>

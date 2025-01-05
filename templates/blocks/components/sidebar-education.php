<?php
/**
 * Education sidebar component
 */
if (!defined('ABSPATH')) exit;

$education = get_post_meta($args['vacancy_id'], '_vacancy_education', true);
if ($education): ?>
    <div class="sidebar-section">
        <h3><?php _e('Education', 'recruit-connect-wp'); ?></h3>
        <p><?php echo esc_html($education); ?></p>
    </div>
<?php endif; ?>

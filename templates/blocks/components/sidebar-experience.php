<?php
/**
 * Experience sidebar component
 */
if (!defined('ABSPATH')) exit;

$experience = get_post_meta($args['vacancy_id'], '_vacancy_experience', true);
if ($experience): ?>
    <div class="sidebar-section">
        <h3><?php _e('Experience', 'recruit-connect-wp'); ?></h3>
        <p><?php echo esc_html($experience); ?></p>
    </div>
<?php endif; ?>

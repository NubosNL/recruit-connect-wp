<?php
/**
 * Company meta component
 */
if (!defined('ABSPATH')) exit;

$company = get_post_meta($args['vacancy_id'], '_vacancy_company', true);
if ($company): ?>
    <div class="meta-item company">
        <i class="fas fa-building"></i>
        <span><?php echo esc_html($company); ?></span>
    </div>
<?php endif; ?>

<?php
/**
 * Category filter component
 */
if (!defined('ABSPATH')) exit;

$categories = RCWP_Search::get_vacancy_categories();
?>

<div class="rcwp-filter-group">
    <h4><?php _e('Category', 'recruit-connect-wp'); ?></h4>
    <select id="rcwp-category-filter" class="rcwp-filter">
        <option value=""><?php _e('All Categories', 'recruit-connect-wp'); ?></option>
        <?php foreach ($categories as $category): ?>
            <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
        <?php endforeach; ?>
    </select>
</div>

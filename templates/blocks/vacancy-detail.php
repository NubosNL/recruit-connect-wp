<?php
/**
 * Block template for displaying vacancy detail
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Get block attributes
$className = isset($attributes['className']) ? $attributes['className'] : '';
$vacancy_id = isset($attributes['vacancyId']) ? $attributes['vacancyId'] : get_the_ID();

// Get enabled detail page fields
$detail_fields = get_option('rcwp_detail_page_fields', array());

// Get vacancy data
$vacancy = get_post($vacancy_id);
if (!$vacancy || $vacancy->post_type !== 'vacancy') {
    return;
}

?>
<div class="wp-block-rcwp-vacancy-detail <?php echo esc_attr($className); ?>">
    <article class="rcwp-single-vacancy">
        <div class="vacancy-main-content">
            <?php if (!empty($detail_fields['title'])): ?>
                <header class="vacancy-header">
                    <h1 class="vacancy-title"><?php echo esc_html($vacancy->post_title); ?></h1>

                    <div class="vacancy-meta">
                        <?php foreach (['company', 'location', 'salary'] as $meta_field): ?>
                            <?php if (!empty($detail_fields[$meta_field])): ?>
                                <?php get_template_part('templates/blocks/components/meta', $meta_field, array('vacancy_id' => $vacancy_id)); ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </header>
            <?php endif; ?>

            <?php if (!empty($detail_fields['description'])): ?>
                <div class="vacancy-description">
                    <?php echo wp_kses_post($vacancy->post_content); ?>
                </div>
            <?php endif; ?>

            <div class="vacancy-apply">
                <a href="#application-form" class="rcwp-button rcwp-button-primary">
                    <?php _e('Apply Now', 'recruit-connect-wp'); ?>
                </a>
            </div>
        </div>

        <div class="vacancy-sidebar">
            <?php foreach (['jobtype', 'education', 'experience', 'recruiter'] as $sidebar_field): ?>
                <?php if (!empty($detail_fields[$sidebar_field])): ?>
                    <?php get_template_part('templates/blocks/components/sidebar', $sidebar_field, array('vacancy_id' => $vacancy_id)); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </article>
</div>

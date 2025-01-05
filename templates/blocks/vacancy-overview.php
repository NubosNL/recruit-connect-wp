<?php
/**
 * Block template for displaying vacancy overview
 *
 * @package    Recruit_Connect_WP
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// Get block attributes
$className = isset($attributes['className']) ? $attributes['className'] : '';
$limit = isset($attributes['limit']) ? intval($attributes['limit']) : 10;

// Get enabled search components
$search_components = get_option('rcwp_search_components', array(
	'category'  => true,
	'education' => true,
	'jobtype'   => true,
	'salary'    => true
));
?>

<div class="wp-block-rcwp-vacancy-overview <?php echo esc_attr($className); ?>">
	<div class="rcwp-vacancies-wrapper">
		<div class="rcwp-row">
			<!-- Search and Filters -->
			<div class="rcwp-col-md-3">
				<div class="rcwp-filters">
					<!-- Search Bar -->
					<div class="rcwp-search-bar">
						<input type="text"
						       id="rcwp-search-input"
						       placeholder="<?php esc_attr_e('Search vacancies...', 'recruit-connect-wp'); ?>">
					</div>

					<!-- Filters -->
					<?php foreach ($search_components as $component => $enabled): ?>
						<?php if ($enabled): ?>
							<?php get_template_part('templates/blocks/components/filter', $component); ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Vacancies List -->
			<div class="rcwp-col-md-9">
				<div id="rcwp-vacancies-list" data-limit="<?php echo esc_attr($limit); ?>">
					<div class="rcwp-vacancies-grid"></div>
					<div class="rcwp-loading" style="display: none;">
						<div class="rcwp-spinner"></div>
					</div>
					<div class="rcwp-load-more" style="display: none;">
						<button class="rcwp-button">
							<?php _e('Load More', 'recruit-connect-wp'); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

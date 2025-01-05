<?php
/**
 * The template for displaying vacancy archives
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 */

get_header();

// Get enabled search components
$search_components = get_option('rcwp_search_components', array(
	'category'  => true,
	'education' => true,
	'jobtype'   => true,
	'salary'    => true
));
?>

    <div class="rcwp-vacancies-wrapper">
        <div class="container">
            <div class="row">
                <!-- Search and Filters Sidebar -->
                <div class="col-md-3">
                    <div class="rcwp-filters">
                        <!-- Search Bar -->
                        <div class="rcwp-search-bar">
                            <input type="text"
                                   id="rcwp-search-input"
                                   placeholder="<?php esc_attr_e('Search vacancies...', 'recruit-connect-wp'); ?>"
                                   value="<?php echo esc_attr(get_query_var('s')); ?>">
                        </div>

                        <!-- Category Filter -->
						<?php if (!empty($search_components['category'])): ?>
                            <div class="rcwp-filter-group">
                                <h4><?php _e('Category', 'recruit-connect-wp'); ?></h4>
                                <select id="rcwp-category-filter">
                                    <option value=""><?php _e('All Categories', 'recruit-connect-wp'); ?></option>
									<?php
									$categories = RCWP_Search::get_vacancy_categories();
									foreach ($categories as $category) {
										echo sprintf(
											'<option value="%s" %s>%s</option>',
											esc_attr($category),
											selected($category, get_query_var('category'), false),
											esc_html($category)
										);
									}
									?>
                                </select>
                            </div>
						<?php endif; ?>

                        <!-- Education Filter -->
						<?php if (!empty($search_components['education'])): ?>
                            <div class="rcwp-filter-group">
                                <h4><?php _e('Education', 'recruit-connect-wp'); ?></h4>
                                <select id="rcwp-education-filter">
                                    <option value=""><?php _e('All Education Levels', 'recruit-connect-wp'); ?></option>
									<?php
									$education_levels = RCWP_Search::get_vacancy_education_levels();
									foreach ($education_levels as $level) {
										echo sprintf(
											'<option value="%s" %s>%s</option>',
											esc_attr($level),
											selected($level, get_query_var('education'), false),
											esc_html($level)
										);
									}
									?>
                                </select>
                            </div>
						<?php endif; ?>

                        <!-- Job Type Filter -->
						<?php if (!empty($search_components['jobtype'])): ?>
                            <div class="rcwp-filter-group">
                                <h4><?php _e('Job Type', 'recruit-connect-wp'); ?></h4>
                                <select id="rcwp-jobtype-filter">
                                    <option value=""><?php _e('All Job Types', 'recruit-connect-wp'); ?></option>
									<?php
									$job_types = RCWP_Search::get_vacancy_job_types();
									foreach ($job_types as $type) {
										echo sprintf(
											'<option value="%s" %s>%s</option>',
											esc_attr($type),
											selected($type, get_query_var('jobtype'), false),
											esc_html($type)
										);
									}
									?>
                                </select>
                            </div>
						<?php endif; ?>

                        <!-- Salary Range Filter -->
						<?php if (!empty($search_components['salary'])): ?>
                            <div class="rcwp-filter-group">
                                <h4><?php _e('Salary Range', 'recruit-connect-wp'); ?></h4>
                                <div id="rcwp-salary-slider"></div>
                                <div class="salary-inputs">
                                    <input type="number" id="salary-min" readonly>
                                    <input type="number" id="salary-max" readonly>
                                </div>
                            </div>
						<?php endif; ?>
                    </div>
                </div>

                <!-- Vacancies List -->
                <div class="col-md-9">
                    <div id="rcwp-vacancies-list">
						<?php if (have_posts()) : ?>
                            <div class="rcwp-vacancies-grid">
								<?php while (have_posts()) : the_post(); ?>
                                    <article id="vacancy-<?php the_ID(); ?>" <?php post_class('rcwp-vacancy-card'); ?>>
                                        <header class="vacancy-header">
                                            <h2 class="vacancy-title">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h2>
                                            <div class="vacancy-meta">
												<?php
												$company = get_post_meta(get_the_ID(), '_vacancy_company', true);
												$location = get_post_meta(get_the_ID(), '_vacancy_city', true);
												$salary = get_post_meta(get_the_ID(), '_vacancy_salary', true);
												?>
												<?php if ($company) : ?>
                                                    <span class="company"><?php echo esc_html($company); ?></span>
												<?php endif; ?>
												<?php if ($location) : ?>
                                                    <span class="location"><?php echo esc_html($location); ?></span>
												<?php endif; ?>
												<?php if ($salary) : ?>
                                                    <span class="salary"><?php echo esc_html($salary); ?></span>
												<?php endif; ?>
                                            </div>
                                        </header>

                                        <div class="vacancy-excerpt">
											<?php the_excerpt(); ?>
                                        </div>

                                        <footer class="vacancy-footer">
                                            <a href="<?php the_permalink(); ?>" class="rcwp-button">
												<?php _e('View Details', 'recruit-connect-wp'); ?>
                                            </a>
                                        </footer>
                                    </article>
								<?php endwhile; ?>
                            </div>

                            <!-- Load More Button -->
							<?php if ($wp_query->max_num_pages > 1) : ?>
                                <div class="rcwp-load-more">
                                    <button id="rcwp-load-more" class="rcwp-button"
                                            data-page="1"
                                            data-max="<?php echo esc_attr($wp_query->max_num_pages); ?>">
										<?php _e('Load More', 'recruit-connect-wp'); ?>
                                    </button>
                                </div>
							<?php endif; ?>

						<?php else : ?>
                            <div class="rcwp-no-results">
                                <p><?php _e('No vacancies found matching your criteria.', 'recruit-connect-wp'); ?></p>
                            </div>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>

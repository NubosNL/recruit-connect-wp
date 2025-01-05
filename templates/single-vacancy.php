<?php
/**
 * The template for displaying single vacancy
 *
 * @link       https://www.nubos.nl/en
 * @since      1.0.0
 *
 * @package    Recruit_Connect_WP
 */

get_header();

// Get enabled detail page fields and their order
$detail_fields = get_option('rcwp_detail_page_fields', array());
?>

    <article id="vacancy-<?php the_ID(); ?>" <?php post_class('rcwp-single-vacancy'); ?>>
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <!-- Main Content -->
                    <div class="vacancy-main-content">
                        <header class="vacancy-header">
							<?php if (!empty($detail_fields['title'])) : ?>
                                <h1 class="vacancy-title"><?php the_title(); ?></h1>
							<?php endif; ?>

                            <div class="vacancy-meta">
								<?php if (!empty($detail_fields['company'])) : ?>
									<?php $company = get_post_meta(get_the_ID(), '_vacancy_company', true); ?>
									<?php if ($company) : ?>
                                        <div class="meta-item company">
                                            <i class="fas fa-building"></i>
                                            <span><?php echo esc_html($company); ?></span>
                                        </div>
									<?php endif; ?>
								<?php endif; ?>

								<?php if (!empty($detail_fields['location'])) : ?>
									<?php
									$city = get_post_meta(get_the_ID(), '_vacancy_city', true);
									$country = get_post_meta(get_the_ID(), '_vacancy_country', true);
									?>
									<?php if ($city || $country) : ?>
                                        <div class="meta-item location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo esc_html(implode(', ', array_filter(array($city, $country)))); ?></span>
                                        </div>
									<?php endif; ?>
								<?php endif; ?>

								<?php if (!empty($detail_fields['salary'])) : ?>
									<?php $salary = get_post_meta(get_the_ID(), '_vacancy_salary', true); ?>
									<?php if ($salary) : ?>
                                        <div class="meta-item salary">
                                            <i class="fas fa-euro-sign"></i>
                                            <span><?php echo esc_html($salary); ?></span>
                                        </div>
									<?php endif; ?>
								<?php endif; ?>
                            </div>
                        </header>

						<?php if (!empty($detail_fields['description'])) : ?>
                            <div class="vacancy-description">
								<?php the_content(); ?>
                            </div>
						<?php endif; ?>

                        <!-- Apply Button -->
                        <div class="vacancy-apply">
                            <a href="#application-form" class="rcwp-button rcwp-button-primary">
								<?php _e('Apply Now', 'recruit-connect-wp'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Sidebar -->
                    <div class="vacancy-sidebar">
						<?php if (!empty($detail_fields['jobtype'])) : ?>
							<?php $jobtype = get_post_meta(get_the_ID(), '_vacancy_jobtype', true); ?>
							<?php if ($jobtype) : ?>
                                <div class="sidebar-section">
                                    <h3><?php _e('Job Type', 'recruit-connect-wp'); ?></h3>
                                    <p><?php echo esc_html($jobtype); ?></p>
                                </div>
							<?php endif; ?>
						<?php endif; ?>

						<?php if (!empty($detail_fields['education'])) : ?>
							<?php $education = get_post_meta(get_the_ID(), '_vacancy_education', true); ?>
							<?php if ($education) : ?>
                                <div class="sidebar-section">
                                    <h3><?php _e('Education', 'recruit-connect-wp'); ?></h3>
                                    <p><?php echo esc_html($education); ?></p>
                                </div>
							<?php endif; ?>
						<?php endif; ?>

						<?php if (!empty($detail_fields['experience'])) : ?>
							<?php $experience = get_post_meta(get_the_ID(), '_vacancy_experience', true); ?>
							<?php if ($experience) : ?>
                                <div class="sidebar-section">
                                    <h3><?php _e('Experience', 'recruit-connect-wp'); ?></h3>
                                    <p><?php echo esc_html($experience); ?></p>
                                </div>
							<?php endif; ?>
						<?php endif; ?>

						<?php if (!empty($detail_fields['recruiter'])) : ?>
							<?php
							$recruiter_name = get_post_meta(get_the_ID(), '_vacancy_recruitername', true);
							$recruiter_email = get_post_meta(get_the_ID(), '_vacancy_recruiteremail', true);
							$recruiter_image = get_post_meta(get_the_ID(), '_vacancy_recruiterimage', true);
							?>
							<?php if ($recruiter_name || $recruiter_email) : ?>
                                <div class="sidebar-section recruiter-info">
                                    <h3><?php _e('Recruiter', 'recruit-connect-wp'); ?></h3>
									<?php if ($recruiter_image) : ?>
                                        <img src="<?php echo esc_url($recruiter_image); ?>" alt="<?php echo esc_attr($recruiter_name); ?>" class="recruiter-image">
									<?php endif; ?>
									<?php if ($recruiter_name) : ?>
                                        <p class="recruiter-name"><?php echo esc_html($recruiter_name); ?></p>
									<?php endif; ?>
									<?php if ($recruiter_email) : ?>
                                        <p class="recruiter-email">
                                            <a href="mailto:<?php echo esc_attr($recruiter_email); ?>">
												<?php echo esc_html($recruiter_email); ?>
                                            </a>
                                        </p>
									<?php endif; ?>
                                </div>
							<?php endif; ?>
						<?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Application Form -->
            <div class="row">
                <div class="col-md-8">
                    <div id="application-form" class="vacancy-application-form">
                        <h2><?php _e('Apply for this position', 'recruit-connect-wp'); ?></h2>
						<?php echo do_shortcode('[recruit_connect_application_form]'); ?>
                    </div>
                </div>
            </div>
        </div>
    </article>

<?php get_footer(); ?>

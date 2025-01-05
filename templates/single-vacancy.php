<?php
/**
 * Template for displaying single vacancy
 */

get_header();

while (have_posts()) :
    the_post();
    $meta = get_post_custom(get_the_ID());
?>

<article id="vacancy-<?php the_ID(); ?>" <?php post_class('rcwp-vacancy-detail'); ?>>
    <div class="rcwp-vacancy-header">
        <div class="rcwp-container">
            <h1 class="rcwp-vacancy-title"><?php the_title(); ?></h1>

            <div class="rcwp-vacancy-meta">
                <?php if (!empty($meta['_vacancy_company'][0])): ?>
                    <div class="rcwp-meta-item rcwp-company">
                        <span class="rcwp-meta-label"><?php _e('Company', 'recruit-connect-wp'); ?></span>
                        <span class="rcwp-meta-value"><?php echo esc_html($meta['_vacancy_company'][0]); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($meta['_vacancy_location'][0])): ?>
                    <div class="rcwp-meta-item rcwp-location">
                        <span class="rcwp-meta-label"><?php _e('Location', 'recruit-connect-wp'); ?></span>
                        <span class="rcwp-meta-value"><?php echo esc_html($meta['_vacancy_location'][0]); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($meta['_vacancy_salary'][0])): ?>
                    <div class="rcwp-meta-item rcwp-salary">
                        <span class="rcwp-meta-label"><?php _e('Salary', 'recruit-connect-wp'); ?></span>
                        <span class="rcwp-meta-value"><?php echo esc_html($meta['_vacancy_salary'][0]); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="rcwp-quick-apply">
                <button class="rcwp-apply-btn" data-scroll-to="application-form">
                    <?php _e('Apply Now', 'recruit-connect-wp'); ?>
                </button>
            </div>
        </div>
    </div>

    <div class="rcwp-vacancy-content">
        <div class="rcwp-container">
            <div class="rcwp-grid">
                <div class="rcwp-main-content">
                    <?php the_content(); ?>
                </div>

                <aside class="rcwp-sidebar">
                    <div class="rcwp-sidebar-widget rcwp-job-details">
                        <h3><?php _e('Job Details', 'recruit-connect-wp'); ?></h3>

                        <?php
                        $details = array(
                            'jobtype' => __('Job Type', 'recruit-connect-wp'),
                            'education' => __('Education', 'recruit-connect-wp'),
                            'experience' => __('Experience', 'recruit-connect-wp'),
                            'remotetype' => __('Remote Type', 'recruit-connect-wp')
                        );

                        foreach ($details as $key => $label):
                            $value = get_post_meta(get_the_ID(), "_vacancy_{$key}", true);
                            if (!empty($value)):
                        ?>
                            <div class="rcwp-detail-item">
                                <span class="rcwp-detail-label"><?php echo esc_html($label); ?></span>
                                <span class="rcwp-detail-value"><?php echo esc_html($value); ?></span>
                            </div>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>

                    <?php if (!empty($meta['_vacancy_recruitername'][0])): ?>
                    <div class="rcwp-sidebar-widget rcwp-recruiter-info">
                        <h3><?php _e('Recruiter', 'recruit-connect-wp'); ?></h3>
                        <div class="rcwp-recruiter-card">
                            <?php if (!empty($meta['_vacancy_recruiterimage'][0])): ?>
                                <img src="<?php echo esc_url($meta['_vacancy_recruiterimage'][0]); ?>"
                                     alt="<?php echo esc_attr($meta['_vacancy_recruitername'][0]); ?>"
                                     class="rcwp-recruiter-image">
                            <?php endif; ?>

                            <div class="rcwp-recruiter-details">
                                <h4><?php echo esc_html($meta['_vacancy_recruitername'][0]); ?></h4>
                                <?php if (!empty($meta['_vacancy_recruiteremail'][0])): ?>
                                    <a href="mailto:<?php echo esc_attr($meta['_vacancy_recruiteremail'][0]); ?>"
                                       class="rcwp-recruiter-email">
                                        <?php echo esc_html($meta['_vacancy_recruiteremail'][0]); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </div>

    <div id="application-form" class="rcwp-application-section">
        <div class="rcwp-container">
            <h2><?php _e('Apply for this position', 'recruit-connect-wp'); ?></h2>
            <?php echo do_shortcode('[recruit_connect_application_form]'); ?>
        </div>
    </div>
</article>

<?php
endwhile;

get_footer();
?>

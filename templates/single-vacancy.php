<?php
/**
 * Template for displaying single vacancy
 */

get_header();

while (have_posts()) :
	the_post();

	// Get all vacancy meta data
	$meta_fields = array(
		'company' => array(
			'label' => __('Company', 'recruit-connect-wp'),
			'icon' => 'building'
		),
		'city' => array(
			'label' => __('Location', 'recruit-connect-wp'),
			'icon' => 'location'
		),
		'salary' => array(
			'label' => __('Salary', 'recruit-connect-wp'),
			'icon' => 'money'
		),
		'education' => array(
			'label' => __('Education', 'recruit-connect-wp'),
			'icon' => 'graduation-cap'
		),
		'jobtype' => array(
			'label' => __('Job Type', 'recruit-connect-wp'),
			'icon' => 'briefcase'
		),
		'experience' => array(
			'label' => __('Experience', 'recruit-connect-wp'),
			'icon' => 'clock'
		)
	);
	?>

    <div class="recruit-connect-vacancy-detail">
        <div class="vacancy-header">
            <h1><?php the_title(); ?></h1>

            <div class="vacancy-meta">
				<?php foreach ($meta_fields as $key => $field) :
					$value = get_post_meta(get_the_ID(), '_vacancy_' . $key, true);
					if (!empty($value)) : ?>
                        <div class="meta-item">
                            <i class="dashicons dashicons-<?php echo esc_attr($field['icon']); ?>"></i>
                            <span class="label"><?php echo esc_html($field['label']); ?>:</span>
                            <span class="value"><?php echo esc_html($value); ?></span>
                        </div>
					<?php endif;
				endforeach; ?>
            </div>
        </div>

        <div class="vacancy-content">
			<?php the_content(); ?>
        </div>

		<?php
		// Display recruiter information if available
		$recruiter_name = get_post_meta(get_the_ID(), '_vacancy_recruitername', true);
		$recruiter_email = get_post_meta(get_the_ID(), '_vacancy_recruiteremail', true);
		$recruiter_image = get_post_meta(get_the_ID(), '_vacancy_recruiterimage', true);

		if (!empty($recruiter_name)) : ?>
            <div class="vacancy-recruiter">
                <h3><?php _e('Contact Person', 'recruit-connect-wp'); ?></h3>
                <div class="recruiter-info">
					<?php if (!empty($recruiter_image)) : ?>
                        <img src="<?php echo esc_url($recruiter_image); ?>"
                             alt="<?php echo esc_attr($recruiter_name); ?>"
                             class="recruiter-image">
					<?php endif; ?>

                    <div class="recruiter-details">
                        <h4><?php echo esc_html($recruiter_name); ?></h4>
						<?php if (!empty($recruiter_email)) : ?>
                            <a href="mailto:<?php echo esc_attr($recruiter_email); ?>"
                               class="recruiter-email">
								<?php echo esc_html($recruiter_email); ?>
                            </a>
						<?php endif; ?>
                    </div>
                </div>
            </div>
		<?php endif; ?>

        <!-- Add application form here if needed -->
    </div>

<?php
endwhile;

get_footer();
?>

<style>
    .recruit-connect-vacancy-detail {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    .vacancy-header {
        margin-bottom: 30px;
    }

    .vacancy-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .meta-item .dashicons {
        color: #666;
    }

    .meta-item .label {
        font-weight: bold;
        color: #666;
    }

    .vacancy-content {
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .vacancy-recruiter {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 4px;
        margin-top: 30px;
    }

    .recruiter-info {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 15px;
    }

    .recruiter-image {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
    }

    .recruiter-details h4 {
        margin: 0 0 10px 0;
    }

    .recruiter-email {
        color: #0073aa;
        text-decoration: none;
    }

    .recruiter-email:hover {
        text-decoration: underline;
    }
</style>

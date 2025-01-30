<?php if ($query->have_posts()): ?>
    <div class="recruit-connect-vacancies grid">
		<?php while ($query->have_posts()): $query->the_post(); ?>
            <div class="vacancy-card">
                <h3 class="vacancy-title">
                    <a href="<?php echo esc_url(add_query_arg('vacancy_id', get_field('vacancy_id', get_the_ID()), get_permalink())); ?>">
						<?php the_title(); ?>
                    </a>
                </h3>

                <div class="vacancy-meta">
					<?php if ($company = get_field('company', get_the_ID())): ?>
                        <span class="company">
                            <i class="fas fa-building"></i> <?php echo esc_html($company); ?>
                        </span>
					<?php endif; ?>

					<?php if ($location = get_field('city', get_the_ID())): ?>
                        <span class="location">
                            <i class="fas fa-map-marker-alt"></i> <?php echo esc_html($location); ?>
                        </span>
					<?php endif; ?>

	                <?php
	                $min_salary = get_field('salary_minimum', get_the_ID());
	                $max_salary = get_field('salary_maximum', get_the_ID());
	                if (!empty($min_salary) || !empty($max_salary)): ?>
                        <span class="salary">
                            <i class="fas fa-euro-sign"></i>
                            <?php
                            if (!empty($min_salary) && !empty($max_salary) && $min_salary !== $max_salary) {
                                echo esc_html(number_format($min_salary, 0, ',', '.')) . ' - ' . esc_html(number_format($max_salary, 0, ',', '.'));
                            } elseif (!empty($min_salary)) {
                                echo esc_html(number_format($min_salary, 0, ',', '.'));
                            } elseif (!empty($max_salary)) { // Just in case only max is provided (unlikely but possible)
                                echo esc_html(number_format($max_salary, 0, ',', '.'));
                            }
                            ?>
                        </span>
	                <?php endif; ?>
                </div>

                <div class="vacancy-excerpt">
					<?php echo wp_trim_words(get_the_content(), 20); ?>
                </div>

                <div class="vacancy-footer">
					<?php if ($type = get_field('jobtype', get_the_ID())): ?>
                        <span class="job-type"><?php echo esc_html($type); ?></span>
					<?php endif; ?>

                    <a href="<?php echo esc_url(add_query_arg('vacancy_id', get_field('vacancy_id', get_the_ID()), get_permalink())); ?>"
                       class="button">
						<?php _e('View Details', 'recruit-connect-wp'); ?>
                    </a>
                </div>
            </div>
		<?php endwhile; ?>
    </div>

	<?php if ($query->max_num_pages > 1): ?>
        <div class="recruit-connect-pagination">
			<?php
			echo paginate_links(array(
				'total' => $query->max_num_pages,
				'current' => max(1, get_query_var('paged')),
				'prev_text' => '«',
				'next_text' => '»'
			));
			?>
        </div>
	<?php endif; ?>

<?php else: ?>
    <p class="no-vacancies"><?php _e('No vacancies found.', 'recruit-connect-wp'); ?></p>
<?php endif;
wp_reset_postdata(); ?>

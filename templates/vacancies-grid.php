<?php if ($query->have_posts()): ?>
    <div class="recruit-connect-vacancies grid">
        <?php while ($query->have_posts()): $query->the_post(); ?>
            <div class="vacancy-card">
                <h3 class="vacancy-title">
                    <a href="<?php echo esc_url(add_query_arg('vacancy_id', get_post_meta(get_the_ID(), '_vacancy_id', true), get_permalink())); ?>">
                        <?php the_title(); ?>
                    </a>
                </h3>

                <div class="vacancy-meta">
                    <?php if ($company = get_post_meta(get_the_ID(), '_vacancy_company', true)): ?>
                        <span class="company">
                            <i class="fas fa-building"></i> <?php echo esc_html($company); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($location = get_post_meta(get_the_ID(), '_vacancy_city', true)): ?>
                        <span class="location">
                            <i class="fas fa-map-marker-alt"></i> <?php echo esc_html($location); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($salary = get_post_meta(get_the_ID(), '_vacancy_salary', true)): ?>
                        <span class="salary">
                            <i class="fas fa-euro-sign"></i> <?php echo esc_html($salary); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="vacancy-excerpt">
                    <?php echo wp_trim_words(get_the_content(), 20); ?>
                </div>

                <div class="vacancy-footer">
                    <?php if ($type = get_post_meta(get_the_ID(), '_vacancy_jobtype', true)): ?>
                        <span class="job-type"><?php echo esc_html($type); ?></span>
                    <?php endif; ?>

                    <a href="<?php echo esc_url(add_query_arg('vacancy_id', get_post_meta(get_the_ID(), '_vacancy_id', true), get_permalink())); ?>"
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
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;'
            ));
            ?>
        </div>
    <?php endif; ?>

<?php else: ?>
    <p class="no-vacancies"><?php _e('No vacancies found.', 'recruit-connect-wp'); ?></p>
<?php endif;
wp_reset_postdata(); ?>

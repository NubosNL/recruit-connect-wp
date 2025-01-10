<form class="recruit-connect-search" method="get">
    <div class="search-fields">
        <div class="search-field">
            <input type="text"
                   name="keyword"
                   placeholder="<?php esc_attr_e('Search keywords', 'recruit-connect-wp'); ?>"
                   value="<?php echo esc_attr(get_query_var('keyword')); ?>">
        </div>

        <?php if ($atts['show_category']): ?>
            <div class="search-field">
                <select name="category">
                    <option value=""><?php esc_html_e('All Categories', 'recruit-connect-wp'); ?></option>
                    <?php
                    $categories = $this->get_unique_meta_values('_vacancy_category');
                    foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category); ?>"
                                <?php selected(get_query_var('category'), $category); ?>>
                            <?php echo esc_html($category); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($atts['show_education']): ?>
            <div class="search-field">
                <select name="education">
                    <option value=""><?php esc_html_e('All Education Levels', 'recruit-connect-wp'); ?></option>
                    <?php
                    $education_levels = $this->get_unique_meta_values('_vacancy_education');
                    foreach ($education_levels as $level): ?>
                        <option value="<?php echo esc_attr($level); ?>"
                                <?php selected(get_query_var('education'), $level); ?>>
                            <?php echo esc_html($level); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ($atts['show_jobtype']): ?>
            <div class="search-field">
                <select name="jobtype">
                    <option value=""><?php esc_html_e('All Job Types', 'recruit-connect-wp'); ?></option>
                    <?php
                    $job_types = $this->get_unique_meta_values('_vacancy_jobtype');
                    foreach ($job_types as $type): ?>
                        <option value="<?php echo esc_attr($type); ?>"
                                <?php selected(get_query_var('jobtype'), $type); ?>>
                            <?php echo esc_html($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="search-submit">
            <button type="submit" class="button">
                <?php esc_html_e('Search', 'recruit-connect-wp'); ?>
            </button>
        </div>
    </div>
</form>

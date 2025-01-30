<div class="vacancy-filters">
	<?php if (in_array('category', $search_components)): ?>
        <!-- Category Filter -->
        <div class="filter-group mb-4">
            <label class="form-label" for="categoryFilter"><?php esc_html_e('Category', 'recruit-connect-wp'); ?></label>
            <select class="form-select" id="categoryFilter">
                <option value=""><?php esc_html_e('All Categories', 'recruit-connect-wp'); ?></option>
				<?php foreach ($categories as $category): ?>
                    <option value="<?php echo esc_attr($category); ?>">
						<?php echo esc_html($category); ?>
                    </option>
				<?php endforeach; ?>
            </select>
        </div>
	<?php endif; ?>

	<?php if (in_array('education', $search_components)): ?>
        <!-- Education Level Filter -->
        <div class="filter-group mb-4">
            <label class="form-label" for="educationFilter"><?php esc_html_e('Education Level', 'recruit-connect-wp'); ?></label>
            <select class="form-select" id="educationFilter">
                <option value=""><?php esc_html_e('All Levels', 'recruit-connect-wp'); ?></option>
				<?php foreach ($education_options as $education): ?>
                    <option value="<?php echo esc_attr($education); ?>">
						<?php echo esc_html($education); ?>
                    </option>
				<?php endforeach; ?>
            </select>
        </div>
	<?php endif; ?>

	<?php if (in_array('jobtype', $search_components)): ?>
        <!-- Job Type Filter -->
        <div class="filter-group mb-4">
            <label class="form-label" for="jobtypeFilter"><?php esc_html_e('Job Type', 'recruit-connect-wp'); ?></label>
            <select class="form-select" id="jobtypeFilter">
                <option value=""><?php esc_html_e('All Types', 'recruit-connect-wp'); ?></option>
				<?php foreach ($jobtype_options as $jobtype): ?>
                    <option value="<?php echo esc_attr($jobtype); ?>">
						<?php echo esc_html($jobtype); ?>
                    </option>
				<?php endforeach; ?>
            </select>
        </div>
	<?php endif; ?>
</div>

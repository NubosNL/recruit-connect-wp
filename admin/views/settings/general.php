<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<table class="form-table" role="presentation">
    <tr>
        <th scope="row">
            <label for="recruit_connect_xml_url">
				<?php echo esc_html__('XML Feed URL', 'recruit-connect-wp'); ?>
            </label>
        </th>
        <td>
            <input type="url"
                   name="recruit_connect_xml_url"
                   id="recruit_connect_xml_url"
                   value="<?php echo esc_attr(get_option('recruit_connect_xml_url')); ?>"
                   class="regular-text">
            <p class="description">
				<?php echo esc_html__('Enter the URL of your Recruit Connect XML feed', 'recruit-connect-wp'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="recruit_connect_application_url">
				<?php echo esc_html__('Application Destination URL', 'recruit-connect-wp'); ?>
            </label>
        </th>
        <td>
            <input type="url"
                   name="recruit_connect_application_url"
                   id="recruit_connect_application_url"
                   value="<?php echo esc_attr(get_option('recruit_connect_application_url')); ?>"
                   class="regular-text">
            <p class="description">
				<?php echo esc_html__('URL where application forms will be submitted', 'recruit-connect-wp'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
            <label for="recruit_connect_detail_param">
				<?php echo esc_html__('Vacancy Detail URL Parameter', 'recruit-connect-wp'); ?>
            </label>
        </th>
        <td>
            <input type="text"
                   name="recruit_connect_detail_param"
                   id="recruit_connect_detail_param"
                   value="<?php echo esc_attr(get_option('recruit_connect_detail_param', 'vacancy_id')); ?>"
                   class="regular-text">
            <p class="description">
				<?php echo esc_html__('URL parameter used for vacancy detail pages', 'recruit-connect-wp'); ?>
            </p>
        </td>
    </tr>

    <tr>
        <th scope="row">
			<?php echo esc_html__('Enable Detail Page', 'recruit-connect-wp'); ?>
        </th>
        <td>
            <fieldset>
                <label for="recruit_connect_enable_detail">
                    <input type="checkbox"
                           name="recruit_connect_enable_detail"
                           id="recruit_connect_enable_detail"
                           value="1"
						<?php checked(get_option('recruit_connect_enable_detail', '1')); ?>>
					<?php echo esc_html__('Enable individual vacancy detail pages', 'recruit-connect-wp'); ?>
                </label>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row">
			<?php echo esc_html__('Search Components', 'recruit-connect-wp'); ?>
        </th>
        <td>
            <fieldset>
				<?php
				$search_components = get_option('recruit_connect_search_components', array());
				if (!is_array($search_components)) {
					$search_components = array();
				}

				$components = array(
					'category' => __('Category', 'recruit-connect-wp'),
					'education' => __('Education', 'recruit-connect-wp'),
					'jobtype' => __('Job Type', 'recruit-connect-wp'),
					'salary' => __('Salary', 'recruit-connect-wp')
				);

				foreach ($components as $value => $label): ?>
                    <label style="display: block; margin-bottom: 8px;">
                        <input type="checkbox"
                               name="recruit_connect_search_components[]"
                               value="<?php echo esc_attr($value); ?>"
							<?php checked(in_array($value, $search_components, true)); ?>>
						<?php echo esc_html($label); ?>
                    </label>
				<?php endforeach; ?>
            </fieldset>
        </td>
    </tr>
</table>

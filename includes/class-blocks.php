<?php
namespace RecruitConnect;

class Blocks {
	public function __construct() {
		add_action('init', array($this, 'register_blocks'));
		add_action('block_categories_all', array($this, 'register_block_category'), 10, 2);
	}

	public function register_blocks() {
		if (!function_exists('register_block_type')) {
			return;
		}

		// Register block script
		wp_register_script(
			'recruit-connect-blocks',
			RECRUIT_CONNECT_PLUGIN_URL . 'blocks/build/index.js',
			[
				'wp-blocks',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-data',
				'wp-i18n'
			],
			filemtime(RECRUIT_CONNECT_PLUGIN_DIR . 'blocks/build/index.js')
		);

		// Register block styles
		wp_register_style(
			'recruit-connect-blocks-editor',
			RECRUIT_CONNECT_PLUGIN_URL . 'blocks/build/editor.css',
			['wp-edit-blocks'],
			filemtime(RECRUIT_CONNECT_PLUGIN_DIR . 'blocks/build/editor.css')
		);

		// Register all meta fields
		$meta_fields = [
			'_vacancy_id',
			'_vacancy_company',
			'_vacancy_city',
			'_vacancy_createdat',
			'_vacancy_streetaddress',
			'_vacancy_postalcode',
			'_vacancy_state',
			'_vacancy_country',
			'_vacancy_salary',
			'_vacancy_education',
			'_vacancy_jobtype',
			'_vacancy_experience',
			'_vacancy_remotetype',
			'_vacancy_recruitername',
			'_vacancy_recruiteremail',
			'_vacancy_recruiterimage'
		];

		foreach ($meta_fields as $meta_key) {
			register_post_meta('vacancy', $meta_key, [
				'show_in_rest' => true,
				'single' => true,
				'type' => 'string',
				'auth_callback' => function() {
					return current_user_can('edit_posts');
				}
			]);
		}

		// Register the block
		register_block_type('recruit-connect/vacancy-field', [
			'editor_script' => 'recruit-connect-blocks',
			'editor_style' => 'recruit-connect-blocks-editor',
			'attributes' => [
				'fieldKey' => [
					'type' => 'string',
					'default' => ''
				]
			],
			'render_callback' => [$this, 'render_vacancy_field_block']
		]);
	}

	public function render_vacancy_field_block($attributes) {
		if (empty($attributes['fieldKey'])) {
			return '';
		}

		$meta_value = get_post_meta(get_the_ID(), $attributes['fieldKey'], true);

		ob_start();
		?>
        <div class="wp-block-recruit-connect-vacancy-field">
			<?php if ($attributes['fieldKey'] === '_vacancy_recruiterimage' && !empty($meta_value)): ?>
                <img src="<?php echo esc_url($meta_value); ?>" alt="" class="recruiter-image" />
			<?php else: ?>
				<?php echo esc_html($meta_value); ?>
			<?php endif; ?>
        </div>
		<?php
		return ob_get_clean();
	}

	public function register_block_category($categories) {
		return array_merge(
			$categories,
			[
				[
					'slug' => 'recruit-connect',
					'title' => __('Recruit Connect', 'recruit-connect-wp'),
					'icon' => 'businessman'
				]
			]
		);
	}
}

<?php
namespace RecruitConnect;

class Blocks {
	public function __construct() {
		add_action('init', array($this, 'register_blocks'));
		add_action('block_categories_all', array($this, 'register_block_category'), 10, 2);
	}

	public function register_blocks() {
		// Register block script
		wp_register_script(
			'recruit-connect-blocks',
			RECRUIT_CONNECT_PLUGIN_URL . 'blocks/build/index.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-block-editor',
				'wp-components',
				'wp-i18n',
				'wp-data'
			),
			RECRUIT_CONNECT_VERSION
		);

		// Register block styles
		wp_register_style(
			'recruit-connect-blocks-style',
			RECRUIT_CONNECT_PLUGIN_URL . 'blocks/build/style.css',
			array(),
			RECRUIT_CONNECT_VERSION
		);

		// Register editor styles
		wp_register_style(
			'recruit-connect-blocks-editor',
			RECRUIT_CONNECT_PLUGIN_URL . 'blocks/build/editor.css',
			array(),
			RECRUIT_CONNECT_VERSION
		);

		// Register the block
		register_block_type('recruit-connect/vacancy-field', array(
			'editor_script' => 'recruit-connect-blocks',
			'editor_style'  => 'recruit-connect-blocks-editor',
			'style'         => 'recruit-connect-blocks-style',
			'render_callback' => array($this, 'render_vacancy_field_block'),
			'attributes' => array(
				'fieldKey' => array(
					'type' => 'string',
					'default' => ''
				),
				'label' => array(
					'type' => 'string',
					'default' => ''
				),
				'showLabel' => array(
					'type' => 'boolean',
					'default' => true
				)
			)
		));
	}

	public function register_block_category($categories, $post) {
		return array_merge(
			$categories,
			array(
				array(
					'slug' => 'recruit-connect',
					'title' => __('Recruit Connect', 'recruit-connect-wp'),
					'icon'  => 'businessman'
				),
			)
		);
	}

	public function render_vacancy_field_block($attributes, $content) {
		if (empty($attributes['fieldKey'])) {
			return '';
		}

		$post_id = get_the_ID();
		$value = get_post_meta($post_id, $attributes['fieldKey'], true);

		if (empty($value)) {
			return '';
		}

		$output = '<div class="wp-block-recruit-connect-vacancy-field">';

		if (!empty($attributes['showLabel'])) {
			$output .= '<span class="field-label">' . esc_html($attributes['label']) . ': </span>';
		}

		if ($attributes['fieldKey'] === '_vacancy_recruiterimage') {
			$output .= '<img src="' . esc_url($value) . '" alt="' . esc_attr($attributes['label']) . '" class="recruiter-image">';
		} else {
			$output .= '<span class="field-value">' . esc_html($value) . '</span>';
		}

		$output .= '</div>';

		return $output;
	}
}

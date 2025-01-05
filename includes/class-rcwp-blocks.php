<?php
class RCWP_Blocks {
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
    }

    public function register_blocks() {
        register_block_type('recruit-connect-wp/vacancy-list', array(
            'editor_script' => 'rcwp-blocks-editor',
            'editor_style' => 'rcwp-blocks-editor-style',
            'render_callback' => array($this, 'render_vacancy_list_block'),
            'attributes' => array(
                'limit' => array(
                    'type' => 'number',
                    'default' => 10
                ),
                'showFilters' => array(
                    'type' => 'boolean',
                    'default' => true
                ),
                'layout' => array(
                    'type' => 'string',
                    'default' => 'grid'
                )
            )
        ));

        register_block_type('recruit-connect-wp/vacancy-detail', array(
            'editor_script' => 'rcwp-blocks-editor',
            'editor_style' => 'rcwp-blocks-editor-style',
            'render_callback' => array($this, 'render_vacancy_detail_block'),
            'attributes' => array(
                'showApplication' => array(
                    'type' => 'boolean',
                    'default' => true
                ),
                'layout' => array(
                    'type' => 'string',
                    'default' => 'standard'
                )
            )
        ));

        register_block_type('recruit-connect-wp/application-form', array(
            'editor_script' => 'rcwp-blocks-editor',
            'editor_style' => 'rcwp-blocks-editor-style',
            'render_callback' => array($this, 'render_application_form_block')
        ));
    }

    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'rcwp-blocks-editor',
            RCWP_PLUGIN_URL . 'build/blocks.js',
            array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
            RCWP_VERSION
        );

        wp_enqueue_style(
            'rcwp-blocks-editor-style',
            RCWP_PLUGIN_URL . 'build/blocks.css',
            array('wp-edit-blocks'),
            RCWP_VERSION
        );
    }

    public function render_vacancy_list_block($attributes) {
        $frontend = new RCWP_Frontend();
        return $frontend->render_vacancies_overview($attributes);
    }

    public function render_vacancy_detail_block($attributes) {
        $frontend = new RCWP_Frontend();
        return $frontend->render_vacancy_detail($attributes);
    }

    public function render_application_form_block($attributes) {
        $frontend = new RCWP_Frontend();
        return $frontend->render_application_form($attributes);
    }
}

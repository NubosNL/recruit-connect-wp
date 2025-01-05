<?php
class RCWP_Block_Category {
    public function __construct() {
        add_filter('block_categories_all', array($this, 'register_block_category'), 10, 2);
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
}

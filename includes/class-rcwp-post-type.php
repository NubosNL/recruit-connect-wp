<?php
class RCWP_Post_Type {
    public function __construct() {
        add_action('init', array($this, 'register_vacancy_post_type'));
        add_action('admin_menu', array($this, 'remove_add_new_menu'));
        add_filter('post_row_actions', array($this, 'modify_list_row_actions'), 10, 2);
        add_action('admin_head', array($this, 'disable_quick_edit'));
    }

    public function register_vacancy_post_type() {
        $labels = array(
            'name'               => __('Vacancies', 'recruit-connect-wp'),
            'singular_name'      => __('Vacancy', 'recruit-connect-wp'),
            'menu_name'          => __('Vacancies', 'recruit-connect-wp'),
            'all_items'          => __('All Vacancies', 'recruit-connect-wp'),
            'view_item'          => __('View Vacancy', 'recruit-connect-wp'),
            'search_items'       => __('Search Vacancies', 'recruit-connect-wp'),
            'not_found'          => __('No vacancies found', 'recruit-connect-wp'),
            'not_found_in_trash' => __('No vacancies found in trash', 'recruit-connect-wp')
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug' => get_option('rcwp_detail_url_param', 'vacancy')
            ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'editor'),
            'show_in_rest'       => true,
        );

        register_post_type('vacancy', $args);
    }

    public function remove_add_new_menu() {
        global $submenu;
        if (isset($submenu['edit.php?post_type=vacancy'])) {
            foreach ($submenu['edit.php?post_type=vacancy'] as $key => $item) {
                if ($item[2] === 'post-new.php?post_type=vacancy') {
                    unset($submenu['edit.php?post_type=vacancy'][$key]);
                }
            }
        }
    }

    public function modify_list_row_actions($actions, $post) {
        if ($post->post_type === 'vacancy') {
            unset($actions['edit']);
            unset($actions['inline hide-if-no-js']);
            unset($actions['trash']);
        }
        return $actions;
    }

    public function disable_quick_edit() {
        global $current_screen;
        if (!$current_screen) return;

        if ($current_screen->post_type === 'vacancy') {
            ?>
            <style type="text/css">
                .post-type-vacancy .inline-edit-row { display: none !important; }
            </style>
            <?php
        }
    }
}

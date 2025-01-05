<?php
class RCWP_Performance {
    private $cache;

    public function __construct($cache) {
        $this->cache = $cache;

        add_action('init', array($this, 'init_performance_features'));
        add_filter('posts_clauses', array($this, 'optimize_vacancy_queries'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'optimize_assets'), 99);
    }

    public function init_performance_features() {
        // Enable object caching for queries
        wp_using_ext_object_cache(true);

        // Add index for meta queries if not exists
        $this->maybe_add_meta_index();
    }

    /**
     * Optimize vacancy queries
     */
    public function optimize_vacancy_queries($clauses, $query) {
        global $wpdb;

        if (!$query->is_main_query() || $query->get('post_type') !== 'vacancy') {
            return $clauses;
        }

        // Optimize JOIN clauses
        if (isset($clauses['join'])) {
            $clauses['join'] = $this->optimize_joins($clauses['join']);
        }

        // Add indexes hints
        if (isset($clauses['where'])) {
            $clauses['where'] .= " USE INDEX (type_status_date)";
        }

        return $clauses;
    }

    /**
     * Optimize asset loading
     */
    public function optimize_assets() {
        global $post;

        // Load assets only on relevant pages
        if (!is_singular('vacancy') && !has_shortcode($post->post_content, 'recruit_connect_vacancies_overview')) {
            wp_dequeue_style('rcwp-frontend');
            wp_dequeue_script('rcwp-frontend');
        }

        // Defer non-critical CSS
        add_filter('style_loader_tag', array($this, 'defer_non_critical_css'), 10, 4);
    }

    /**
     * Defer non-critical CSS loading
     */
    public function defer_non_critical_css($html, $handle, $href, $media) {
        if ($handle === 'rcwp-frontend') {
            $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.rel='stylesheet'\"", $html);
        }
        return $html;
    }

    /**
     * Add database indexes for better performance
     */
    private function maybe_add_meta_index() {
        global $wpdb;

        // Check if index exists
        $index_exists = $wpdb->get_results(
            "SHOW INDEX FROM {$wpdb->postmeta} WHERE Key_name = 'rcwp_vacancy_meta_idx'"
        );

        if (empty($index_exists)) {
            $wpdb->query(
                "ALTER TABLE {$wpdb->postmeta} 
                ADD INDEX rcwp_vacancy_meta_idx (meta_key(191), meta_value(191))"
            );
        }
    }

    /**
     * Optimize JOIN clauses
     */
    private function optimize_joins($join) {
        // Remove duplicate JOINs
        $join_parts = array_unique(explode(' JOIN ', $join));
        return implode(' JOIN ', $join_parts);
    }

    /**
     * Get optimized vacancy query args
     */
    public function get_optimized_query_args($args = array()) {
        $default_args = array(
            'no_found_rows' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
            'cache_results' => true
        );

        return wp_parse_args($args, $default_args);
    }
}

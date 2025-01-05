<?php
class RCWP_Cache {
    private $cache_group = 'rcwp_cache';
    private $cache_expiry = 3600; // 1 hour default
    private $transient_prefix = 'rcwp_';

    public function __construct() {
        add_action('save_post_vacancy', array($this, 'flush_vacancy_cache'));
        add_action('deleted_post', array($this, 'flush_vacancy_cache'));
        add_action('rcwp_after_import', array($this, 'flush_all_cache'));
    }

    /**
     * Get cached data
     */
    public function get($key, $callback = null, $expiry = null) {
        $cache_key = $this->transient_prefix . $key;
        $cached_data = get_transient($cache_key);

        if (false !== $cached_data) {
            return $cached_data;
        }

        if (is_callable($callback)) {
            $data = $callback();
            $this->set($key, $data, $expiry);
            return $data;
        }

        return false;
    }

    /**
     * Set cache data
     */
    public function set($key, $data, $expiry = null) {
        $cache_key = $this->transient_prefix . $key;
        $expiration = $expiry ?? $this->cache_expiry;

        return set_transient($cache_key, $data, $expiration);
    }

    /**
     * Delete specific cache
     */
    public function delete($key) {
        $cache_key = $this->transient_prefix . $key;
        return delete_transient($cache_key);
    }

    /**
     * Flush all plugin cache
     */
    public function flush_all_cache() {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $this->transient_prefix . '%'
            )
        );

        wp_cache_flush();
    }

    /**
     * Flush vacancy related cache
     */
    public function flush_vacancy_cache($post_id) {
        if (get_post_type($post_id) !== 'vacancy') {
            return;
        }

        $this->delete('vacancy_' . $post_id);
        $this->delete('vacancies_list');
        $this->delete('vacancy_filters');
    }

    /**
     * Get cache key with filters
     */
    public function get_filtered_cache_key($base_key, $filters) {
        return $base_key . '_' . md5(serialize($filters));
    }
}

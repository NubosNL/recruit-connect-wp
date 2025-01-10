<?php
namespace RecruitConnect;

class Cache {
    private $prefix = 'recruit_connect_';
    private $expiration = 3600; // 1 hour

    public function get($key) {
        return get_transient($this->prefix . $key);
    }

    public function set($key, $value, $expiration = null) {
        $expiration = $expiration ?? $this->expiration;
        return set_transient($this->prefix . $key, $value, $expiration);
    }

    public function delete($key) {
        return delete_transient($this->prefix . $key);
    }

    public function flush() {
        global $wpdb;

        $sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_" . $this->prefix . "%'";
        $wpdb->query($sql);
    }
}

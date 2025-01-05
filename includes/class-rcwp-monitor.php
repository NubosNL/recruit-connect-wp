<?php
class RCWP_Monitor {
    private $start_time;
    private $metrics = array();
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
        $this->start_time = microtime(true);

        add_action('init', array($this, 'start_monitoring'));
        add_action('shutdown', array($this, 'log_metrics'));
    }

    /**
     * Start monitoring various metrics
     */
    public function start_monitoring() {
        // Monitor query performance
        add_filter('query', array($this, 'monitor_query'));

        // Monitor memory usage
        add_action('admin_init', array($this, 'check_memory_usage'));

        // Monitor cache hits
        add_action('debug_bar_metrics', array($this, 'add_cache_metrics'));
    }

    /**
     * Monitor database queries
     */
    public function monitor_query($query) {
        if (stripos($query, 'rcwp_') !== false) {
            $start = microtime(true);
            $this->metrics['queries'][] = array(
                'query' => $query,
                'time' => microtime(true) - $start
            );
        }
        return $query;
    }

    /**
     * Check memory usage
     */
    public function check_memory_usage() {
        $memory_usage = memory_get_peak_usage(true);
        $memory_limit = ini_get('memory_limit');

        if ($memory_usage > $this->convert_to_bytes($memory_limit) * 0.8) {
            $this->logger->log(
                'High memory usage detected: ' . size_format($memory_usage),
                'warning'
            );
        }
    }

    /**
     * Add cache metrics
     */
    public function add_cache_metrics($metrics) {
        global $wp_object_cache;

        if (!is_object($wp_object_cache)) {
            return $metrics;
        }

        $metrics['cache_hits'] = $wp_object_cache->cache_hits;
        $metrics['cache_misses'] = $wp_object_cache->cache_misses;

        return $metrics;
    }

    /**
     * Log performance metrics
     */
    public function log_metrics() {
        $execution_time = microtime(true) - $this->start_time;
        $memory_usage = memory_get_peak_usage(true);

        $metrics = array(
            'execution_time' => round($execution_time, 4),
            'memory_usage' => size_format($memory_usage),
            'queries' => $this->metrics['queries'] ?? array()
        );

        if ($execution_time > 2) { // Log slow requests
            $this->logger->log(
                'Slow request detected',
                'warning',
                $metrics
            );
        }

        // Store metrics for analysis
        $this->store_metrics($metrics);
    }

    /**
     * Store metrics for analysis
     */
    private function store_metrics($metrics) {
        $stored_metrics = get_option('rcwp_performance_metrics', array());
        $stored_metrics[] = array(
            'timestamp' => time(),
            'metrics' => $metrics
        );

        // Keep only last 1000 metrics
        if (count($stored_metrics) > 1000) {
            array_shift($stored_metrics);
        }

        update_option('rcwp_performance_metrics', $stored_metrics);
    }

    /**
     * Convert memory limit to bytes
     */
    private function convert_to_bytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int)$value;

        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}

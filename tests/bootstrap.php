<?php
/**
 * PHPUnit bootstrap file for Recruit Connect WP tests
 */

$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Load plugin
 */
function _manually_load_plugin() {
    require dirname(dirname(__FILE__)) . '/recruit-connect-wp.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

// Load test case base class
require_once dirname(__FILE__) . '/class-rcwp-test-case.php';

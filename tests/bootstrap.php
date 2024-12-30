<?php

require_once __DIR__ . '/../vendor/autoload.php';
// require_once __DIR__ . '/../vendor/autoload_packages.php';

// Define WordPress constants
define('ABSPATH', __DIR__ . '/../../../../');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

// Mock WC_Countries class if it doesn't exist
if (!class_exists('WC_Countries')) {
    class WC_Countries {
        public function get_countries() { return []; }
        public function get_states($country) { return []; }
        public function get_base_country() { return 'US'; }
        public function get_base_state() { return 'CA'; }
    }
}

// Define WC function if not exists
if (!function_exists('WC')) {
    function WC() {
        return new class {
            public $countries;
            public function __construct() {
                global $wc_countries_mock;
                $this->countries = $wc_countries_mock ?? new WC_Countries();
            }
        };
    }
}

// Mock WordPress functions
if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

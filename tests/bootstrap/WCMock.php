<?php

namespace {
    if (!function_exists('WC')) {
        function WC() {
            return new class {
                public $countries;
                public function __construct() {
                    global $wc_countries_mock;
                    $this->countries = $wc_countries_mock;
                }
            };
        }
    }
}

<?php

if (!interface_exists('Throwable')) {
    interface Throwable
    {
        public function getMessage();
        public function getCode();
        public function getFile();
        public function getLine();
        public function getTrace();
        public function getTraceAsString();
        public function getPrevious();
    }
}

if (!function_exists('billink_xml_safe_value')) {
    /**
     * Html encode the given value so it can be safely used within XML.
     * @param  string $value
     * @return string
     */
    function billink_xml_safe_value($value)
    {
        return htmlspecialchars($value, ENT_XML1, 'UTF-8');
    }
}

add_action('init', function () {
    if (!function_exists('WC')) {
        function WC()
        {
            return WooCommerce::instance();
        }
    }
});

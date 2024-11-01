<?php

namespace Tussendoor\Billink;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/helpers.php';

App::bind('debug', defined('BILLINK_DEBUG') ? BILLINK_DEBUG : WP_DEBUG);

class_alias('Tussendoor\Billink\Helpers\Log', 'Tussendoor\Billink\Log');

if (WP_DEBUG) {
    // Allow local HTTP requests
    add_filter('block_local_requests', '__return_false');
    add_filter('http_request_host_is_external', '__return_true');
}

App::loadFromConfig(__DIR__ . '/config/plugin.php');
App::loadFromConfig(__DIR__ . '/config/errors.php');
App::loadFromConfig(__DIR__ . '/config/triggers.php');
App::loadFromConfig(__DIR__ . '/config/integration.php');

add_action('plugins_loaded', function () {
    $plugin = new Plugin();
    $plugin->boot();

    // Load some config files later as they contain translatable text.
    App::loadFromConfig(__DIR__ . '/config/fields.php');
    App::loadFromConfig(__DIR__ . '/config/gateway.php');
}, 99);

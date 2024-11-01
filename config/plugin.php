<?php

return [

    /**
     * Basic plugin data
     */
    'plugin.name'       => 'Tussendoor Billink - Legacy',
    'plugin.prefix'     => 'tsd-bllnk',
    'plugin.version'    => '2.5.0',
    'plugin.filename'   => basename(dirname(__DIR__)) . '/index.php',
    'plugin.path'       => dirname(__DIR__),
    'plugin.viewpath'   => dirname(__DIR__) . '/app/views',
    'plugin.url'        => plugins_url() . '/' . basename(dirname(__DIR__)),
    'plugin.folder'     => 'woocommerce-billink',
    'plugin.php'        => '7.4',
    'plugin.textdomain' => 'woocommerce-gateway-billink',
];

<?php

namespace Tussendoor\Billink;

use ReflectionClass;
use Tussendoor\Http\Client;
use Tussendoor\Billink\Gateway\Settings;
use Thunder\Serializard\Format\XmlFormat;
use Tussendoor\Billink\Helpers\Singleton;
use Thunder\Serializard\FormatContainer\FormatContainer;
use Thunder\Serializard\HydratorContainer\FallbackHydratorContainer;
use Thunder\Serializard\NormalizerContainer\FallbackNormalizerContainer;

return [
    'formatter.container' => Singleton::create(function () {
        return new FormatContainer();
    }),

    'hydrator.container' => Singleton::create(function () {
        return new FallbackHydratorContainer();
    }),

    'normalizer.container' => Singleton::create(function () {
        return new FallbackNormalizerContainer();
    }),

    'formatters' => [
        'xml' => new XmlFormat(function ($format) {
            if (in_array(Contracts\Formattable::class, class_implements($format))) {
                return $format::getRootName();
            }

            return strtoupper((new ReflectionClass($format))->getShortName());
        }),
    ],

    'normalizers' => [
        Contracts\Normalizable::class => function ($instance) {
            return $instance->normalize();
        }
    ],

    'hydrators' => [
        Endpoint\Check::class => function ($data) {
            return new Response\Check($data, Endpoint\Check::class);
        },
        Endpoint\Order::class => function ($data) {
            return new Response\Order($data, Endpoint\Order::class);
        },
        Endpoint\Status::class => function ($data) {
            return new Response\Status($data, Endpoint\Status::class);
        },
        Endpoint\StartWorkflow::class => function ($data) {
            return new Response\Workflow($data, Endpoint\StartWorkflow::class);
        },
        Endpoint\Credit::class => function ($data) {
            return new Response\Credit($data, Endpoint\Credit::class);
        },
        Endpoint\Payment::class => function ($data) {
            return new Response\Payment($data, Endpoint\Payment::class);
        }
    ],

    'auth.header'       => Singleton::create(function () {
        $properties = (new ReflectionClass('WC_Settings_API'))->getDefaultProperties();
        $pluginId = isset($properties['plugin_id']) ? $properties['plugin_id'] : 'woocommerce_';

        $settings = new Settings($pluginId, App::get('gateway.id'));

        return new AuthenticationHeader($settings->user, $settings->userid);
    }),

    'billink.endpoint'  => Singleton::create(function () {
        $properties = (new ReflectionClass('WC_Settings_API'))->getDefaultProperties();
        $pluginId = isset($properties['plugin_id']) ? $properties['plugin_id'] : 'woocommerce_';

        $settings = new Settings($pluginId, App::get('gateway.id'));

        if ($settings->testmode) {
            return apply_filters('billink_api_base_url', 'https://api-staging.billink.nl');
        }

        return apply_filters('billink_api_base_url', 'https://api.billink.nl');
    }),

    'billink.settings' => function() {
        $ref = new ReflectionClass('WC_Settings_API');
        $props = $ref->getDefaultProperties();

        return new Settings(
            isset($props['plugin_id']) ? $props['plugin_id'] : 'woocommerce_',
            App::get('gateway.id')
        );
    },

    'http.client'      => Singleton::create(function () {
        return new Client(App::get('billink.endpoint'));
    }),
];

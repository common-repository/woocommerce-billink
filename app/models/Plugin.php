<?php

namespace Tussendoor\Billink;

use Tussendoor\Billink\Helpers\Log;
use Thunder\Serializard\Serializard;
use Tussendoor\Billink\Helpers\Notice;

class Plugin
{
    public function boot()
    {
        $this->bootLogger();

        $this->loadTranslations();

        $this->loadFormatters();

        $this->loadHydrators();

        $this->loadNormalizers();

        $this->loadSerializer();

        $this->loadTriggers();

        $this->registerCompatabilityHelpers();

        $this->registerControllers();

        $this->displayNotices();

        $this->declareHposCompatibility();
    }

    public function bootLogger()
    {
        $minLoglevel = apply_filters('billink_min_loglevel', App::get('debug') ? 'debug' : 'notice');

        $logger = new Log();
        $logger->boot(wc_get_logger(), 'billink', $minLoglevel);
    }

    protected function loadTranslations()
    {
        load_plugin_textdomain(
            'woocommerce-gateway-billink',
            '',
            basename(App::get('plugin.path')) . '/languages/'
        );
    }

    protected function loadFormatters()
    {
        $formatterContainer = App::get('formatter.container');
        foreach (App::get('formatters') as $name => $formatter) {
            $formatterContainer->add($name, $formatter);
        }
    }

    protected function loadHydrators()
    {
        $hydratorContainer = App::get('hydrator.container');
        foreach (App::get('hydrators') as $name => $hydrator) {
            $hydratorContainer->add($name, $hydrator);
        }
    }

    protected function loadNormalizers()
    {
        $normalizerContainer = App::get('normalizer.container');
        foreach (App::get('normalizers') as $name => $normalizer) {
            $normalizerContainer->add($name, $normalizer);
        }
    }

    protected function loadSerializer()
    {
        App::bind(
            'serializer',
            new Serializard(
                App::get('formatter.container'),
                App::get('normalizer.container'),
                App::get('hydrator.container')
            )
        );
    }

    protected function loadTriggers()
    {
        $triggers = apply_filters('billink_triggers', App::get('triggers'));

        foreach ($triggers as $name => $callback) {
            (new Action($name))->callback($callback)->register();
        }
    }

    protected function registerCompatabilityHelpers()
    {
        $helpers = apply_filters('billink_compatability_helpers', [
            Compatibility\PostcodeCheckout::class,
        ]);

        foreach ($helpers as $helper) {
            if (!class_exists($helper)) {
                continue;
            }

            $helper = new $helper();
            if (!$helper instanceof Contracts\PluginCompatible) {
                continue;
            }

            $helper->register();
        }
    }

    protected function registerControllers()
    {
        (new Controllers\AdminController())->register();
        (new Controllers\AdminActionController())->register();
        (new Controllers\GatewayController())->register();
        (new Controllers\WorkflowController())->register();
        (new Controllers\MigrationController())->register();

        // (new PaymentController)->register();
        // (new CreditController)->register();
    }

    protected function displayNotices()
    {
        add_action('init', [new Notice(), 'display'], 99);
    }

    protected function declareHposCompatibility()
    {
        add_action('before_woocommerce_init', function() {
            if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', App::get('plugin.filename'), true);
            }
        });
    }
}

<?php

namespace Tussendoor\Billink\Controllers;

use Tussendoor\Billink\App;
use Tussendoor\Billink\Helpers\Notice;
use Tussendoor\Billink\Gateway\Settings;

class MigrationController
{

    protected $settings;

    /**
     * If the 'billink_action' key is set, we'll parse the request for actions.
     */
    public function register()
    {
        $this->setProperties();
        $this->addActions();
    }

    private function setProperties()
    {
        $this->settings = get_option('woocommerce_billink_settings');
    }

    private function addActions()
    {
        add_action('init', [$this, 'maybeMigrateSettings']);
    }

    public function maybeMigrateSettings()
    {
        $this->migrateDefaultDescriptionFromSettings();
    }

    /**
     * Migrate description specifically towards version 2.4.0
     */
    private function migrateDefaultDescriptionFromSettings()
    {
        // Only migrate if the version is older then 2.4.0
        if (version_compare(get_option('woocommerce_billink_gateway_description_version', '0.0.0'), '2.4.0', '>=')) {
            return;
        }

        $previousDefault = 'Easily pay afterward with Billink. Extra costs are %costs% %vat%';
        $currentSetting  = ($this->settings['description'] ?? '');

        // New users or users who changed the value will not have to migrate. Just bump version.
        if ($currentSetting != $previousDefault) {
            return update_option('woocommerce_billink_gateway_description_version', '2.4.0');
        };

        // Migrate description for users who still had the previous default setting.
        $this->settings['description'] = 'Easily pay afterward with Billink.';

        update_option('woocommerce_billink_settings', $this->settings);
        update_option('woocommerce_billink_gateway_description_version', '2.4.0');
    }
}

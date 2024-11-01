<?php

namespace Tussendoor\Billink\Gateway\Settings;

use WC_Countries;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Gateway\Gateway;
use Tussendoor\Billink\Gateway\Settings;
use Tussendoor\Billink\Helpers\LegacyPaymentCosts;

class PaymentCost
{
    protected $gateway;
    protected $settings;
    protected $template = '/setting.paymentcost.php';

    public function __construct(Gateway $gateway, Settings $settings)
    {
        $this->gateway = $gateway;
        $this->settings = $settings;
    }

    /**
     * Render the payment cost setting for WP-Admin.
     * @param  string $key
     * @param  array  $data
     * @return string
     */
    public function render($key, $data)
    {
        $defaults  = [
            'title'             => '',
            'disabled'          => false,
            'class'             => '',
            'css'               => '',
            'placeholder'       => '',
            'type'              => 'text',
            'desc_tip'          => false,
            'description'       => '',
            'custom_attributes' => [],
        ];

        $data = wp_parse_args($data, $defaults);

        $selected = $this->getCurrentConfiguration($key);
        $countries = array_merge(
            ['*' => __('Any country', 'woocommerce-gateway-billink')],
            (new WC_Countries())->get_countries()
        );

        $fieldKey = $this->gateway->get_field_key($key);
        $tooltip = $this->gateway->get_tooltip_html($data);
        $description = $this->gateway->get_description_html($data);

        $templatePath = App::get('plugin.viewpath') . $this->template;
        ob_start();
        include $templatePath;

        return ob_get_clean();
    }

    /**
     * When this setting is saved, this method is called to validate the value.
     * @param  string $key
     * @param  array  $value
     * @return array
     */
    public function validate($key, $value)
    {
        if (!is_array($value)) {
            return [];
        }

        $formattedConfig = [];
        foreach ($value as $id => $config) {
            if ($this->validConfigValues($config) === false || $id === '%ID%') {
                continue;
            }

            // If the country selects contains a wildcard, make sure it is the only entry.
            if (in_array('*', $config['country'])) {
                $config['country'] = ['*'];
            }

            $parsed = $this->parseValue($config['value']);

            $formattedConfig[] = [
                'country'   => array_map('sanitize_text_field', $config['country']),
                'min'       => $config['min'] == '' ? '' : abs($config['min']),
                'max'       => $config['max'] == '' ? '' : abs($config['max']),
                'type'      => sanitize_text_field($config['type']),
                'value'     => $parsed,
             ];
        }

        return $formattedConfig;
    }

    /**
     * Checks if the given value is considered valid for this setting.
     * @param  array $values
     * @return bool
     */
    protected function validConfigValues($values)
    {
        return isset($values['country']) && !empty($values['country'])
            && isset($values['type']) && !empty($values['type'])
            // Check if the type is known and supported
            && in_array($values['type'], ['fixed', 'percentage'])
            && isset($values['value']) && $values['value'] !== ''
            // Both min and max values have to be set
            && isset($values['min']) && isset($values['max'])
            // But they cannot be both an empty string
            && ($values['min'] !== '' || $values['max'] !== '');
    }

    /**
     * Return the current payment cost configuration. Does some additional checking,
     * to keep backward compatability.
     * @param  string $key
     * @return array
     */
    protected function getCurrentConfiguration($key)
    {
        $selected = $this->settings->{$key};

        // If the current configuration looks like a legacy config string, parse it.
        if (is_string($selected) && strpos($selected, ':') !== false) {
            $legacyCosts = new LegacyPaymentCosts($selected);
            $selected = $legacyCosts->format();
        }

        // If the config is considered empty, set it to an array so no error will occur.
        if (empty($selected) || !is_array($selected)) {
            $selected = [];
        }

        return $selected;
    }

    /**
     * Parses the value (cost) so it can be correctly cast to a float
     * @param  string $value
     * @return float
     */
    protected function parseValue($value)
    {
        // If the value contains a comma, replace it with a period.
        if (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }
}

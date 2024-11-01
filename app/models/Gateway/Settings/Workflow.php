<?php

namespace Tussendoor\Billink\Gateway\Settings;

use WC_Countries;
use Tussendoor\Billink\App;
use Tussendoor\Billink\Gateway\Gateway;
use Tussendoor\Billink\Gateway\Settings;

class Workflow
{
    protected $gateway;
    protected $settings;
    protected $template = '/setting.workflow.php';

    public function __construct(Gateway $gateway, Settings $settings)
    {
        $this->gateway = $gateway;
        $this->settings = $settings;
    }

    /**
     * Render the workflow setting for WP-Admin
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
        $countries = (new WC_Countries())->get_countries();
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
        foreach ($value as $config) {
            if ($this->validConfigValues($config) === false) {
                continue;
            }

            $isBusiness = isset($config['isBusiness']) && $config['isBusiness'] == '1' ? '1' : '0';

            $formattedConfig[] = [
                'country'           => sanitize_text_field($config['country']),
                'isBusiness'        => $isBusiness,
                'workflow_number'   => sanitize_text_field($config['workflow_number']),

             ];
        }

        return $formattedConfig;
    }

    /**
     * Check if the workflow configuration can be considered valid.
     * Checks if certain values are set
     * @param  array $values
     * @return bool
     */
    protected function validConfigValues($values)
    {
        return isset($values['country']) && !empty($values['country'])
            && isset($values['workflow_number']) && !empty($values['workflow_number']);
    }

    /**
     * Get the current configuration of this setting. If it does not exists,
     * returns an empty array
     * @param  string $key
     * @return array
     */
    protected function getCurrentConfiguration($key)
    {
        $selected = $this->settings->{$key};

        if (empty($selected) || !is_array($selected)) {
            $selected = [];
        }

        return $selected;
    }
}

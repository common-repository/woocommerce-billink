<?php

namespace Tussendoor\Billink\Helpers;

/**
 * Convert legacy payment costs to the new format
 */
class LegacyPaymentCosts
{
    protected $config;

    /**
     * Inject the legacy payment costs string into the class
     * @param string $string
     */
    public function __construct($string)
    {
        $this->config = $string;
    }

    /**
     * Try and format the legacy payment cost string to the new array format.
     * @return array
     */
    public function format()
    {
        if (empty($this->config)) {
            return [];
        }

        $legacyConfig = $this->formatConfig();
        $prevOrderAmount = null;
        $converted = [];

        foreach ($legacyConfig as $orderAmount => $costs) {
            if (empty($prevOrderAmount)) {
                $converted[] = [
                    'country'   => '*',
                    'min'       => $orderAmount,
                    'max'       => '',
                    'type'      => 'fixed',
                    'value'     => $costs,
                ];

                $prevOrderAmount = $orderAmount;

                continue;
            }

            $converted[] = [
                'country'   => '*',
                'min'       => $orderAmount,
                'max'       => $prevOrderAmount,
                'type'      => 'fixed',
                'value'     => $costs,
            ];

            $prevOrderAmount = $orderAmount;
        }

        // formatConfig() reverses the sorting, so we'll unreverse it.
        return array_reverse($converted);
    }

    /**
     * Formats the config string into an array.
     * @return array
     */
    protected function formatConfig()
    {
        // Get the rows by exploding the string by the ';' delimiter.
        $rows = explode(';', rtrim($this->config, ';'));
        $legacyConfig = [];

        // Per row there should be a defined order amount and associated costs. Put them
        // in a new array where the order amount is the key and the costs are the value.
        foreach ($rows as $row) {
            list($orderAmount, $costs) = explode(':', $row);
            $legacyConfig[(int) $orderAmount] = $costs;
        }

        // Sort the array by order amount in reverse.
        krsort($legacyConfig);

        return $legacyConfig;
    }
}

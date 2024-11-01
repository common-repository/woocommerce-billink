<?php

namespace Tussendoor\Billink\Gateway;

use Tussendoor\Billink\Customer\Customer;

class WorkflowNumberProcessor
{
    protected $settings;

    /**
     * Set an instance of Settings, so we can search it for the correct workflow.
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(Customer $customer)
    {
        return $this->process($customer);
    }

    /**
     * Find the correct workflow number for the given Customer. If none could be found,
     * the fallback workflow number is used.
     * @param  \Tussendoor\Billink\Customer\Customer $customer
     * @return string
     */
    public function process(Customer $customer)
    {
        $workflowConfig = $this->settings->extended_workflow;

        if (empty($workflowConfig) || !is_array($workflowConfig)) {
            return apply_filters('billink_workflow_processed', $this->settings->workflow, $customer, $workflowConfig);
        }

        foreach ($workflowConfig as $config) {
            if ($config['country'] !== $customer->address->country) {
                continue;
            }

            if (($config['isBusiness'] == '1') !== $customer->isBusiness()) {
                continue;
            }

            return apply_filters('billink_workflow_processed', $config['workflow_number'], $customer, $workflowConfig);
        }

        return apply_filters('billink_workflow_processed', $this->settings->workflow, $customer, $workflowConfig);
    }
}

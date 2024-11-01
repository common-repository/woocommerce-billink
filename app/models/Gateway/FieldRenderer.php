<?php

namespace Tussendoor\Billink\Gateway;

use Tussendoor\Billink\App;
use Tussendoor\Billink\Helpers\ParameterBag;

class FieldRenderer
{
    protected $fields;
    protected $request;
    protected $isBusiness = false;

    public function __construct($fields, ParameterBag $bag)
    {
        $this->request = $bag;
        $this->fields = $fields;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Renders the custom, extra fields for the checkout.
     * @return string
     */
    public function render()
    {
        $rendered = '';
        foreach ($this->fields as $fieldname => $arguments) {
            $arguments['return'] = true;

            if (isset($arguments['forBusiness']) && $arguments['forBusiness'] !== $this->customerIsBusiness()) {
                continue;
            }

            // Special case for Belgium customers: change the label to 'Ondernemingsnummer'.
            if ($fieldname == 'billink_chamber_of_commerce' && $this->request->get('billing_country') == 'BE') {
                /* translators: The special case for Belgium customers, changes the Chamber of Commerce label */
                $arguments['label'] = __('Ondernemingsnummer', 'woocommerce-gateway-billink');
            }

            // Add terms text above checkbox
            if ($fieldname == 'billink_accept') {
                $rendered .= "<p>".App::get('gateway.acceptTermsText')."</p>";
            }
            
            $rendered .= woocommerce_form_field(
                $fieldname,
                apply_filters('billink_render_field', $arguments, $fieldname, $this->isBusiness),
                $this->request->get($fieldname, '')
            );
        }

        return $rendered;
    }

    /**
     * Checks if the current request contains some variables which indicates
     * if the customer should be considered as a business.
     * @return bool
     */
    protected function customerIsBusiness()
    {
        return $this->request->has('billing_company')
            && $this->request->isNotEmpty('billing_company');
    }
}

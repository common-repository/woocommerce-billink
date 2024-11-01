<?php

namespace Tussendoor\Billink\Gateway;

use Tussendoor\Billink\Order\Fee;

class DescriptionRenderer
{
    protected $fee;
    protected $description;

    public function __construct($description, Fee $fee)
    {
        $this->fee = $fee;
        $this->description = $description;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render the Billink description for the checkout. Renders some variables, like costs.
     * @return string
     */
    public function render()
    {
        $feeAmount = wc_prices_include_tax() ?
            wc_price($this->fee->amount) :
            wc_price($this->fee->amount + (($this->fee->amount / 100) * $this->fee->vat));

        $description = strtr($this->description, [
            '%costs%'   => $feeAmount,
            '%vat%'     => $this->fee->vatSuffix,
        ]);

        return wpautop(wptexturize($description));
    }
}

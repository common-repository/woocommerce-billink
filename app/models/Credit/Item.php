<?php

namespace Tussendoor\Billink\Credit;

use Tussendoor\Billink\App;
use Tussendoor\Billink\Model;
use Tussendoor\Billink\Contracts\Formattable;
use Tussendoor\Billink\Contracts\Normalizable;

class Item extends Model implements Normalizable, Formattable
{
    /**
     * {@inheritdoc}
     * @return string
     */
    public static function getRootName()
    {
        return 'ITEM';
    }

    /**
     * {@inheritdoc}
     * @param  string $format
     * @return string
     */
    public function serialize($format = 'xml')
    {
        return App::get('serializer')->serialize($this, $format);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function normalize()
    {
        $description = strlen($this->description) > 254 ?
            substr($this->description, 0, 254) :
            $this->description;

        return array_filter([
            'INVOICENUMBER'     => $this->orderNumber,
            'CREDITAMOUNT'      => number_format($this->creditAmount, 2),
            'DESCRIPTION'       => \billink_xml_safe_value($description),
        ], function ($item) {
            return $item !== '' && $item !== null;
        });
    }
}

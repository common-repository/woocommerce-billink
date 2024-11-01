<?php

namespace Tussendoor\Billink\Status;

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
        return array_filter([
            // 'WORKFLOWNUMBER'    => $this->workflowNumber,
            'INVOICENUMBER'     => $this->orderNumber,
        ], function ($item) {
            return $item !== '' && $item !== null;
        });
    }
}

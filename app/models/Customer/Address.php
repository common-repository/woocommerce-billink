<?php

namespace Tussendoor\Billink\Customer;

use Tussendoor\Billink\Model;

class Address extends Model
{
    protected $validAttributes = [
        'street', 'number', 'extension', 'postalCode', 'city', 'country',
        'companyName', 'firstname', 'lastname',
    ];
}

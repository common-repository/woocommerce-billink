<?php

namespace Tussendoor\Billink\Payment;

use MabeEnum\Enum;

class StatusEnum extends Enum
{
    const NOT_EXISTS = -1;
    const SUCCESS = 200;
    const NOT_ALLOWED = 706;
    const ALREADY_PAID = 707;
    const DESCRIPTION_TOO_LONG = 708;
}

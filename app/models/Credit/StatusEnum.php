<?php

namespace Tussendoor\Billink\Credit;

use MabeEnum\Enum;

class StatusEnum extends Enum
{
    const NOT_EXISTS = -1;
    const SUCCESS = 200;
    const NOT_ALLOWED = 706;
    const NOT_POSSIBLE = 707;
    const NOT_POSSIBLE_2 = 708;
    const UNKNOWN_BANK_ACCOUNT = 709;
}

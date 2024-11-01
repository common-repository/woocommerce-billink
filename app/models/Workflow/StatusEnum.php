<?php

namespace Tussendoor\Billink\Workflow;

use MabeEnum\Enum;

class StatusEnum extends Enum
{
    const NOT_EXISTS = -1;
    const SUCCESS = 500;
    const ALREADY_STARTED = 707;
}

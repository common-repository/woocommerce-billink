<?php

use Tussendoor\Billink\Controllers\ErrorController;
use Tussendoor\Billink\Controllers\CreditController;
use Tussendoor\Billink\Controllers\WorkflowController;

return [
    'triggers'  => [
        'order.credit'                  => CreditController::class . '@credit',
        'order.workflow.start'          => WorkflowController::class . '@start',

        'order.credit.failed'           => ErrorController::class . '@orderCreditFailed',
        'order.processing.failed'       => ErrorController::class . '@orderProcessingFailed',
        'order.startworkflow.failed'    => ErrorController::class . '@orderStartWorkflowFailed',

    ],
];

<?php

namespace Tussendoor\Billink\Workflow;

use InvalidArgumentException;
use Tussendoor\Billink\Model;

class Invoice extends Model
{
    public function setCodeAttribute($status)
    {
        if (is_numeric($status)) {
            $status = StatusEnum::byValue((int) $status);
        }

        if (is_object($status) && $status instanceof StatusEnum) {
            return $status;
        }

        throw new InvalidArgumentException("Unkown status {$status}");
    }

    public function exists()
    {
        return $this->notExists() === false;
    }

    public function notExists()
    {
        return $this->code->is(StatusEnum::NOT_EXISTS());
    }

    public function success()
    {
        return $this->code->is(StatusEnum::SUCCESS());
    }

    public function alreadyStarted()
    {
        return $this->code->is(StatusEnum::ALREADY_STARTED());
    }

    public function getInvoiceNumberAttribute()
    {
        return $this->getAttributeFromArray('invoicenumber');
    }
}

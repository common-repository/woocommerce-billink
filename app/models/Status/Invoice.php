<?php

namespace Tussendoor\Billink\Status;

use InvalidArgumentException;
use Tussendoor\Billink\Model;

class Invoice extends Model
{
    public function setStatusAttribute($status)
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
        return $this->status->is(StatusEnum::NOT_EXISTS());
    }

    public function isPaid()
    {
        return $this->status->is(StatusEnum::PAID());
    }

    public function isUnpaid()
    {
        return $this->status->is(StatusEnum::UNPAID());
    }

    public function isOpen()
    {
        return $this->status->is(StatusEnum::OPEN());
    }

    public function isPaidOut()
    {
        return $this->paidout !== null && $this->paidout === 1;
    }

    public function isNotPaidOut()
    {
        return $this->paidout !== null && $this->paidout === 0;
    }

    public function getInvoiceNumberAttribute()
    {
        return $this->getAttributeFromArray('invoicenumber');
    }

    public function workflowHasStarted()
    {
        return strpos($this->description, 'No steps given') === false;
    }
}

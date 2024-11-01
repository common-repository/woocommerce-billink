<?php

namespace Tussendoor\Billink\Payment;

use InvalidArgumentException;
use Tussendoor\Billink\Model;

class Invoice extends Model
{
    public function setCodeAttribute($code)
    {
        if (is_numeric($code)) {
            $code = StatusEnum::byValue((int) $code);
        }

        if (is_object($code) && $code instanceof StatusEnum) {
            return $code;
        }

        throw new InvalidArgumentException("Unkown code {$code}");
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

    public function notAllowed()
    {
        return $this->code->is(StatusEnum::NOT_ALLOWED());
    }

    public function alreadyPaid()
    {
        return $this->code->is(StatusEnum::ALREADY_PAID());
    }

    public function descriptionTooLong()
    {
        return $this->code->is(StatusEnum::DESCRIPTION_TOO_LONG());
    }

    public function getInvoiceNumberAttribute()
    {
        return $this->getAttributeFromArray('invoicenumber');
    }
}

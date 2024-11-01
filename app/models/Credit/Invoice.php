<?php

namespace Tussendoor\Billink\Credit;

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

    public function notPossible()
    {
        return $this->code->is(StatusEnum::NOT_POSSIBLE()) ||
            $this->code->is(StatusEnum::NOT_POSSIBLE_2()) ;
    }

    public function unkownBankAccount()
    {
        return $this->code->is(StatusEnum::UNKNOWN_BANK_ACCOUNT());
    }

    public function getInvoiceNumberAttribute()
    {
        return $this->getAttributeFromArray('invoicenumber');
    }
}

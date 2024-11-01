<?php

namespace Tussendoor\Billink\Payment;

use InvalidArgumentException;
use Tussendoor\Billink\Helpers\Collection;

/**
 * Extend the default Collection class, but add an Payment type check to any
 * method that adds a new item to the Collection.
 */
final class PaymentList extends Collection
{
    /**
     * {@inheritdoc}
     * @param  mixed $value
     * @return $this
     */
    public function append($value)
    {
        if (!$value instanceof Payment) {
            throw new InvalidArgumentException(
                $this->generateInvalidClassErrorMessage(Payment::class, $value)
            );
        }

        return parent::append($value);
    }

    /**
     * {@inheritdoc}
     * @param  mixed $value
     * @return $this
     */
    public function push($name, $value)
    {
        if (!$value instanceof Payment) {
            throw new InvalidArgumentException(
                $this->generateInvalidClassErrorMessage(Payment::class, $value)
            );
        }

        return parent::push($name, $value);
    }

    /**
     * {@inheritdoc}
     * @param  mixed $value
     * @return $this
     */
    public function replace($name, $value)
    {
        if (!$value instanceof Payment) {
            throw new InvalidArgumentException(
                $this->generateInvalidClassErrorMessage(Payment::class, $value)
            );
        }

        return parent::replace($name, $value);
    }

    /**
     * {@inheritdoc}
     * @param  mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        if (!$value instanceof Payment) {
            throw new InvalidArgumentException(
                $this->generateInvalidClassErrorMessage(Payment::class, $value)
            );
        }

        return parent::set($name, $value);
    }

    /**
     * Generate a error message when the required class instance does not match
     * the given class instance.
     * @param  string $required FQCN
     * @param  object $given
     * @return string
     */
    protected function generateInvalidClassErrorMessage($required, $given)
    {
        return sprintf(
            "%s only accepts instances of %s, %s given",
            PaymentList::class,
            $required,
            is_object($given) ? "instance of " . get_class($given) : gettype($required)
        );
    }
}

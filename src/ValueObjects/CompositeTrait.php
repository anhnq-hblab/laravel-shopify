<?php

namespace Osiset\ShopifyApp\ValueObjects;

/**
 * Trait for composite value objects.
 * Replaces Funeralzone\ValueObjects\CompositeTrait
 */
trait CompositeTrait
{
    /**
     * Check if this value object is null.
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return false;
    }

    /**
     * Check if the value is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * Check if this value object is the same as another.
     *
     * @param ValueObject $other
     * @return bool
     */
    public function isSame(ValueObject $other): bool
    {
        if (!$other instanceof static) {
            return false;
        }

        return $this->toNative() === $other->toNative();
    }
}

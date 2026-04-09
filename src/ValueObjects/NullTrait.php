<?php

namespace Osiset\ShopifyApp\ValueObjects;

/**
 * Trait for null value objects.
 * Replaces Funeralzone\ValueObjects\NullTrait
 */
trait NullTrait
{
    /**
     * Create a null value object.
     *
     * @param null $native
     * @return static
     */
    public static function fromNative($native = null): self
    {
        return new static();
    }

    /**
     * Convert to native null value.
     *
     * @return null
     */
    public function toNative()
    {
        return null;
    }

    /**
     * Check if this value object is null.
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return true;
    }

    /**
     * Check if this value object is the same as another.
     *
     * @param ValueObject $other
     * @return bool
     */
    public function isSame(ValueObject $other): bool
    {
        return $other instanceof self;
    }

    /**
     * Check if the value is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return true;
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }
}

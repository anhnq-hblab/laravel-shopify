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
     * @param int|string|array|object|null $native
     * @return static
     */
    public static function fromNative(int|string|array|object|null $native = null): static
    {
        return new static();
    }

    /**
     * Convert to native null value.
     *
     * @return null
     */
    public function toNative(): mixed
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

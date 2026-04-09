<?php

namespace Osiset\ShopifyApp\ValueObjects;

/**
 * Base interface for value objects.
 * Replaces Funeralzone\ValueObjects\ValueObject
 */
interface ValueObject
{
    /**
     * Create a value object from a native value.
     *
     * @param mixed $native
     * @return static
     */
    public static function fromNative($native);

    /**
     * Convert the value object to its native representation.
     *
     * @return mixed
     */
    public function toNative();

    /**
     * Check if this value object is the same as another.
     *
     * @param ValueObject $other
     * @return bool
     */
    public function isSame(ValueObject $other): bool;
}

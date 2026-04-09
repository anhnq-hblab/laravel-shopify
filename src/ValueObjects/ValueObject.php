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
     * @param int|string|array|object|null $native
     * @return static
     */
    public static function fromNative(int|string|array|object|null $native): static;

    /**
     * Convert the value object to its native representation.
     *
     * @return mixed
     */
    public function toNative(): mixed;

    /**
     * Check if this value object is the same as another.
     *
     * @param ValueObject $other
     * @return bool
     */
    public function isSame(ValueObject $other): bool;
}

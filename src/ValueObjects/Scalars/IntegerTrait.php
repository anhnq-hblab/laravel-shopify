<?php

namespace Osiset\ShopifyApp\ValueObjects\Scalars;

use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * Trait for integer-based value objects.
 * Replaces Funeralzone\ValueObjects\Scalars\IntegerTrait
 */
trait IntegerTrait
{
    /**
     * The integer value.
     *
     * @var int
     */
    protected $integer;

    /**
     * Create a value object from a native integer.
     *
     * @param int $integer
     * @return static
     */
    public static function fromNative(int $integer): self
    {
        return new static($integer);
    }

    /**
     * Convert the value object to its native integer representation.
     *
     * @return int
     */
    public function toNative(): int
    {
        return $this->integer;
    }

    /**
     * Check if this value object is the same as another.
     *
     * @param ValueObject $other
     * @return bool
     */
    public function isSame(ValueObject $other): bool
    {
        return $this->toNative() === $other->toNative();
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->toNative();
    }
}

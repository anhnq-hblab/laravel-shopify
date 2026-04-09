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
     * Constructor.
     *
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->integer = $value;
    }

    /**
     * Create a value object from a native integer.
     *
     * @param int|string|array|object|null $native
     * @return static
     */
    public static function fromNative(int|string|array|object|null $native): static
    {
        return new static((int) $native);
    }

    /**
     * Convert the value object to its native integer representation.
     *
     * @return int|null
     */
    public function toNative(): mixed
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

    /**
     * Check if this value object is null.
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return false;
    }
}

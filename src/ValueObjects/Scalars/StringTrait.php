<?php

namespace Osiset\ShopifyApp\ValueObjects\Scalars;

use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * Trait for string-based value objects.
 * Replaces Funeralzone\ValueObjects\Scalars\StringTrait
 */
trait StringTrait
{
    /**
     * The string value.
     *
     * @var string
     */
    protected $string;

    /**
     * Constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->string = $value;
    }

    /**
     * Create a value object from a native string.
     *
     * @param int|string|array|object|null $native
     * @return static
     */
    public static function fromNative(int|string|array|object|null $native): static
    {
        return new static((string) $native);
    }

    /**
     * Convert the value object to its native string representation.
     *
     * @return string|null
     */
    public function toNative(): mixed
    {
        return $this->string;
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
        return $this->toNative();
    }

    /**
     * Check if the value is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->toNative() === '';
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

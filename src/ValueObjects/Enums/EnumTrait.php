<?php

namespace Osiset\ShopifyApp\ValueObjects\Enums;

use BadMethodCallException;
use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * Trait for enum-based value objects.
 * Replaces Funeralzone\ValueObjects\Enums\EnumTrait
 */
trait EnumTrait
{
    /**
     * The enum value.
     *
     * @var int
     */
    protected $enum;

    /**
     * Constructor.
     *
     * @param int $enum
     */
    public function __construct(int $enum)
    {
        $this->enum = $enum;
    }

    /**
     * Create a value object from a native integer.
     *
     * @param int $enum
     * @return static
     */
    public static function fromNative(int $enum): self
    {
        return new static($enum);
    }

    /**
     * Convert the value object to its native integer representation.
     *
     * @return int
     */
    public function toNative(): int
    {
        return $this->enum;
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
     * Handle static calls for enum constants.
     *
     * @param string $name
     * @param array $arguments
     * @return static
     * @throws BadMethodCallException
     */
    public static function __callStatic(string $name, array $arguments): self
    {
        $constant = @constant(static::class . '::' . $name);

        if ($constant === null) {
            throw new BadMethodCallException("No static method or enum constant '{$name}' in class " . static::class);
        }

        return new static($constant);
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

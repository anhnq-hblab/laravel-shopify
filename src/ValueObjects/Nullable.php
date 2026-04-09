<?php

namespace Osiset\ShopifyApp\ValueObjects;

/**
 * Base class for nullable value objects.
 * Replaces Funeralzone\ValueObjects\Nullable
 */
abstract class Nullable implements ValueObject
{
    /**
     * The wrapped value object.
     *
     * @var ValueObject
     */
    protected $valueObject;

    /**
     * Constructor.
     *
     * @param ValueObject $valueObject
     */
    public function __construct(ValueObject $valueObject)
    {
        $this->valueObject = $valueObject;
    }

    /**
     * Get the class name for non-null implementation.
     *
     * @return string
     */
    abstract protected static function nonNullImplementation(): string;

    /**
     * Get the class name for null implementation.
     *
     * @return string
     */
    abstract protected static function nullImplementation(): string;

    /**
     * Create a nullable value object from a native value.
     *
     * @param mixed $native
     * @return static
     */
    public static function fromNative($native): self
    {
        if ($native === null) {
            $nullClass = static::nullImplementation();
            return new static(new $nullClass());
        }

        $nonNullClass = static::nonNullImplementation();
        return new static($nonNullClass::fromNative($native));
    }

    /**
     * Convert to native value.
     *
     * @return mixed
     */
    public function toNative()
    {
        return $this->valueObject->toNative();
    }

    /**
     * Check if this value object is the same as another.
     *
     * @param ValueObject $other
     * @return bool
     */
    public function isSame(ValueObject $other): bool
    {
        return $this->valueObject->isSame($other);
    }

    /**
     * Check if the value is null.
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->valueObject instanceof NullValueObject || $this->valueObject->isNull();
    }

    /**
     * Check if the value is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->valueObject->isEmpty();
    }

    /**
     * Get the inner value object.
     *
     * @return ValueObject
     */
    public function getValue(): ValueObject
    {
        return $this->valueObject;
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->valueObject;
    }
}

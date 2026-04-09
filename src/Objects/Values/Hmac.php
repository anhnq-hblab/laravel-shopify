<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\Scalars\StringTrait;
use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * Value object for HMAC.
 */
final class Hmac implements ValueObject
{
    use StringTrait;

    /**
     * {@inheritDoc}
     */
    public function isSame(ValueObject $object): bool
    {
        return hash_equals($this->toNative(), $object->toNative());
    }
}

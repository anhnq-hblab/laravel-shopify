<?php

namespace Osiset\ShopifyApp\ValueObjects;

/**
 * Base null value object class.
 * Used internally by Nullable for type checking.
 */
abstract class NullValueObject implements ValueObject
{
    use NullTrait;
}

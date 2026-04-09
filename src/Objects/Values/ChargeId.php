<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\Scalars\IntegerTrait;
use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * Value object for charge ID.
 */
final class ChargeId implements ValueObject
{
    use IntegerTrait;
}

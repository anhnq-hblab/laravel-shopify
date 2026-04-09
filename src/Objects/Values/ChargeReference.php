<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\Scalars\IntegerTrait;
use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * Value object for Shopify charge ID.
 */
final class ChargeReference implements ValueObject
{
    use IntegerTrait;
}

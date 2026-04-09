<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\Scalars\IntegerTrait;
use Osiset\ShopifyApp\Contracts\Objects\Values\PlanId as PlanIdValue;

/**
 * Value object for plan's ID.
 */
final class PlanId implements PlanIdValue
{
    use IntegerTrait;
}

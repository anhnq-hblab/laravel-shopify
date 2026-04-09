<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\NullTrait;
use Osiset\ShopifyApp\Contracts\Objects\Values\PlanId as PlanIdValue;

/**
 * Value object for plan's ID (null).
 */
final class NullPlanId implements PlanIdValue
{
    use NullTrait;
}

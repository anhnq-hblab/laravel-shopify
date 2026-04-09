<?php

namespace Osiset\ShopifyApp\Objects\Enums;

use Osiset\ShopifyApp\ValueObjects\Enums\EnumTrait;
use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * API types for plans.
 *
 * @method static PlanType RECURRING()
 * @method static PlanType ONETIME()
 */
final class PlanType implements ValueObject
{
    use EnumTrait;

    /**
     * Plan: Recurring.
     *
     * @var int
     */
    public const RECURRING = 0;

    /**
     * Plan: One-time.
     *
     * @var int
     */
    public const ONETIME = 1;
}

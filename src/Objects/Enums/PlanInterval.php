<?php

namespace Osiset\ShopifyApp\Objects\Enums;

use Osiset\ShopifyApp\ValueObjects\Enums\EnumTrait;
use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * Plan interval with annual support.
 *
 * @method static PlanInterval EVERY_30_DAYS()
 * @method static PlanInterval ANNUAL()
 */
class PlanInterval implements ValueObject
{
    use EnumTrait;

    /**
     * Interval: Monthly.
     *
     * @var int
     */
    public const EVERY_30_DAYS = 1;

    /**
     * Interval: Annual.
     *
     * @var int
     */
    public const ANNUAL = 2;
}

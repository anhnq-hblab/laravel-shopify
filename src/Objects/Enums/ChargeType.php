<?php

namespace Osiset\ShopifyApp\Objects\Enums;

use Osiset\ShopifyApp\ValueObjects\Enums\EnumTrait;
use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * API types for charges.
 *
 * @method static ChargeType RECURRING()
 * @method static ChargeType CHARGE()
 * @method static ChargeType ONETIME()
 * @method static ChargeType USAGE()
 * @method static ChargeType CREDIT()
 */
final class ChargeType implements ValueObject
{
    use EnumTrait;

    /**
     * Charge: Recurring.
     *
     * @var int
     */
    public const RECURRING = 0;

    /**
     * Charge: One-time.
     *
     * @var int
     */
    public const CHARGE = 1;

    /**
     * Charge: Alias for onetime.
     *
     * @var int
     */
    public const ONETIME = 1;

    /**
     * Charge: Usage.
     *
     * @var int
     */
    public const USAGE = 2;

    /**
     * Charge: Credit.
     *
     * @var int
     */
    public const CREDIT = 3;
}

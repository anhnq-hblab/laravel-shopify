<?php

namespace Osiset\ShopifyApp\Objects\Enums;

use Osiset\ShopifyApp\ValueObjects\Enums\EnumTrait;
use Osiset\ShopifyApp\ValueObjects\ValueObject;

class FrontendType implements ValueObject
{
    use EnumTrait;

    /**
     * @var int
     */
    public const MPA = 0;

    /**
     * @var int
     */
    public const SPA = 1;
}

<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\Scalars\IntegerTrait;
use Osiset\ShopifyApp\Contracts\Objects\Values\ThemeId as ThemeIdValue;

/**
 * Value object for theme's ID.
 */
final class ThemeId implements ThemeIdValue
{
    use IntegerTrait;
}

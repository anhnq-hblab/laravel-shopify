<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\Scalars\StringTrait;
use Osiset\ShopifyApp\Contracts\Objects\Values\ThemeRole as ThemeRoleValue;

/**
 * Value object for theme's role.
 */
final class ThemeRole implements ThemeRoleValue
{
    use StringTrait;
}

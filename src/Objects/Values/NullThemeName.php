<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\NullTrait;
use Osiset\ShopifyApp\Contracts\Objects\Values\ThemeName as ThemeNameValue;

/**
 * Value object for theme's name (null).
 */
final class NullThemeName implements ThemeNameValue
{
    use NullTrait;
}

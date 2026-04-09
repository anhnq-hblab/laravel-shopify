<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\NullTrait;
use Osiset\ShopifyApp\Contracts\Objects\Values\SessionToken as SessionTokenValue;

/**
 * Value object for session token (null).
 */
final class NullSessionToken implements SessionTokenValue
{
    use NullTrait;
}

<?php

namespace Osiset\ShopifyApp\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\NullTrait;
use Osiset\ShopifyApp\Contracts\Objects\Values\SessionId as SessionIdValue;

/**
 * Value object for session ID of a session token (null).
 */
final class NullSessionId implements SessionIdValue
{
    use NullTrait;
}

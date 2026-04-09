<?php

namespace Osiset\ShopifyApp\Contracts\Objects\Values;

use Osiset\ShopifyApp\ValueObjects\ValueObject;

/**
 * Access token value object.
 */
interface AccessToken extends ValueObject
{
    /**
     * Detects if the string is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool;
}

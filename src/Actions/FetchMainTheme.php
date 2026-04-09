<?php

namespace Osiset\ShopifyApp\Actions;

use Illuminate\Support\Facades\Cache;
use Osiset\ShopifyApp\Contracts\ShopModel;
use Osiset\ShopifyApp\Util;

/**
 * Fetches the main theme for a shop using GraphQL.
 */
class FetchMainTheme
{
    /**
     * Cache key prefix.
     */
    private const CACHE_KEY_PREFIX = 'main_theme';

    /**
     * Execute the action.
     *
     * @param ShopModel $shop The shop model.
     *
     * @return array|null Array with theme id and name, or null if not found.
     */
    public function __invoke(ShopModel $shop): ?array
    {
        $cacheInterval = (string) \Illuminate\Support\Str::of(Util::getShopifyConfig('theme_support.cache_interval'))
            ->plural()
            ->ucfirst()
            ->start('add');

        $cacheDuration = Util::getShopifyConfig('theme_support.cache_duration');

        return Cache::remember(
            $this->cacheKey($shop),
            now()->{$cacheInterval}($cacheDuration),
            function () use ($shop) {
                $response = $shop->api()->graph('
                    query {
                        themes(first: 1, roles: MAIN) {
                            nodes {
                                id
                                name
                            }
                        }
                    }
                ');

                if ($response['errors'] || blank(data_get($response['body']->toArray(), 'data.themes.nodes.0'))) {
                    return null;
                }

                $theme = data_get($response['body'], 'data.themes.nodes.0');

                return [
                    'id' => str_replace('gid://shopify/Theme/', '', $theme['id']),
                    'name' => $theme['name'],
                ];
            }
        );
    }

    /**
     * Generate cache key for the shop.
     *
     * @param ShopModel $shop The shop model.
     *
     * @return string
     */
    private function cacheKey(ShopModel $shop): string
    {
        return sprintf('%s.%s', $shop->getDomain()->toNative(), self::CACHE_KEY_PREFIX);
    }
}

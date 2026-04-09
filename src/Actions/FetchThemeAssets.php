<?php

namespace Osiset\ShopifyApp\Actions;

use Illuminate\Support\Facades\Cache;
use Osiset\ShopifyApp\Contracts\ShopModel;
use Osiset\ShopifyApp\Util;

/**
 * Fetches specific theme files using GraphQL.
 */
class FetchThemeAssets
{
    /**
     * Cache key prefix.
     */
    private const CACHE_KEY_PREFIX = 'theme_files';

    /**
     * Execute the action.
     *
     * @param ShopModel $shop      The shop model.
     * @param string    $themeId   The theme ID.
     * @param array     $filenames Array of file names to fetch.
     *
     * @return array Array of file objects with filename and body.
     */
    public function __invoke(ShopModel $shop, string $themeId, array $filenames): array
    {
        $cacheInterval = (string) \Illuminate\Support\Str::of(Util::getShopifyConfig('theme_support.cache_interval'))
            ->plural()
            ->ucfirst()
            ->start('add');

        $cacheDuration = Util::getShopifyConfig('theme_support.cache_duration');

        return Cache::remember(
            $this->cacheKey($shop, $themeId, $filenames),
            now()->{$cacheInterval}($cacheDuration),
            function () use ($shop, $themeId, $filenames) {
                $response = $shop->api()->graph('
                    query($id: ID!, $filenames: [String!]!) {
                        theme(id: $id) {
                            files(filenames: $filenames) {
                                nodes {
                                    filename
                                    body {
                                        ... on OnlineStoreThemeFileBodyText {
                                            content
                                        }
                                    }
                                }
                            }
                        }
                    }
                ', [
                    'id' => "gid://shopify/Theme/{$themeId}",
                    'filenames' => $filenames,
                ]);

                if ($response['errors'] || blank(data_get($response['body']->toArray(), 'data.theme.files.nodes'))) {
                    return [];
                }

                $files = data_get($response['body'], 'data.theme.files.nodes');

                return array_map(function ($file) {
                    return [
                        'filename' => $file['filename'],
                        'body' => $file['body']['content'] ?? null,
                    ];
                }, $files->toArray());
            }
        );
    }

    /**
     * Generate cache key for the theme files.
     *
     * @param ShopModel $shop      The shop model.
     * @param string    $themeId   The theme ID.
     * @param array     $filenames Array of file names.
     *
     * @return string
     */
    private function cacheKey(ShopModel $shop, string $themeId, array $filenames): string
    {
        $filenamesHash = md5(implode(',', $filenames));

        return sprintf('%s.%s.%s.%s', $shop->getDomain()->toNative(), self::CACHE_KEY_PREFIX, $themeId, $filenamesHash);
    }
}

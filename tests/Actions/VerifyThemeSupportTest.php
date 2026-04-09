<?php

namespace Osiset\ShopifyApp\Test\Actions;

use Illuminate\Support\Facades\Cache;
use Osiset\ShopifyApp\Actions\VerifyThemeSupport;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Objects\Enums\ThemeSupportLevel;
use Osiset\ShopifyApp\Objects\Values\ShopId;
use Osiset\ShopifyApp\Test\TestCase;
use Osiset\ShopifyApp\Test\Stubs\Api as ApiStub;

class VerifyThemeSupportTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testStoreWithUndefinedMainTheme(): void
    {
        $shop = factory($this->model)->create();
        $this->fakeGraphqlApi(['empty_theme']);

        $action = $this->app->make(VerifyThemeSupport::class);

        $result = call_user_func(
            $action,
            ShopId::fromNative($shop->id)
        );

        $this->assertNotNull($result);
        $this->assertEquals(ThemeSupportLevel::UNSUPPORTED, $result);
    }

    public function testStoreWithFullExtensionSupport(): void
    {
        $shop = factory($this->model)->create();
        // 3 API calls: FetchMainTheme, FetchThemeAssets (templates), FetchThemeAssets (sections)
        $this->fakeGraphqlApi(['main_theme', 'theme_sections', 'theme_sections']);

        $action = $this->app->make(VerifyThemeSupport::class);

        $result = call_user_func(
            $action,
            ShopId::fromNative($shop->id)
        );

        $this->assertNotNull($result);
        $this->assertEquals(ThemeSupportLevel::FULL, $result);
    }

    public function testStoreWithPartialExtensionSupport(): void
    {
        $shop = factory($this->model)->create();

        // First call: main_theme, Second call: partial templates (only product has @app), Third: same data for sections
        $partialTemplates = [
            'data' => [
                'theme' => [
                    'files' => [
                        'nodes' => [
                            ['filename' => 'templates/product.json', 'body' => ['content' => '{"name":"Product","sections":{"main":{"type":"product"}},"order":["main"]}']],
                            ['filename' => 'templates/collection.json', 'body' => ['content' => '{"name":"Collection","sections":{"main":{"type":"main-collection"}},"order":["main"]}']]
                        ]
                    ]
                ]
            ]
        ];
        $partialSections = [
            'data' => [
                'theme' => [
                    'files' => [
                        'nodes' => [
                            ['filename' => 'sections/product.liquid', 'body' => ['content' => '{% schema %}{"name":"Product","blocks":[{"type":"@app"}]}{% endschema %}']],
                            ['filename' => 'sections/main-collection.liquid', 'body' => ['content' => '{% schema %}{"name":"Collection","blocks":[{"type":"text"}]}{% endschema %}']]
                        ]
                    ]
                ]
            ]
        ];

        $this->fakeGraphqlApi(['main_theme', '_inline_templates_', '_inline_sections_']);
        ApiStub::$inlineResponses['_inline_templates_'] = $partialTemplates;
        ApiStub::$inlineResponses['_inline_sections_'] = $partialSections;

        $action = $this->app->make(VerifyThemeSupport::class);

        $result = call_user_func(
            $action,
            ShopId::fromNative($shop->id)
        );

        $this->assertNotNull($result);
        $this->assertEquals(ThemeSupportLevel::PARTIAL, $result);
    }

    public function testStoreWithoutExtensionSupport(): void
    {
        $shop = factory($this->model)->create();

        // No @app blocks at all
        $noSupportTemplates = [
            'data' => [
                'theme' => [
                    'files' => [
                        'nodes' => [
                            ['filename' => 'templates/product.json', 'body' => ['content' => '{"name":"Product","sections":{"main":{"type":"product"}},"order":["main"]}']]
                        ]
                    ]
                ]
            ]
        ];
        $noSupportSections = [
            'data' => [
                'theme' => [
                    'files' => [
                        'nodes' => [
                            ['filename' => 'sections/product.liquid', 'body' => ['content' => '{% schema %}{"name":"Product","blocks":[{"type":"text"}]}{% endschema %}']]
                        ]
                    ]
                ]
            ]
        ];

        $this->fakeGraphqlApi(['main_theme', '_inline_templates2_', '_inline_sections2_']);
        ApiStub::$inlineResponses['_inline_templates2_'] = $noSupportTemplates;
        ApiStub::$inlineResponses['_inline_sections2_'] = $noSupportSections;

        $action = $this->app->make(VerifyThemeSupport::class);

        $result = call_user_func(
            $action,
            ShopId::fromNative($shop->id)
        );

        $this->assertNotNull($result);
        $this->assertEquals(ThemeSupportLevel::UNSUPPORTED, $result);
    }
}

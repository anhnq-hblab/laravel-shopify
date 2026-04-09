<?php

namespace Osiset\ShopifyApp\Test\Actions;

use Osiset\ShopifyApp\Actions\VerifyThemeSupport;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Objects\Enums\ThemeSupportLevel;
use Osiset\ShopifyApp\Objects\Values\ShopId;
use Osiset\ShopifyApp\Test\TestCase;
use Osiset\ShopifyApp\Test\Stubs\ApiStub;

class VerifyThemeSupportTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
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
        $this->fakeGraphqlApi(['main_theme', 'theme_sections']);

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

        // Create a fixture with only one template having app block support
        $partialSections = [
            'data' => [
                'theme' => [
                    'files' => [
                        'nodes' => [
                            [
                                'filename' => 'templates/product.json',
                                'body' => [
                                    'content' => '{"name":"Product","sections":{"main":{"type":"product"}},"order":["main"]}'
                                ]
                            ],
                            [
                                'filename' => 'sections/product.liquid',
                                'body' => [
                                    'content' => '{% schema %}{"name":"Product","blocks":[{"type":"@app"}]}{% endschema %}'
                                ]
                            ],
                            [
                                'filename' => 'templates/collection.json',
                                'body' => [
                                    'content' => '{"name":"Collection","sections":{"main":{"type":"main-collection"}},"order":["main"]}'
                                ]
                            ],
                            [
                                'filename' => 'sections/main-collection.liquid',
                                'body' => [
                                    'content' => '{% schema %}{"name":"Collection","blocks":[{"type":"text"}]}{% endschema %}'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        ApiStub::stubResponses(['main_theme']);
        ApiStub::stubResponse(['body' => json_decode(json_encode($partialSections['data']['theme']['files']))]);

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

        // Create a fixture with no templates having app block support
        $noSupportSections = [
            'data' => [
                'theme' => [
                    'files' => [
                        'nodes' => [
                            [
                                'filename' => 'templates/product.json',
                                'body' => [
                                    'content' => '{"name":"Product","sections":{"main":{"type":"product"}},"order":["main"]}'
                                ]
                            ],
                            [
                                'filename' => 'sections/product.liquid',
                                'body' => [
                                    'content' => '{% schema %}{"name":"Product","blocks":[{"type":"text"}]}{% endschema %}'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        ApiStub::stubResponses(['main_theme']);
        ApiStub::stubResponse(['body' => json_decode(json_encode($noSupportSections['data']['theme']['files']))]);

        $action = $this->app->make(VerifyThemeSupport::class);

        $result = call_user_func(
            $action,
            ShopId::fromNative($shop->id)
        );

        $this->assertNotNull($result);
        $this->assertEquals(ThemeSupportLevel::UNSUPPORTED, $result);
    }

    /**
     * Fake GraphQL API responses for testing.
     *
     * @param array $fixtures Array of fixture names to return sequentially.
     *
     * @return void
     */
    protected function fakeGraphqlApi(array $fixtures): void
    {
        ApiStub::stubResponses($fixtures);
    }
}

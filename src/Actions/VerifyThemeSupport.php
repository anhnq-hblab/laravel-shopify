<?php

namespace Osiset\ShopifyApp\Actions;

use Illuminate\Support\Facades\Cache;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Objects\Enums\ThemeSupportLevel;
use Osiset\ShopifyApp\Objects\Values\ShopId;
use Osiset\ShopifyApp\Util;

class VerifyThemeSupport
{
    /**
     * Main theme role.
     */
    public const MAIN_ROLE = 'main';

    public function __construct(
        protected IShopQuery $shopQuery,
        protected FetchMainTheme $fetchMainTheme,
        protected FetchThemeAssets $fetchThemeAssets
    ) {
    }

    public function __invoke(ShopId $shopId): int
    {
        $shop = $this->shopQuery->getById($shopId);
        $mainTheme = ($this->fetchMainTheme)($shop);

        if (empty($mainTheme)) {
            return ThemeSupportLevel::UNSUPPORTED;
        }

        $templateFiles = $this->getTemplateFiles($shop, $mainTheme['id']);

        if (empty($templateFiles)) {
            return ThemeSupportLevel::UNSUPPORTED;
        }

        $sectionsWithAppBlock = $this->getSectionsWithAppBlock($shop, $mainTheme['id'], $templateFiles);

        $hasTemplates = count($templateFiles) > 0;
        $allTemplatesHaveAppBlock = count($templateFiles) === count($sectionsWithAppBlock);
        $templatesCountWithAppBlock = count($sectionsWithAppBlock);

        return match (true) {
            $hasTemplates && $allTemplatesHaveAppBlock => ThemeSupportLevel::FULL,
            $templatesCountWithAppBlock > 0 => ThemeSupportLevel::PARTIAL,
            default => ThemeSupportLevel::UNSUPPORTED,
        };
    }

    /**
     * Get template JSON files for configured templates.
     *
     * @param mixed  $shop    The shop model.
     * @param string $themeId The theme ID.
     *
     * @return array
     */
    private function getTemplateFiles($shop, string $themeId): array
    {
        $templates = Util::getShopifyConfig('theme_support.templates');
        $filenames = array_map(fn ($template) => "templates/{$template}.json", $templates);

        $files = ($this->fetchThemeAssets)($shop, $themeId, $filenames);

        return array_filter($files, function ($file) {
            return $file['body'] !== null && $this->jsonValidate($file['body']);
        });
    }

    /**
     * Get sections that contain @app block type from template files.
     *
     * @param mixed  $shop          The shop model.
     * @param string $themeId       The theme ID.
     * @param array  $templateFiles The template files with content.
     *
     * @return array
     */
    private function getSectionsWithAppBlock($shop, string $themeId, array $templateFiles): array
    {
        $sectionFilenames = [];
        $templateData = [];

        // Extract section types from templates
        foreach ($templateFiles as $file) {
            $content = json_decode($file['body'], true);
            if (! empty($content['sections'])) {
                foreach ($content['sections'] as $key => $section) {
                    if ($key === self::MAIN_ROLE || str_starts_with($section['type'], self::MAIN_ROLE)) {
                        $sectionFilenames[] = "sections/{$section['type']}.liquid";
                        $templateData[$file['filename']] = $section['type'];
                    }
                }
            }
        }

        if (empty($sectionFilenames)) {
            return [];
        }

        $sectionFiles = ($this->fetchThemeAssets)($shop, $themeId, array_unique($sectionFilenames));

        $sectionsWithAppBlock = [];

        foreach ($sectionFiles as $file) {
            if ($file['body'] !== null && $this->hasAppBlock($file['body'])) {
                // Map back to template
                foreach ($templateData as $templateFilename => $sectionType) {
                    if (str_contains($file['filename'], $sectionType)) {
                        $sectionsWithAppBlock[] = $templateFilename;
                        break;
                    }
                }
            }
        }

        return array_unique($sectionsWithAppBlock);
    }

    /**
     * Check if file content has @app block in schema.
     *
     * @param string $content The file content.
     *
     * @return bool
     */
    private function hasAppBlock(string $content): bool
    {
        preg_match('/\{%\s+schema\s+\%}([\s\S]*?)\{%\s+endschema\s+\%}/m', $content, $matches);

        if (empty($matches) || ! isset($matches[1])) {
            return false;
        }

        $schema = json_decode($matches[1], true);

        if (! $schema || ! isset($schema['blocks'])) {
            return false;
        }

        return in_array('@app', array_column($schema['blocks'], 'type'), true);
    }

    /**
     * Validate JSON string.
     *
     * @param string $json The JSON string to validate.
     *
     * @return bool
     */
    private function jsonValidate(string $json): bool
    {
        if (function_exists('json_validate')) {
            return json_validate($json);
        }

        json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

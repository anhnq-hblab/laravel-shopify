<?php

namespace Osiset\ShopifyApp\Test\Stubs;

use ErrorException;
use Exception;
use Gnikyt\BasicShopifyAPI\BasicShopifyAPI;
use Gnikyt\BasicShopifyAPI\ResponseAccess;
use Illuminate\Http\Response;

class Api extends BasicShopifyAPI
{
    public static $stubFiles = [];
    public static $inlineResponses = [];

    public static function stubResponses(array $stubFiles): void
    {
        self::$stubFiles = $stubFiles;
    }

    public static function stubResponse(array $response): void
    {
        // For inline responses, store as special marker
        self::$stubFiles[] = '_inline_';
        self::$inlineResponses['_inline_'] = $response;
    }

    public function rest(string $method, string $path, array $params = null, array $headers = [], bool $sync = true): array
    {
        $filename = array_shift(self::$stubFiles);

        // Handle inline responses from $inlineResponses
        if ($filename === '_inline_' && isset(self::$inlineResponses['_inline_'])) {
            $response = self::$inlineResponses['_inline_'];
        } else {
            try {
                $response = json_decode(file_get_contents(__DIR__."/../fixtures/{$filename}.json"), true);
            } catch (ErrorException $error) {
                throw new Exception("Missing fixture for {$method} @ {$path}, tried: '{$filename}.json'");
            }
        }

        $errors = false;
        $exception = null;
        if (isset($response['errors'])) {
            $errors = true;
            $exception = new Exception();
        }

        return [
            'errors' => $errors,
            'exception' => $exception,
            'body' => new ResponseAccess($response),
            'status' => Response::HTTP_OK,
        ];
    }

    public function graph(string $query, array $variables = [], bool $sync = true): array
    {
        $filename = array_shift(self::$stubFiles);

        // Handle inline responses from $inlineResponses (any key starting with _inline_)
        if (is_string($filename) && str_starts_with($filename, '_inline_') && isset(self::$inlineResponses[$filename])) {
            $response = self::$inlineResponses[$filename];
        } else {
            try {
                $response = json_decode(file_get_contents(__DIR__."/../fixtures/{$filename}.json"), true);
            } catch (ErrorException $error) {
                throw new Exception('Missing fixture for GraphQL call: ' . $filename);
            }
        }

        $errors = false;
        $exception = null;
        if (isset($response['errors'])) {
            $errors = $response['errors'];
            $exception = new Exception();
        }

        return [
            'errors' => $errors,
            'exception' => $exception,
            'response' => $response,
            'status' => Response::HTTP_OK,
            'body' => new ResponseAccess($response),
        ];
    }

    public function requestAccess(string $code): ResponseAccess
    {
        return new ResponseAccess(
            json_decode(file_get_contents(__DIR__.'/../fixtures/access_token.json'), true)
        );
    }
}

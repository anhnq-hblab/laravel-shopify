# Usage Guide

## Table of Contents

- [Authentication](#authentication)
- [Accessing the Shopify API](#accessing-the-shopify-api)
- [Shop Management](#shop-management)
- [Session Handling](#session-handling)
- [Blade Views](#blade-views)

## Authentication

The package handles Shopify OAuth automatically. After installation, your app will:

1. Redirect users to Shopify for authentication
2. Handle the OAuth callback
3. Store access tokens securely
4. Manage session tokens for embedded apps

### Default Routes

The package provides default authentication routes:

| Route | Purpose |
|-------|---------|
| `/` | Home route (requires `verify.shopify` middleware) |
| `/authenticate` | OAuth callback handler |
| `/billing/process` | Billing flow handler |

### Custom Authentication

For custom authentication flows, use the `verify.shopify` middleware:

```php
Route::get('/custom-route', function () {
    return view('custom');
})->middleware(['verify.shopify']);
```

## Accessing the Shopify API

### Basic Usage

Access the API through the `ShopifyApp` facade:

```php
use Osiset\ShopifyApp\Facades\ShopifyApp;

// Get the API helper
$api = ShopifyApp::api();

// Make REST API calls
$response = $api->rest('GET', '/admin/products.json', ['limit' => 10]);

// Make GraphQL queries
$response = $api->graph('
    query {
        shop {
            name
            myshopifyDomain
        }
    }
');
```

### API Helper Methods

The `ApiHelper` provides convenient methods for common operations:

```php
use Osiset\ShopifyApp\Services\ApiHelper;

// Get script tags
$scriptTags = $apiHelper->getScriptTags();

// Create a script tag
$apiHelper->createScriptTag([
    'event' => 'onload',
    'src' => 'https://your-domain.com/script.js',
]);

// Get webhooks
$webhooks = $apiHelper->getWebhooks();

// Create webhook
$apiHelper->createWebhook([
    'topic' => 'orders/create',
    'address' => 'https://your-domain.com/webhooks/orders-create',
]);
```

### REST vs GraphQL

The package supports both REST and GraphQL APIs:

**REST Example:**
```php
$response = $api->rest('GET', '/admin/orders.json', [
    'status' => 'any',
    'limit' => 50,
]);
$orders = $response['body']['orders'];
```

**GraphQL Example:**
```php
$query = '
    query GetOrders($first: Int!) {
        orders(first: $first) {
            edges {
                node {
                    id
                    name
                    totalPrice
                }
            }
        }
    }
';
$response = $api->graph($query, ['first' => 50]);
$orders = $response['body']['data']['orders']['edges'];
```

## Shop Management

### Current Shop

Access the authenticated shop through the user model:

```php
// In a controller with verify.shopify middleware
$user = auth()->user();
$shopDomain = $user->name; // myshopify.com domain
$shopToken = $user->password; // Access token (stored encrypted)

// Check if shop exists and is authenticated
if ($user->shop) {
    $shopData = $user->shop;
}
```

### Shop Model Contract

Your User model must implement `ShopModel` contract:

```php
use Osiset\ShopifyApp\Contracts\ShopModel;
use Osiset\ShopifyApp\Traits\ShopModel;

class User extends Authenticatable implements ShopModel
{
    use ShopModel;

    // Required: shopify_domain field
    // Required: shopify_token field
}
```

### Shop Commands

The package provides artisan commands for shop management:

```bash
# List all shops
php artisan shopify-app:shop:list

# Trigger a job for a specific shop
php artisan shopify-app:shop:trigger <shop-domain> <job-class>
```

## Session Handling

### Embedded App Sessions

For embedded apps using App Bridge, session tokens are handled automatically:

```javascript
// Frontend: Get session token
import { getSessionToken } from '@shopify/app-bridge-utils';

const sessionToken = await getSessionToken(app);

// Include in API requests
fetch('/api/data', {
    headers: {
        'Authorization': `Bearer ${sessionToken}`,
        'Content-Type': 'application/json',
    },
});
```

### SPA Mode

For Single Page Applications, configure in `.env`:

```env
SHOPIFY_FRONTEND_TYPE=SPA
```

In SPA mode, authentication is JWT-based:

```javascript
// React/Vue example: Store the token
const token = new URLSearchParams(window.location.search).get('token');
localStorage.setItem('shopifyToken', token);

// Use in API calls
const response = await fetch('/api/shopify-data', {
    headers: {
        'Authorization': `Bearer ${localStorage.getItem('shopifyToken')}`,
    },
});
```

### Session Token Middleware

For API routes in embedded apps, use token authentication:

```php
Route::middleware(['auth.token'])->group(function () {
    Route::get('/api/products', [ProductController::class, 'index']);
});
```

## Blade Views

### Passing Shop Data

Pass shop information to your Blade views:

```php
Route::get('/', function () {
    $shop = auth()->user();

    return view('home', [
        'shopDomain' => $shop->name,
        'apiKey' => config('shopify-app.api_key'),
    ]);
})->middleware(['verify.shopify']);
```

### App Bridge Integration

Include App Bridge in your Blade templates:

```html
<!DOCTYPE html>
<html>
<head>
    <meta name="shopify-api-key" content="{{ $apiKey }}">
    <meta name="shopify-domain" content="{{ $shopDomain }}">
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
</head>
<body>
    <div id="app"></div>

    <script>
        var AppBridge = window['app-bridge'];
        var createApp = AppBridge.default;

        var app = createApp({
            apiKey: '{{ $apiKey }}',
            host: new URLSearchParams(location.search).get("host"),
        });
    </script>
</body>
</html>
```

### Shopify Polaris

For apps using Shopify Polaris design system:

```html
<link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@10/build/esm/styles.css">
```

## Next Steps

- [Billing Guide](billing.md) - Implement app billing
- [Webhooks Guide](webhooks.md) - Handle Shopify webhooks

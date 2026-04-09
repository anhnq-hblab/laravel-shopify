# Webhooks Guide

## Table of Contents

- [Overview](#overview)
- [Configuration](#configuration)
- [Webhook Topics](#webhook-topics)
- [Automatic Registration](#automatic-registration)
- [Handling Webhooks](#handling-webhooks)
- [Job Classes](#job-classes)
- [GraphQL Webhooks](#graphql-webhooks)
- [HMAC Verification](#hmac-verification)
- [Testing Webhooks](#testing-webhooks)
- [Troubleshooting](#troubleshooting)

## Overview

Shopify webhooks allow your app to receive real-time notifications when events occur in a shop. The package provides:

- Automatic webhook registration
- HMAC verification middleware
- Pre-configured job classes for common topics
- GraphQL webhook subscription support

## Configuration

Configure webhooks in `config/shopify-app.php`:

```php
'webhooks' => [
    [
        'topic' => 'APP_UNINSTALLED',
        'address' => 'https://your-domain.com/webhooks/app-uninstalled'
    ],
    [
        'topic' => 'ORDERS_CREATE',
        'address' => 'https://your-domain.com/webhooks/orders-create'
    ],
],
```

For GraphQL webhooks (recommended):

```php
'webhooks' => [
    [
        'topic' => 'APP_UNINSTALLED', // Auto-converted to GraphQL format
        'address' => 'https://your-domain.com/webhooks/app-uninstalled'
    ],
],
```

## Webhook Topics

### Common Topics (2025-01 API Version)

| Topic | Description |
|-------|-------------|
| `APP_UNINSTALLED` | App was uninstalled from a shop |
| `SHOP_UPDATE` | Shop information was updated |
| `ORDERS_CREATE` | New order was created |
| `ORDERS_PAID` | Order was paid |
| `ORDERS_FULFILLED` | Order was fulfilled |
| `PRODUCTS_CREATE` | Product was created |
| `PRODUCTS_UPDATE` | Product was updated |
| `PRODUCTS_DELETE` | Product was deleted |
| `CUSTOMERS_CREATE` | Customer was created |
| `CUSTOMERS_UPDATE` | Customer was updated |
| `CHECKOUTS_CREATE` | Checkout was started |
| `CHECKOUTS_UPDATE` | Checkout was updated |

### Billing Topics

| Topic | Description |
|-------|-------------|
| `APP_SUBSCRIPTIONS_UPDATE` | Subscription status changed |
| `APP_PURCHASES_ONE_TIME_UPDATE` | One-time purchase updated |

### Inventory Topics

| Topic | Description |
|-------|-------------|
| `INVENTORY_LEVELS_UPDATE` | Inventory level changed |
| `INVENTORY_ITEMS_CREATE` | Inventory item created |

## Automatic Registration

### After Authentication

Webhooks are automatically registered after shop authentication. The package:

1. Compares configured webhooks with existing ones
2. Creates missing webhooks via GraphQL
3. Updates existing webhook URLs if changed
4. Removes orphaned webhooks (optional)

### Manual Registration

Trigger webhook registration manually:

```php
use Osiset\ShopifyApp\Actions\RegisterWebhooks;

$action = app(RegisterWebhooks::class);
$action($shop);
```

## Handling Webhooks

### Route Setup

Add webhook routes to `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;
use Osiset\ShopifyApp\Http\Controllers\WebhookController;

Route::post('/webhooks/{type}', [WebhookController::class, 'handle'])
    ->middleware(['auth.webhook'])
    ->name('shopify.webhook');
```

The `{type}` parameter maps to a job class.

### Webhook Controller

The package provides a default controller that:
1. Verifies HMAC signature
2. Dispatches to the appropriate job class
3. Returns 200 OK on success

### Custom Handlers

Create custom job classes for specific topics:

```php
<?php

namespace App\Jobs;

use Osiset\ShopifyApp\Messaging\Jobs\WebhookJob;

class OrdersCreateJob extends WebhookJob
{
    public function handle(): void
    {
        $shop = $this->shop;
        $data = $this->data;

        // Process order data
        Log::info("New order {$data['id']} for shop {$shop->name}");

        // Your custom logic here
    }
}
```

## Job Classes

### Job Naming Convention

Job classes must follow the naming pattern:

```
{Topic}Job
```

Examples:
- `AppUninstalledJob` for `APP_UNINSTALLED`
- `OrdersCreateJob` for `ORDERS_CREATE`
- `ProductsUpdateJob` for `PRODUCTS_UPDATE`

### App Uninstalled Job

Required for proper cleanup:

```php
<?php

namespace App\Jobs;

use Osiset\ShopifyApp\Messaging\Jobs\WebhookJob;
use Osiset\ShopifyApp\Contracts\ShopModel;

class AppUninstalledJob extends WebhookJob
{
    public function handle(): void
    {
        $shop = $this->shop;

        // Mark shop as uninstalled
        $shop->update(['shopify_installed' => false]);

        // Clean up data
        // Cancel subscriptions
        // Remove webhooks
        // etc.
    }
}
```

### Base WebhookJob Class

Extend the base class for automatic injection:

```php
abstract class WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ShopModel $shop;
    public array $data;

    public function __construct(
        ShopModel $shop,
        array $data
    ) {
        $this->shop = $shop;
        $this->data = $data;
    }

    abstract public function handle(): void;
}
```

## GraphQL Webhooks

### Creating Webhooks via GraphQL

The package automatically converts REST-format topics to GraphQL format:

```php
// Config: REST format (pre-v17 compatibility)
[
    'topic' => 'orders/create',     // REST format
    'address' => 'https://...'
]

// Automatically converted to:
// ORDERS_CREATE for GraphQL
```

### Querying Existing Webhooks

```php
$apiHelper = app(ApiHelper::class);
$webhooks = $apiHelper->getWebhooks();

foreach ($webhooks['data']['webhookSubscriptions']['edges'] as $edge) {
    $node = $edge['node'];
    echo "Topic: {$node['topic']}, URL: {$node['endpoint']['callbackUrl']}\n";
}
```

### Deleting Webhooks

```php
$apiHelper->deleteWebhook('gid://shopify/WebhookSubscription/1234567890');
```

## HMAC Verification

### Middleware

The `auth.webhook` middleware verifies webhook authenticity:

```php
Route::post('/webhooks/{type}', [WebhookController::class, 'handle'])
    ->middleware(['auth.webhook']);
```

Verification process:
1. Extracts `X-Shopify-Hmac-SHA256` header
2. Calculates HMAC using app secret
3. Rejects requests with invalid signatures

### Custom Verification

Manual HMAC verification:

```php
use Osiset\ShopifyApp\Services\ApiHelper;

$apiHelper = app(ApiHelper::class);
$isValid = $apiHelper->verifyRequest($request->all());

if (!$isValid) {
    abort(401, 'Invalid HMAC');
}
```

## Testing Webhooks

### Shopify CLI

Use Shopify CLI to trigger test webhooks:

```bash
shopify webhook trigger --topic orders/create --address https://ngrok-url/webhooks/orders-create
```

### Local Testing with ngrok

1. Start ngrok tunnel:
```bash
ngrok http 8000
```

2. Update webhook URLs in config to ngrok URL

3. Register webhooks:
```bash
php artisan tinker
>>> app(Osiset\ShopifyApp\Actions\RegisterWebhooks::class)($shop);
```

### Test Payloads

Example test payload for orders/create:

```json
{
  "id": 1234567890,
  "name": "#1001",
  "email": "customer@example.com",
  "total_price": "99.99",
  "shop_id": 123456789
}
```

### Queue Configuration

For production, use Redis or database queue:

```env
QUEUE_CONNECTION=redis
```

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Webhooks not received | Check HTTPS URL, verify HMAC middleware |
| 401 Unauthorized | Verify HMAC signature, check app secret |
| Job not dispatched | Check job class name matches topic |
| Duplicate webhooks | Enable webhook cleanup in config |

### Debugging

Enable webhook logging:

```php
// In your job class
public function handle(): void
{
    Log::debug('Webhook received', [
        'shop' => $this->shop->name,
        'topic' => $this->topic,
        'data' => $this->data,
    ]);

    // Your logic
}
```

### Failed Job Handling

Configure failed job handling:

```php
// config/queue.php
'failed' => [
    'database' => 'mysql',
    'table' => 'failed_jobs',
],
```

Retry failed jobs:

```bash
php artisan queue:retry all
```

## Next Steps

- [Installation Guide](installation.md) - Full setup instructions
- [Usage Guide](usage.md) - API access and shop management
- [Development Guide](development.md) - Local development setup

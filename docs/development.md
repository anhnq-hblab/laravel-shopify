# Development Guide

## Table of Contents

- [Local Development Setup](#local-development-setup)
- [Using ngrok](#using-ngrok)
- [Shopify Partner Dashboard](#shopify-partner-dashboard)
- [Development Stores](#development-stores)
- [Testing](#testing)
- [Docker Testing](#docker-testing)
- [Debugging](#debugging)
- [Best Practices](#best-practices)

## Local Development Setup

### Requirements

- PHP 8.1, 8.2, 8.3, or 8.4
- Composer 2.0+
- Node.js 16+ (for frontend assets)
- ngrok (for Shopify tunnel)
- MySQL/PostgreSQL or SQLite

### Quick Start

1. **Create Laravel project:**

```bash
composer create-project laravel/laravel my-shopify-app
cd my-shopify-app
```

2. **Install the package:**

```bash
composer require kyon147/laravel-shopify
```

3. **Publish configuration:**

```bash
php artisan vendor:publish --tag=shopify-config
```

4. **Set up environment:**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure database:**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopify_app
DB_USERNAME=root
DB_PASSWORD=
```

6. **Run migrations:**

```bash
php artisan migrate
```

## Using ngrok

ngrok creates a public HTTPS URL that tunnels to your local server. Shopify requires HTTPS URLs.

### Installation

```bash
# macOS
brew install ngrok

# Windows
choco install ngrok

# Or download from https://ngrok.com/download
```

### Configuration

1. Sign up at https://ngrok.com
2. Get your authtoken
3. Configure:

```bash
ngrok config add-authtoken YOUR_TOKEN
```

### Running ngrok

Start your Laravel server:

```bash
php artisan serve
```

In another terminal, start ngrok:

```bash
ngrok http 8000
```

Output:
```
Forwarding  https://abc123.ngrok.io -> http://localhost:8000
```

Use the HTTPS URL (`https://abc123.ngrok.io`) in your Shopify app settings.

### ngrok Tips

**Use a static subdomain (paid feature):**

```bash
ngrok http --subdomain=myapp 8000
```

**View request logs:**

```bash
ngrok http 8000 --log=stdout
```

**Inspect traffic:**

Visit http://localhost:4040 to see all requests.

## Shopify Partner Dashboard

### Creating an App

1. Go to https://partners.shopify.com
2. Click "Apps" → "Create app"
3. Choose "Create app manually"

### Configuration

**App settings:**

| Setting | Value |
|---------|-------|
| App URL | `https://your-ngrok-url/` |
| Allowed redirection URL(s) | `https://your-ngrok-url/authenticate` |
| | `https://your-ngrok-url/billing/process` |

**Note:** Both URLs must use HTTPS.

### API Credentials

Get your API credentials from the Partner Dashboard:

1. Go to "Apps" → [Your App] → "Configuration"
2. Copy "Client ID" → `SHOPIFY_API_KEY`
3. Copy "Client secret" → `SHOPIFY_API_SECRET`

Add to `.env`:

```env
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
SHOPIFY_API_VERSION=2025-01
SHOPIFY_API_SCOPES=read_products,write_products,read_orders
```

## Development Stores

### Creating a Development Store

1. In Partner Dashboard, go to "Stores"
2. Click "Add store"
3. Select "Development store"
4. Fill in store details

### Installing Your App

1. In Partner Dashboard, go to "Apps" → [Your App]
2. Click "Select store"
3. Choose your development store
4. Install the app

### Testing Billing

Development stores allow free testing:

1. Enable test mode in plan config:
```php
'test' => true
```

2. Or use "Test billing" option in Partner Dashboard

## Testing

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html build/coverage

# Run specific test
vendor/bin/phpunit --filter TestName
```

### Writing Tests

Example test for a controller:

```php
<?php

namespace Tests\Feature;

use Osiset\ShopifyApp\Test\TestCase;

class ShopControllerTest extends TestCase
{
    public function testShopCanAccessDashboard(): void
    {
        $shop = $this->createShop();
        $this->actingAs($shop);

        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }
}
```

### Test Helpers

The package provides test helpers:

```php
// Create a mock shop
$shop = $this->createShop([
    'name' => 'test-shop.myshopify.com',
]);

// Mock API responses
$this->mockApiResponse('GET', '/admin/shop.json', [
    'shop' => ['name' => 'Test Shop'],
]);
```

## Docker Testing

### Quick Start

The package includes Docker testing for multiple PHP/Laravel versions:

```bash
cd docker-testing

# Test PHP 8.2 with Laravel 11
docker-compose run --rm php82-laravel11
```

### Available Combinations

| PHP Version | Laravel 10 | Laravel 11 | Laravel 12 | Laravel 13 |
|-------------|------------|------------|------------|------------|
| 8.1 | ✅ | ✅ | ❌ | ❌ |
| 8.2 | ✅ | ✅ | ✅ | ✅ |
| 8.3 | ✅ | ✅ | ✅ | ✅ |
| 8.4 | ✅ | ✅ | ✅ | ✅ |

### Running Tests

```bash
# Specific version
docker-compose run --rm php82-laravel11

# Run all tests (custom script)
docker-compose run --rm php82-laravel11 ./vendor/bin/phpunit
```

### Custom Docker Setup

Create your own Dockerfile:

```dockerfile
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
```

## Debugging

### Enable Debug Mode

```env
APP_DEBUG=true
APP_ENV=local
LOG_LEVEL=debug
```

### Shopify API Debugging

Log all API requests:

```php
// In a service provider
\Log::listen(function ($message) {
    if (str_contains($message, 'shopify')) {
        \Log::debug($message);
    }
});
```

### Common Issues

**OAuth redirect loop:**
- Check `SHOPIFY_API_KEY` and `SHOPIFY_API_SECRET` are correct
- Verify redirect URL is whitelisted in Partner Dashboard
- Ensure HTTPS is used

**HMAC verification failed:**
- Verify app secret is correct
- Check shop domain format (should include `.myshopify.com`)

**Session token issues:**
- Check cookie settings: `secure` and `same_site` in `config/session.php`
- Verify App Bridge is properly initialized

## Best Practices

### Security

1. **Never commit credentials:**
```gitignore
.env
.env.local
```

2. **Use HTTPS in production:**
```env
APP_URL=https://your-domain.com
```

3. **Validate all requests:**
```php
// Always use middleware
Route::middleware(['verify.shopify']);
```

### Performance

1. **Use queues for webhooks:**
```env
QUEUE_CONNECTION=redis
```

2. **Cache API responses:**
```php
$products = Cache::remember('products', 3600, function () {
    return $api->rest('GET', '/admin/products.json')['body']['products'];
});
```

### Code Organization

1. **Separate concerns:**
```
app/
├── Http/
│   └── Controllers/
│       └── Shopify/
│           ├── AuthController.php
│           ├── WebhookController.php
│           └── BillingController.php
├── Jobs/
│   ├── Shopify/
│   │   ├── AppUninstalledJob.php
│   │   └── OrdersCreateJob.php
```

2. **Use service classes:**
```php
class ShopifyProductService
{
    public function __construct(
        private ApiHelper $api
    ) {}

    public function create(array $data): array
    {
        return $this->api->rest('POST', '/admin/products.json', [
            'product' => $data
        ])['body']['product'];
    }
}
```

## Next Steps

- [Installation Guide](installation.md) - Production deployment
- [Troubleshooting Guide](troubleshooting.md) - Common issues
- [Billing Guide](billing.md) - Implement app billing

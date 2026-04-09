# Installation Guide

## Requirements

- PHP: 8.1, 8.2, 8.3, or 8.4
- Laravel: 10.x, 11.x, 12.x, or 13.x
- Composer 2.0+

## Installing the Package

### Step 1: Install via Composer

```bash
composer require kyon147/laravel-shopify
```

**Migrating from the original package?** First remove the old package:
```bash
composer remove osiset/laravel-shopify
composer require kyon147/laravel-shopify
```

### Step 2: Publish Configuration

Publish the config file:
```bash
php artisan vendor:publish --tag=shopify-config
```

This creates `config/shopify-app.php`.

### Step 3: Configure Environment Variables

Add to your `.env` file:

```env
SHOPIFY_APP_NAME="My Shopify App"
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
SHOPIFY_API_SCOPES=read_products,write_products
SHOPIFY_API_VERSION=2025-01
```

### Step 4: Configure Shopify Partner Dashboard

In your Shopify Partner dashboard:

1. **App URL:** `https://your-domain.com/`
2. **Allowed redirection URL(s):**
   - `https://your-domain.com/authenticate`
   - `https://your-domain.com/billing/process`

⚠️ **Important:** Both URLs must use HTTPS or you'll get:
```
Oauth error invalid_request: The redirect_uri is not whitelisted
```

### Step 5: Configure User Model

Your User model must implement the ShopModel contract:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Osiset\ShopifyApp\Contracts\ShopModel as ShopModelContract;
use Osiset\ShopifyApp\Traits\ShopModel;

class User extends Authenticatable implements ShopModelContract
{
    use ShopModel;

    // For Laravel 10+, remove password hashing cast:
    protected $casts = [
        // 'password' => 'hashed',  // COMMENT THIS OUT
    ];
}
```

### Step 6: Set Up Database

Run migrations:
```bash
php artisan migrate
```

Or publish migrations first:
```bash
php artisan vendor:publish --tag=shopify-migrations
php artisan migrate
```

### Step 7: Configure Routes

The package requires a route named `home`. Either:

**Option A:** Use the package's default home route (comment out your default route in `routes/web.php`)

**Option B:** Create your own with the `verify.shopify` middleware:
```php
Route::get('/', function () {
    return view('home');
})->middleware(['verify.shopify'])->name('home');
```

### Step 8: Configure CSRF (Required)

Disable CSRF for Shopify routes in `bootstrap/app.php` (Laravel 11+) or `app/Http/Middleware/VerifyCsrfToken.php`:

```php
// Laravel 11: bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        '*',  // All routes
    ]);
})

// Laravel 10: app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    '*',
];
```

### Step 9: Configure Cookie Settings (For Blade/MPA)

In `config/session.php`:
```php
'secure' => true,
'same_site' => 'none',
```

## Middleware Configuration

### Available Middlewares

| Middleware | Purpose |
|-----------|---------|
| `verify.shopify` | Authenticate shop and handle session tokens |
| `verify.shopify.scopes` | Verify API scopes |
| `auth.webhook` | Authenticate webhooks |
| `auth.proxy` | Authenticate proxy requests |
| `billable` | Force billing if enabled |

### Using Middleware

```php
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['verify.shopify']);

// With billing
Route::get('/premium', function () {
    return view('premium');
})->middleware(['verify.shopify', 'billable']);
```

## SPA (Single Page Application) Configuration

For React, Vue, or other SPA frameworks:

```env
SHOPIFY_FRONTEND_TYPE=SPA
```

**Note:** In SPA mode, you cannot use the `billable` middleware. Handle billing manually with JWT tokens.

## Queue Configuration

For production, configure Redis or database queue driver for webhooks and scripttags:

```env
QUEUE_CONNECTION=redis
```

## Theme App Extensions

Enable theme support in config:
```php
'theme_support' => [
    'templates' => ['product', 'collection', 'index'],
    'cache_interval' => 'hours',
    'cache_duration' => 12,
    'unacceptable_levels' => [
        Osiset\ShopifyApp\Objects\Enums\ThemeSupportLevel::UNSUPPORTED,
    ],
],
```

## Next Steps

- [Usage Guide](usage.md) - Learn how to use the API
- [Billing Guide](billing.md) - Implement app billing
- [Webhooks Guide](webhooks.md) - Handle Shopify webhooks

# Laravel Shopify App

![Tests](https://github.com/kyon147/laravel-shopify/workflows/Package%20Test/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/kyon147/laravel-shopify/branch/master/graph/badge.svg?token=qqUuLItqJj)](https://codecov.io/gh/kyon147/laravel-shopify)
[![License](https://poser.pugx.org/kyon147/laravel-shopify/license)](https://packagist.org/packages/kyon147/laravel-shopify)

----

This is a maintained version of the wonderful but now deprecated original [Laravel Shopify App](https://github.com/gnikyt/laravel-shopify/). To keep things clean, this has been detached from the original.

----

## Requirements

- **PHP:** 8.1, 8.2, 8.3, or 8.4
- **Laravel:** 10.x, 11.x, 12.x, or 13.x
- **Shopify API Version:** 2025-01 (configurable)

## Quick Start

```bash
composer require kyon147/laravel-shopify
```

Publish the config file:
```bash
php artisan vendor:publish --tag=shopify-config
```

## What's New

### Latest Updates (2025)

- ✅ **Multi-version Support:** PHP 8.1-8.4 and Laravel 10-13
- ✅ **Modern API:** Default Shopify API version updated to 2025-01
- ✅ **GraphQL Billing:** New `appSubscriptionCreate` mutation support
- ✅ **Type Safety:** Full PHP 8.0+ type declarations with union types
- ✅ **Docker Testing:** Multi-version testing environment included
- ✅ **Route Fixes:** Compatibility with Laravel 12.29.0+ route registration
- ✅ **Value Objects:** Internal implementation replacing deprecated dependencies

## Table of Contents

- [Installation](docs/installation.md)
- [Usage](docs/usage.md)
- [Billing](docs/billing.md)
- [Webhooks](docs/webhooks.md)
- [Development](docs/development.md)
- [Troubleshooting](docs/troubleshooting.md)
- [Changelog](../../wiki/Changelog)
- [Contributing Guide](CONTRIBUTING.md)
- [License](#license)

## Features

### Authentication
- Shopify OAuth 2.0 with offline and per-user access modes
- Automatic session token handling for embedded apps
- JWT token support for SPA applications

### Billing
- Recurring application charges
- One-time application charges
- Usage charges
- App credits
- Freemium mode support
- GraphQL App Subscription API

### Webhooks
- Automatic webhook registration
- GraphQL webhook subscriptions
- Pre-configured jobs for common topics:
  - `APP_UNINSTALLED`
  - `ORDERS_CREATE`, `ORDERS_PAID`
  - `APP_PURCHASES_ONE_TIME_SUBSCRIPTION_UPDATE`
  - And more...

### Theme Support
- Shopify Theme 2.0 compatibility detection
- Theme App Extensions support
- Automatic ScriptTags fallback for unsupported themes

### Security
- HMAC verification
- Content Security Policy (CSP) headers
- Iframe protection middleware
- Webhook authentication

## Documentation

For detailed documentation, see:
- [Installation Guide](docs/installation.md) - Setup and configuration
- [Usage Guide](docs/usage.md) - API access and shop management
- [Billing Guide](docs/billing.md) - Implementing app billing
- [Webhook Guide](docs/webhooks.md) - Handling Shopify webhooks
- [Development Guide](docs/development.md) - Local development with ngrok
- [Troubleshooting](docs/troubleshooting.md) - Common issues and solutions

## Docker Testing

This package includes a Docker testing environment for multiple PHP and Laravel versions:

```bash
cd docker-testing

# Test specific version
docker-compose run --rm php82-laravel11

# Available combinations:
# - PHP 8.1, 8.2, 8.3, 8.4
# - Laravel 10, 11, 12, 13
```

See [Docker Testing](docker-testing/README.md) for details.

## API Version

Default API version is `2025-01`. To change:

```env
SHOPIFY_API_VERSION=2025-04
```

See [Shopify API Versioning](https://shopify.dev/docs/api/usage/versioning) for details.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Maintainers

- [@kyon147](https://github.com/kyon147) - Current maintainer
- ~[@gnikyt](https://github.com/gnikyt)~ - Original author

Looking to become a maintainer? Contact @kyon147 directly.

## License

This project is released under the MIT [license](LICENSE).

## Special Thanks

Thank you to everyone who has contributed to this package through PRs, issue assistance, and feedback. This package is developed in spare time with a busy family/work life, and every contribution helps!

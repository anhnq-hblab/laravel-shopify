# Troubleshooting Guide

## Table of Contents

- [Common Issues](#common-issues)
- [OAuth & Authentication](#oauth--authentication)
- [API Errors](#api-errors)
- [Webhooks](#webhooks)
- [Billing Issues](#billing-issues)
- [Session & Cookies](#session--cookies)
- [Queue & Jobs](#queue--jobs)
- [Docker Testing](#docker-testing)

## Common Issues

### Installation Problems

**Composer installation fails:**
```
Your requirements could not be resolved to an installable set of packages.
```

**Solution:**
- Check PHP version (requires 8.1+)
- Clear Composer cache: `composer clear-cache`
- Update dependencies: `composer update --with-dependencies`

**Migration fails:**
```
SQLSTATE[42S01]: Base table or view already exists
```

**Solution:**
```bash
php artisan migrate:reset
php artisan migrate
```

Or if in production:
```bash
php artisan migrate --force
```

## OAuth & Authentication

### "The redirect_uri is not whitelisted"

**Cause:** App URL in Partner Dashboard doesn't match your actual URL.

**Solution:**
1. Go to Shopify Partner Dashboard → Apps → [Your App] → Configuration
2. Update "App URL" to match your ngrok/actual URL
3. Add these redirect URLs:
   - `https://your-domain.com/authenticate`
   - `https://your-domain.com/billing/process`

**Important:** Both must use HTTPS.

### "HMAC verification failed"

**Cause:** Invalid signature calculation.

**Solution:**
- Verify `SHOPIFY_API_SECRET` matches Partner Dashboard
- Check shop domain format (should end with `.myshopify.com`)
- Ensure no extra query parameters in the URL

### Redirect Loop

**Cause:** Session or token issues.

**Solution:**
1. Clear browser cookies for your domain
2. Check session configuration in `config/session.php`:
```php
'secure' => true,
'same_site' => 'none',
```
3. Verify `APP_KEY` is set: `php artisan key:generate`

### "This app can't be installed on this store"

**Cause:** Missing permissions or store type.

**Solution:**
- Ensure using a development store for testing
- Check API scopes in `.env` are valid
- Verify app is not marked as "Custom app" when it should be "Public app"

## API Errors

### "API Key is invalid"

**Cause:** Incorrect API credentials.

**Solution:**
```env
SHOPIFY_API_KEY=your_client_id_from_partner_dashboard
SHOPIFY_API_SECRET=your_client_secret_from_partner_dashboard
```

### Rate Limiting (429 Too Many Requests)

**Cause:** Exceeding Shopify API rate limits.

**Solution:**
The package includes built-in rate limiting via BasicShopifyAPI. To handle manually:

```php
use Illuminate\Support\Facades\Cache;

// Check rate limit before request
$callsMade = Cache::get('shopify_calls_' . $shop->name, 0);
if ($callsMade > 35) { // Leave buffer
    sleep(1);
}
```

### GraphQL Errors

**Common GraphQL errors:**

```json
{
  "errors": [
    {
      "message": "Field 'xyz' doesn't exist on type 'Query'"
    }
  ]
}
```

**Solution:**
- Check API version in config matches your query
- Verify field names (case-sensitive)
- Use Shopify GraphQL Explorer to test queries

## Webhooks

### Webhooks Not Received

**Checklist:**
1. URL uses HTTPS
2. Webhook is registered
3. HMAC verification is working
4. Queue is running

**Debug:**
```bash
# Check registered webhooks
php artisan tinker
>>> $shop = \App\Models\User::first();
>>> app(\Osiset\ShopifyApp\Services\ApiHelper::class)->getWebhooks();

# View ngrok logs
ngrok http 8000 --log=stdout
```

### "Invalid HMAC"

**Cause:** Webhook payload was modified or secret is wrong.

**Solution:**
- Verify `SHOPIFY_API_SECRET` in `.env`
- Ensure raw body is used for HMAC (not decoded JSON)
- Check `Content-Type` header is preserved

### Job Not Fired

**Cause:** Job class not found or misnamed.

**Solution:**
- Job class must follow naming: `{Topic}Job` (e.g., `OrdersCreateJob`)
- Must extend `Osiset\ShopifyApp\Messaging\Jobs\WebhookJob`
- Must be in `App\Jobs` namespace

## Billing Issues

### "Charge not found"

**Cause:** Trying to activate a charge that doesn't exist.

**Solution:**
```php
// Verify charge exists before activating
$charge = $apiHelper->getCharge($chargeType, $chargeRef);
if ($charge) {
    $apiHelper->activateCharge($chargeType, $chargeRef);
}
```

### Test Charges Still Show Payment

**Cause:** Test mode not properly enabled.

**Solution:**
```php
// In config/shopify-app.php
'plans' => [
    [
        'name' => 'Test Plan',
        'price' => 9.99,
        'test' => true, // Must be true
    ],
],
```

### "Billing confirmation loop"

**Cause:** Charge not being recorded after activation.

**Solution:**
Ensure your billing process route properly handles the confirmation:

```php
Route::get('/billing/process', function () {
    // Get charge reference from query
    $chargeId = request('charge_id');

    // Verify and activate
    $apiHelper->activateCharge($chargeType, $chargeRef);

    // Redirect to home
    return redirect()->route('home');
});
```

## Session & Cookies

### "419 Page Expired" (CSRF)

**Cause:** CSRF token mismatch.

**Solution:**
Disable CSRF for Shopify routes (required):

```php
// Laravel 11: bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        '*',
    ]);
})

// Laravel 10: app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    '*',
];
```

### Cookies Not Set in iframe

**Cause:** SameSite cookie policy.

**Solution:**
```php
// config/session.php
'secure' => true,
'same_site' => 'none',
```

Also ensure HTTPS is used.

### "Unrecognized shop domain"

**Cause:** Malformed shop domain in session or request.

**Solution:**
- Always validate shop domain format: `*.myshopify.com`
- Check for null or empty values
- Sanitize user input

## Queue & Jobs

### Jobs Not Processing

**Check queue connection:**
```env
QUEUE_CONNECTION=database # or redis, sync for testing
```

**Start queue worker:**
```bash
php artisan queue:work
```

### Failed Jobs

**View failed jobs:**
```bash
php artisan queue:failed
```

**Retry failed jobs:**
```bash
php artisan queue:retry all
```

**Clear failed jobs:**
```bash
php artisan queue:flush
```

### Timeout Errors

**Cause:** Job running longer than allowed.

**Solution:**
```php
// In job class
public $timeout = 300; // 5 minutes

// Or in supervisor config
--timeout=300
```

## Docker Testing

### "Service not found"

**Cause:** Docker service name mismatch.

**Solution:**
```bash
cd docker-testing
docker-compose ps  # List available services
docker-compose run --rm php82-laravel11
```

### Permission Denied

**Cause:** File ownership issues in container.

**Solution:**
```bash
# Run with current user
docker-compose run --rm -u $(id -u):$(id -g) php82-laravel11
```

### Composer Install Fails in Container

**Cause:** Network or memory issues.

**Solution:**
```bash
# Increase memory limit
docker-compose run --rm php82-laravel11 php -d memory_limit=2G /usr/bin/composer install

# Or clear composer cache
docker-compose run --rm php82-laravel11 composer clear-cache
```

## Getting Help

### Debug Information

When reporting issues, include:

1. PHP version: `php -v`
2. Laravel version: `php artisan --version`
3. Package version: `composer show kyon147/laravel-shopify`
4. Error messages with stack trace
5. Relevant configuration (sanitized)

### Resources

- [GitHub Issues](https://github.com/Kyon147/laravel-shopify/issues)
- [GitHub Discussions](https://github.com/Kyon147/laravel-shopify/discussions)
- [Shopify API Documentation](https://shopify.dev/docs/api)
- [Shopify Community Forums](https://community.shopify.com/)

### Enable Debug Logging

```env
APP_DEBUG=true
LOG_LEVEL=debug
LOG_CHANNEL=daily
```

Then check `storage/logs/laravel-*.log`

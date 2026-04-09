# Billing Guide

## Table of Contents

- [Overview](#overview)
- [Configuration](#configuration)
- [Billing Types](#billing-types)
- [Recurring Charges](#recurring-charges)
- [One-Time Charges](#one-time-charges)
- [Usage-Based Charges](#usage-based-charges)
- [App Credits](#app-credits)
- [GraphQL Billing (Recommended)](#graphql-billing-recommended)
- [Freemium Mode](#freemium-mode)
- [Middleware](#middleware)

## Overview

The package supports multiple billing models to monetize your Shopify app:

- **Recurring Charges**: Monthly/yearly subscriptions
- **One-Time Charges**: Single purchase charges
- **Usage-Based Charges**: Pay-per-use billing
- **App Credits**: Refund or promotional credits

## Configuration

Enable billing in `config/shopify-app.php`:

```php
'billing_enabled' => (bool) env('SHOPIFY_BILLING_ENABLED', true),

'plans' => [
    [
        'name' => 'Basic Plan',
        'price' => 9.99,
        'interval' => 'EVERY_30_DAYS', // or 'ANNUAL'
        'trial_days' => 14,
        'test' => false, // Set to true for development
        'on_install' => false,
        'capped_amount' => null,
        'terms' => null,
    ],
    [
        'name' => 'Pro Plan',
        'price' => 29.99,
        'interval' => 'EVERY_30_DAYS',
        'trial_days' => 14,
        'test' => false,
        'on_install' => true, // Charge immediately after install
    ],
],
```

Environment variables:

```env
SHOPIFY_BILLING_ENABLED=true
SHOPIFY_BILLING_REDIRECT=/billing/process
```

## Billing Types

### Charge Types

The package uses an enum for charge types:

```php
use Osiset\ShopifyApp\Objects\Enums\ChargeType;

ChargeType::RECURRING();  // Recurring subscription
ChargeType::CHARGE();       // One-time charge
ChargeType::USAGE();        // Usage-based charge
ChargeType::CREDIT();       // App credit
```

## Recurring Charges

### REST API (Legacy)

```php
use Osiset\ShopifyApp\Services\ApiHelper;
use Osiset\ShopifyApp\Objects\Enums\ChargeType;

$apiHelper = app(ApiHelper::class);

// Create recurring charge
$response = $apiHelper->createCharge(
    ChargeType::RECURRING(),
    new PlanDetailsTransfer(
        'Premium Plan',
        'https://your-domain.com/billing/process',
        9.99,
        14, // trial days
        false // test mode
    )
);

// Activate the charge
$apiHelper->activateCharge(
    ChargeType::RECURRING(),
    ChargeReference::fromNative($chargeId)
);
```

### GraphQL API (Recommended)

```php
// Create subscription using GraphQL
$response = $apiHelper->createChargeGraphQL(
    new PlanDetailsTransfer(
        'Premium Plan',
        'https://your-domain.com/billing/process',
        9.99,
        14,
        false,
        'EVERY_30_DAYS' // interval
    )
);

// Get confirmation URL
$confirmationUrl = $response['confirmationUrl'];
```

## One-Time Charges

### Creating a One-Time Charge

```php
use Osiset\ShopifyApp\Services\ApiHelper;
use Osiset\ShopifyApp\Objects\Enums\ChargeType;

$apiHelper = app(ApiHelper::class);

$response = $apiHelper->createCharge(
    ChargeType::CHARGE(),
    new PlanDetailsTransfer(
        'Feature Unlock',
        'https://your-domain.com/billing/process',
        49.99,
        0, // no trial for one-time
        false
    )
);
```

## Usage-Based Charges

### Creating Usage Charges

Usage charges are attached to a recurring charge:

```php
use Osiset\ShopifyApp\Services\ApiHelper;
use Osiset\ShopifyApp\Objects\Transfers\UsageChargeDetails;
use Osiset\ShopifyApp\Objects\Values\ChargeReference;

$apiHelper = app(ApiHelper::class);

// Create usage charge on existing recurring charge
$response = $apiHelper->createUsageCharge(
    new UsageChargeDetailsTransfer(
        ChargeReference::fromNative($recurringChargeId),
        5.00, // amount
        'Email sent via API' // description
    )
);
```

### Capped Amount Plans

For usage-based plans with a spending cap:

```php
'plans' => [
    [
        'name' => 'Usage-Based Plan',
        'price' => 0, // Base price can be 0
        'capped_amount' => 100.00,
        'terms' => '$0.10 per email sent',
        'interval' => 'EVERY_30_DAYS',
    ],
],
```

## App Credits

### Creating Credits

```php
use Osiset\ShopifyApp\Services\ApiHelper;
use Osiset\ShopifyApp\Objects\Enums\ChargeType;

$apiHelper = app(ApiHelper::class);

$response = $apiHelper->createCharge(
    ChargeType::CREDIT(),
    new PlanDetailsTransfer(
        'Promotional Credit',
        'https://your-domain.com/billing/process',
        10.00,
        0,
        false
    )
);
```

## GraphQL Billing (Recommended)

### App Subscription Create

The recommended approach for new apps:

```php
$apiHelper = app(ApiHelper::class);

$response = $apiHelper->createChargeGraphQL(
    new PlanDetailsTransfer(
        'Pro Subscription',
        'https://your-domain.com/billing/process',
        29.99,
        14,
        false,
        'EVERY_30_DAYS',
        [
            [
                'plan' => [
                    'appRecurringPricingDetails' => [
                        'price' => [
                            'amount' => 29.99,
                            'currencyCode' => 'USD',
                        ],
                        'interval' => 'EVERY_30_DAYS',
                    ],
                ],
            ],
        ]
    )
);

// Check for errors
if (!empty($response['userErrors'])) {
    // Handle errors
    foreach ($response['userErrors'] as $error) {
        Log::error("Billing error: {$error['message']}");
    }
}

// Redirect to confirmation
return redirect($response['confirmationUrl']);
```

### Handling Subscriptions

```graphql
# Query current subscription
query {
    currentAppInstallation {
        activeSubscriptions {
            id
            name
            createdAt
            currentPeriodEnd
            trialDays
        }
    }
}
```

## Freemium Mode

Enable freemium to allow limited free usage:

```php
// config/shopify-app.php
'freemium_enabled' => true,
'freemium_limit' => 100, // Allow 100 free actions
```

Check freemium status:

```php
use Osiset\ShopifyApp\Util;

if (Util::getShopifyConfig('freemium_enabled')) {
    $limit = Util::getShopifyConfig('freemium_limit');
    // Check user's usage against limit
}
```

## Middleware

### Billable Middleware

Require billing for specific routes:

```php
Route::get('/premium-feature', function () {
    return view('premium');
})->middleware(['verify.shopify', 'billable']);
```

The `billable` middleware:
1. Checks if shop has an active plan
2. Redirects to billing if no active plan found
3. Allows access if plan is active

### SPA Mode Note

**Important:** The `billable` middleware cannot be used in SPA mode. Handle billing manually:

```javascript
// Check billing status via API
const response = await fetch('/api/billing-status', {
    headers: { 'Authorization': `Bearer ${token}` },
});
const { hasActivePlan, confirmationUrl } = await response.json();

if (!hasActivePlan && confirmationUrl) {
    window.top.location.href = confirmationUrl;
}
```

## Webhook Topics for Billing

Subscribe to billing-related webhooks:

```php
// config/shopify-app.php
'webhooks' => [
    [
        'topic' => 'APP_SUBSCRIPTIONS_UPDATE',
        'address' => 'https://your-domain.com/webhooks/subscription-update'
    ],
    [
        'topic' => 'APP_PURCHASES_ONE_TIME_UPDATE',
        'address' => 'https://your-domain.com/webhooks/one-time-update'
    ],
],
```

## Testing Billing

### Test Mode

Always use test mode during development:

```php
'plans' => [
    [
        'name' => 'Test Plan',
        'price' => 9.99,
        'test' => true, // Charges won't actually be processed
    ],
],
```

### Testing with Shopify

1. Create a development store in Shopify Partner dashboard
2. Install your app on the development store
3. Test billing flow without real charges
4. Verify webhooks are received correctly

## Next Steps

- [Webhooks Guide](webhooks.md) - Handle billing webhooks
- [Installation Guide](installation.md) - Full setup instructions

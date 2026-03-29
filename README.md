# mercadopago-fake

A fake MercadoPago API server for testing Laravel applications. It replaces the real `puntodev/mercadopago` client with a mock implementation that simulates the full checkout flow without hitting the MercadoPago API.

## Installation

```bash
composer require --dev puntodev/mercadopago-fake
```

## Activation

Set the environment variable in your `.env` or `phpunit.xml`:

```
MERCADOPAGO_USE_FAKE=true
```

The service provider auto-registers and swaps the `MercadoPago` binding in the container with `MercadoPagoFake`.

## How It Works

The library has four main parts:

### MercadoPagoFake

Implements `MercadoPago` and manages global test state. Stores preferences, payments, and merchant orders in a file-based cache, records method calls for assertions, and provides a `reset()` method to clean up between tests.

### MercadoPagoFakeApi

Implements `MercadoPagoApi` with mock implementations:

- `createPaymentPreference(array $order)` — Generates a fake preference ID, stores it in cache, and returns the expected response structure including `init_point` and `sandbox_init_point`.
- `findMerchantOrders(array $query)` — Returns an empty list of merchant orders.
- `findMerchantOrderById(string $id)` — Retrieves a stored merchant order. Throws a `RequestException` if not found.
- `findPayments(array $query)` — Returns an empty list of payments.
- `findPaymentById(string $id)` — Retrieves a stored payment. Throws a `RequestException` if not found.

### FakeCheckoutController

Exposes HTTP routes that simulate MercadoPago's checkout UI:

| Method | Route                                               | Description                                                                   |
|--------|-----------------------------------------------------|-------------------------------------------------------------------------------|
| GET    | `/mercadopago-fake/checkout/{preferenceId}`         | Displays a styled checkout page with Pay, Decline, and Cancel buttons         |
| POST   | `/mercadopago-fake/checkout/{preferenceId}/approve` | Simulates approval and posts a webhook (IPN) to your app's `notification_url` |
| POST   | `/mercadopago-fake/checkout/{preferenceId}/decline` | Marks the payment as declined and redirects to the failure URL                |
| GET    | `/mercadopago-fake/checkout/{preferenceId}/cancel`  | Redirects to the failure back URL                                             |

### MercadoPagoFakeServiceProvider

When `MERCADOPAGO_USE_FAKE` is `true`, it binds `MercadoPagoFake` as a singleton in place of `MercadoPago`, registers the checkout routes, and loads the Blade views.

## Checkout Flow

```
Your test creates a preference via MercadoPagoFakeApi::createPaymentPreference()
        |
Preference is stored in file cache (300s TTL)
        |
Test or browser approves the payment
        |
Webhook (IPN) POSTed to the preference's notification_url
        |
Redirect to back_urls with collection_id, status, preference_id, etc.
        |
Assertions via MercadoPagoFake::getCalls()
```

## Usage

### Automated Tests

```php
use Puntodev\MercadoPagoFake\MercadoPagoFake;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\PaymentPreferenceBuilder;

// Get the fake client
$client = app(MercadoPago::class)->defaultClient();

// Create a payment preference
$order = (new PaymentPreferenceBuilder())
    ->item()
    ->title('Test Product')
    ->unitPrice(25.00)
    ->quantity(1)
    ->currency('ARS')
    ->make()
    ->payerFirstName('John')
    ->payerLastName('Doe')
    ->payerEmail('john@example.com')
    ->notificationUrl('https://example.com/mp/ipn/1')
    ->externalId('order-123')
    ->successBackUrl('https://example.com/success')
    ->make();

$created = $client->createPaymentPreference($order);

// Assert calls were made
$calls = MercadoPagoFake::getCalls('createPaymentPreference');
$this->assertCount(1, $calls);

// Clean up
MercadoPagoFake::reset();
```

### Manual Browser Testing

Start your Laravel app with `MERCADOPAGO_USE_FAKE=true` and navigate to the `init_point` URL returned by `createPaymentPreference()`. The checkout page lets you click Pay, Decline, or Cancel to test the full flow, including IPN webhooks hitting your application.

### Test Helpers

| Method                                               | Description                                       |
|------------------------------------------------------|---------------------------------------------------|
| `MercadoPagoFake::storePreference($id, $preference)` | Store a preference in the fake cache              |
| `MercadoPagoFake::getStoredPreference($id)`          | Retrieve a stored preference                      |
| `MercadoPagoFake::storePayment($id, $payment)`       | Store a payment in the fake cache                 |
| `MercadoPagoFake::getStoredPayment($id)`             | Retrieve a stored payment                         |
| `MercadoPagoFake::storeMerchantOrder($id, $order)`   | Store a merchant order in the fake cache          |
| `MercadoPagoFake::getStoredMerchantOrder($id)`       | Retrieve a stored merchant order                  |
| `MercadoPagoFake::markPaymentAsDeclined($id)`        | Mark a payment as declined                        |
| `MercadoPagoFake::isPaymentDeclined($id)`            | Check if a payment is declined                    |
| `MercadoPagoFake::recordCall($method, $args)`        | Record a method call                              |
| `MercadoPagoFake::getCalls($method)`                 | Get recorded calls, optionally filtered by method |
| `MercadoPagoFake::reset()`                           | Clear all stored state and recorded calls         |

## Requirements

- PHP >= 8.4
- Laravel 12+
- `puntodev/mercadopago` ^v6.0

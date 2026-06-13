# AGENTS.md

Guidance for AI agents working in this repository.

## What this project is

`puntodev/mercadopago-fake` is a **Laravel package** (Composer library) that provides a
**fake/in-memory implementation of the MercadoPago client** shipped by
`puntodev/mercadopago`. It lets Laravel apps exercise the full Checkout flow — payment
preferences, merchant orders, payments and IPN webhooks — **without hitting the real
MercadoPago API**. It is not an application: it is published to Packagist and pulled in
as a `--dev` dependency from Laravel apps and test suites.

- **Namespace:** `Puntodev\MercadoPagoFake\` (PSR-4, mapped to `src/`)
- **PHP:** `>=8.4 <9.0`
- **Main dependencies:** `illuminate/support` (`^13.0`, Laravel 13+) and
  `puntodev/mercadopago` (`^7.0`) — the package this one fakes.
- **License:** MIT

## Architecture

The package mirrors the two public interfaces of `puntodev/mercadopago`
(`MercadoPago` and `MercadoPagoApi`) with fake implementations, plus an HTTP controller
that renders a stand-in Checkout UI.

| File | Role |
|------|------|
| `src/MercadoPagoFake.php` | Implements `Puntodev\MercadoPago\MercadoPago` (the factory). `defaultClient()`/`withCredentials()` return a `MercadoPagoFakeApi`; `usingSandbox()` is always `true`. Also the global **test state store**: static `storePreference`/`storePayment`/`storeMerchantOrder` (+ getters) backed by the **file cache** (300s TTL), `markPaymentAsDeclined`/`isPaymentDeclined`, and a static `$calls` log driven by `recordCall`/`getCalls`/`reset`. |
| `src/MercadoPagoFakeApi.php` | Implements `Puntodev\MercadoPago\MercadoPagoApi`. `createPaymentPreference` mints a random 17-char preference id, builds a response with `init_point`/`sandbox_init_point` pointing at the fake checkout route, stores it and records the call. `findMerchantOrders`/`findPayments` return empty result sets; `findMerchantOrderById`/`findPaymentById` read from the store and throw a `RequestException` (404) when missing. |
| `src/FakeCheckoutController.php` | Renders and drives the fake Checkout UI: `show`, `approve`, `decline`, `cancel`. Approving/declining posts an IPN (notification) to the preference's `notification_url` and redirects to the appropriate `back_urls` with MercadoPago-style query params (`collection_id`, `status`, `preference_id`, …). |
| `src/MercadoPagoFakeServiceProvider.php` | When `mercadopago-fake.enabled` is true, binds `MercadoPago::class` as a singleton to `MercadoPagoFake` (aliased `mercadopago`), loads the Blade views and registers the checkout routes (under the `web` group, **without** `VerifyCsrfToken`). |
| `resources/views/checkout.blade.php` | The styled fake checkout page with Pay / Decline / Cancel actions. |
| `config/mercadopago-fake.php` | Single `enabled` flag, read from `MERCADOPAGO_USE_FAKE` (default `false`). |

### Important behavior details

- **Activation:** everything is gated by `config('mercadopago-fake.enabled')`, i.e. the
  `MERCADOPAGO_USE_FAKE` env var. When off, the package registers nothing and the real
  `puntodev/mercadopago` binding stays in place — so it is safe to keep installed.
- **State lives in the file cache, not memory.** Preferences/payments/merchant orders are
  persisted via `Cache::store('file')` with a 300s TTL so they survive the redirect →
  webhook → back-url round trip across separate HTTP requests. The `$calls` log is static
  (per-process) and is what tests assert against; call `MercadoPagoFake::reset()` between
  tests.
- **Routes bypass CSRF** (`withoutMiddleware(VerifyCsrfToken::class)`) because the fake
  checkout posts back from a plain HTML form.
- **Not-found lookups throw `RequestException`** with a 404 JSON body, matching how the
  real client surfaces MercadoPago errors.

## Laravel auto-registration (package discovery)

Defined in `composer.json` → `extra.laravel`:
- Provider: `Puntodev\MercadoPagoFake\MercadoPagoFakeServiceProvider`

There is no facade: the package overrides the `MercadoPago` container binding, so app code
keeps resolving `Puntodev\MercadoPago\MercadoPago` exactly as in production.

## How to run and test

```bash
composer install
composer test            # vendor/bin/phpunit
composer test-coverage   # generates an HTML coverage report under ./coverage
composer lint            # vendor/bin/pint --test (style check, no changes)
composer format          # vendor/bin/pint (fix style)
```

- Tests use **Orchestra Testbench** (`tests/TestCase.php` extends
  `Orchestra\Testbench\TestCase` and registers the service provider).
- `phpunit.xml.dist` forces `SANDBOX_GATEWAYS=true` and `MERCADOPAGO_USE_FAKE=true`.
- ✅ Unlike `puntodev/mercadopago`, these tests are **fully isolated** — they never reach
  the network, so no credentials are needed in CI.
- CI: `.github/workflows/php.yml` runs on PHP 8.4 on every push/PR to `main`, including a
  Pint code-style check.

## Conventions

- Code style is enforced by **Laravel Pint** (`pint.json`, `laravel` preset). Run
  `composer format` before committing; `composer lint` is what CI runs.
- The fakes must stay drop-in compatible with the interfaces in `puntodev/mercadopago`
  (`MercadoPago`, `MercadoPagoApi`): when that package adds an interface method, mirror it
  here and cover it with a test.
- API methods return `array`/`?array` (matching the real client); keep that convention.

## Workflow rules (inherited from the user's global config)

- **Do not commit on `main`.** Always work on a branch or worktree.
- PRs are always opened as **Draft**.
- Run `git pull` before starting to make sure you have the latest version.

<?php

namespace Puntodev\MercadoPagoFake;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Puntodev\MercadoPago\MercadoPago;

class MercadoPagoFakeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mercadopago-fake.php', 'mercadopago-fake');

        if (config('mercadopago-fake.enabled')) {
            $this->app->singleton(MercadoPago::class, function () {
                return new MercadoPagoFake();
            });
            $this->app->alias(MercadoPago::class, 'mercadopago');
        }
    }

    public function boot(): void
    {
        if (config('mercadopago-fake.enabled')) {
            $this->loadViewsFrom(__DIR__ . '/../resources/views', 'mercadopago-fake');

            Route::middleware('web')
                ->withoutMiddleware(VerifyCsrfToken::class)
                ->group(function () {
                    Route::get('/mercadopago-fake/checkout/{preferenceId}', [FakeCheckoutController::class, 'show']);
                    Route::post('/mercadopago-fake/checkout/{preferenceId}/approve', [FakeCheckoutController::class, 'approve']);
                    Route::post('/mercadopago-fake/checkout/{preferenceId}/decline', [FakeCheckoutController::class, 'decline']);
                    Route::get('/mercadopago-fake/checkout/{preferenceId}/cancel', [FakeCheckoutController::class, 'cancel']);
                });
        }
    }
}

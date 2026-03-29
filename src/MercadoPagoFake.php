<?php

namespace Puntodev\MercadoPagoFake;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\MercadoPagoApi;

class MercadoPagoFake implements MercadoPago
{
    private static array $calls = [];

    public function defaultClient(): MercadoPagoApi
    {
        return new MercadoPagoFakeApi();
    }

    public function withCredentials(string $clientId, string $clientSecret): MercadoPagoApi
    {
        return new MercadoPagoFakeApi();
    }

    public function usingSandbox(): bool
    {
        return true;
    }

    public static function storePreference(string $id, array $preference): void
    {
        static::cache()->put("fake-mp-preference-$id", $preference, 300);
    }

    public static function getStoredPreference(string $id): ?array
    {
        return static::cache()->get("fake-mp-preference-$id");
    }

    public static function storePayment(string $id, array $payment): void
    {
        static::cache()->put("fake-mp-payment-$id", $payment, 300);
    }

    public static function getStoredPayment(string $id): ?array
    {
        return static::cache()->get("fake-mp-payment-$id");
    }

    public static function storeMerchantOrder(string $id, array $order): void
    {
        static::cache()->put("fake-mp-merchant-order-$id", $order, 300);
    }

    public static function getStoredMerchantOrder(string $id): ?array
    {
        return static::cache()->get("fake-mp-merchant-order-$id");
    }

    public static function markPaymentAsDeclined(string $id): void
    {
        static::cache()->put("fake-mp-declined-$id", true, 300);
    }

    public static function isPaymentDeclined(string $id): bool
    {
        return static::cache()->get("fake-mp-declined-$id", false);
    }

    public static function recordCall(string $method, array $args = []): void
    {
        static::$calls[] = ['method' => $method, 'args' => $args];
    }

    public static function getCalls(?string $method = null): array
    {
        if ($method === null) {
            return static::$calls;
        }

        return array_values(array_filter(static::$calls, fn(array $call) => $call['method'] === $method));
    }

    public static function reset(): void
    {
        static::$calls = [];
    }

    private static function cache(): Repository
    {
        return Cache::store('file');
    }
}

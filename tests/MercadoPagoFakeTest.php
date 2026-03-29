<?php

namespace Tests;

use PHPUnit\Framework\Attributes\Test;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPagoFake\MercadoPagoFake;
use Puntodev\MercadoPagoFake\MercadoPagoFakeApi;

class MercadoPagoFakeTest extends TestCase
{
    #[Test]
    public function resolves_fake_from_container(): void
    {
        $mp = app(MercadoPago::class);

        $this->assertInstanceOf(MercadoPagoFake::class, $mp);
    }

    #[Test]
    public function default_client_returns_fake_api(): void
    {
        $mp = app(MercadoPago::class);
        $client = $mp->defaultClient();

        $this->assertInstanceOf(MercadoPagoFakeApi::class, $client);
    }

    #[Test]
    public function with_credentials_returns_fake_api(): void
    {
        $mp = app(MercadoPago::class);
        $client = $mp->withCredentials('any-id', 'any-secret');

        $this->assertInstanceOf(MercadoPagoFakeApi::class, $client);
    }

    #[Test]
    public function reset_clears_state(): void
    {
        $client = app(MercadoPago::class)->defaultClient();
        $client->createPaymentPreference([
            'items' => [['title' => 'Test', 'unit_price' => 10, 'quantity' => 1]],
        ]);

        $this->assertNotEmpty(MercadoPagoFake::getCalls());

        MercadoPagoFake::reset();

        $this->assertEmpty(MercadoPagoFake::getCalls());
    }
}

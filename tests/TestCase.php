<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Puntodev\MercadoPago\MercadoPagoServiceProvider;
use Puntodev\MercadoPagoFake\MercadoPagoFake;
use Puntodev\MercadoPagoFake\MercadoPagoFakeServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        MercadoPagoFake::reset();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('mercadopago.client_id', 'fake-client-id');
        $app['config']->set('mercadopago.client_secret', 'fake-client-secret');
        $app['config']->set('mercadopago.use_sandbox', true);
    }

    protected function getPackageProviders($app): array
    {
        return [
            MercadoPagoServiceProvider::class,
            MercadoPagoFakeServiceProvider::class,
        ];
    }
}

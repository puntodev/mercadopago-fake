<?php

namespace Tests;

use Illuminate\Http\Client\RequestException;
use PHPUnit\Framework\Attributes\Test;
use Puntodev\MercadoPago\MercadoPago;
use Puntodev\MercadoPago\PaymentPreferenceBuilder;
use Puntodev\MercadoPagoFake\MercadoPagoFake;

class MercadoPagoFakeApiTest extends TestCase
{
    private function createTestPreference(): array
    {
        $client = app(MercadoPago::class)->defaultClient();

        $order = (new PaymentPreferenceBuilder())
            ->item()
            ->title('Test Product')
            ->unitPrice(23.20)
            ->quantity(1)
            ->currency('ARS')
            ->make()
            ->payerFirstName('Test')
            ->payerLastName('Buyer')
            ->payerEmail('buyer@example.com')
            ->notificationUrl('https://example.com/mp/ipn/1')
            ->externalId('test-123')
            ->successBackUrl('https://example.com/return')
            ->binaryMode(true)
            ->make();

        return $client->createPaymentPreference($order);
    }

    #[Test]
    public function create_payment_preference(): void
    {
        $result = $this->createTestPreference();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('init_point', $result);
        $this->assertArrayHasKey('sandbox_init_point', $result);
        $this->assertStringContainsString($result['id'], $result['init_point']);
        $this->assertFalse($result['expires']);
        $this->assertNull($result['expiration_date_from']);
        $this->assertNull($result['expiration_date_to']);
        $this->assertTrue($result['binary_mode']);
        $this->assertEquals('test-123', $result['external_reference']);

        $calls = MercadoPagoFake::getCalls('createPaymentPreference');
        $this->assertCount(1, $calls);
    }

    #[Test]
    public function stored_preference_is_retrievable(): void
    {
        $created = $this->createTestPreference();

        $stored = MercadoPagoFake::getStoredPreference($created['id']);

        $this->assertNotNull($stored);
        $this->assertEquals($created['id'], $stored['id']);
    }

    #[Test]
    public function find_merchant_orders(): void
    {
        $client = app(MercadoPago::class)->defaultClient();

        $result = $client->findMerchantOrders();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('elements', $result);
        $this->assertArrayHasKey('total', $result);

        $calls = MercadoPagoFake::getCalls('findMerchantOrders');
        $this->assertCount(1, $calls);
    }

    #[Test]
    public function find_merchant_order_by_id_throws_for_unknown(): void
    {
        $client = app(MercadoPago::class)->defaultClient();

        $this->expectException(RequestException::class);
        $client->findMerchantOrderById('unknown-id');
    }

    #[Test]
    public function find_payments(): void
    {
        $client = app(MercadoPago::class)->defaultClient();

        $result = $client->findPayments();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('paging', $result);

        $calls = MercadoPagoFake::getCalls('findPayments');
        $this->assertCount(1, $calls);
    }

    #[Test]
    public function find_payment_by_id_throws_for_unknown(): void
    {
        $client = app(MercadoPago::class)->defaultClient();

        $this->expectException(RequestException::class);
        $client->findPaymentById('unknown-id');
    }
}

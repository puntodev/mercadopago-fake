<?php

namespace Puntodev\MercadoPagoFake;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Str;
use Puntodev\MercadoPago\MercadoPagoApi;

class MercadoPagoFakeApi implements MercadoPagoApi
{
    public function createPaymentPreference(array $order): array
    {
        $preferenceId = Str::random(17);

        $checkoutUrl = url("/mercadopago-fake/checkout/$preferenceId");

        $response = [
            'id' => $preferenceId,
            'items' => $order['items'] ?? [],
            'payer' => $order['payer'] ?? [],
            'back_urls' => $order['back_urls'] ?? [],
            'auto_return' => $order['auto_return'] ?? 'all',
            'notification_url' => $order['notification_url'] ?? '',
            'external_reference' => $order['external_reference'] ?? '',
            'binary_mode' => $order['binary_mode'] ?? false,
            'expires' => $order['expires'] ?? false,
            'expiration_date_from' => $order['expiration_date_from'] ?? null,
            'expiration_date_to' => $order['expiration_date_to'] ?? null,
            'date_created' => now()->toIso8601String(),
            'init_point' => $checkoutUrl,
            'sandbox_init_point' => $checkoutUrl,
        ];

        MercadoPagoFake::storePreference($preferenceId, $response);
        MercadoPagoFake::recordCall('createPaymentPreference', ['order' => $order, 'response' => $response]);

        return $response;
    }

    public function findMerchantOrders(array $query = []): ?array
    {
        MercadoPagoFake::recordCall('findMerchantOrders', ['query' => $query]);

        return [
            'elements' => [],
            'total' => 0,
        ];
    }

    /**
     * @throws RequestException
     */
    public function findMerchantOrderById(string $id): ?array
    {
        MercadoPagoFake::recordCall('findMerchantOrderById', ['id' => $id]);

        $order = MercadoPagoFake::getStoredMerchantOrder($id);

        if ($order === null) {
            $this->throwNotFoundException('merchant_order', $id);
        }

        return $order;
    }

    public function findPayments(array $query = []): ?array
    {
        MercadoPagoFake::recordCall('findPayments', ['query' => $query]);

        return [
            'results' => [],
            'paging' => [
                'total' => 0,
                'offset' => 0,
                'limit' => 30,
            ],
        ];
    }

    /**
     * @throws RequestException
     */
    public function findPaymentById(string $id): ?array
    {
        MercadoPagoFake::recordCall('findPaymentById', ['id' => $id]);

        $payment = MercadoPagoFake::getStoredPayment($id);

        if ($payment === null) {
            $this->throwNotFoundException('payment', $id);
        }

        return $payment;
    }

    /**
     * @throws RequestException
     */
    private function throwNotFoundException(string $resource, string $id): never
    {
        $body = json_encode([
            'message' => "$resource not found",
            'error' => 'not_found',
            'status' => 404,
            'cause' => [],
        ]);

        $psrResponse = new Response(404, ['Content-Type' => 'application/json'], $body);
        $clientResponse = new ClientResponse($psrResponse);

        throw new RequestException($clientResponse);
    }
}

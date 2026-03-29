<?php

namespace Puntodev\MercadoPagoFake;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;

class FakeCheckoutController extends Controller
{
    public function show(string $preferenceId): View
    {
        $preference = MercadoPagoFake::getStoredPreference($preferenceId);

        $item = $preference['items'][0] ?? [];

        return view('mercadopago-fake::checkout', [
            'preferenceId' => $preferenceId,
            'description' => $item['title'] ?? 'MercadoPago Order',
            'amount' => $item['unit_price'] ?? 0,
            'currency' => $item['currency'] ?? 'ARS',
            'payer' => $preference['payer'] ?? [],
        ]);
    }

    public function approve(string $preferenceId): RedirectResponse
    {
        return $this->processCheckout($preferenceId, 'approved');
    }

    public function decline(string $preferenceId): RedirectResponse
    {
        MercadoPagoFake::markPaymentAsDeclined($preferenceId);

        return $this->processCheckout($preferenceId, 'rejected');
    }

    public function cancel(string $preferenceId): RedirectResponse
    {
        $preference = MercadoPagoFake::getStoredPreference($preferenceId);

        $failureUrl = $preference['back_urls']['failure'] ?? $preference['back_urls']['success'] ?? '/';

        $redirectUrl = Uri::of($failureUrl)
            ->withQuery([
                'collection_status' => 'null',
                'preference_id' => $preferenceId,
                'external_reference' => $preference['external_reference'] ?? '',
            ]);

        return redirect($redirectUrl);
    }

    private function processCheckout(string $preferenceId, string $status): RedirectResponse
    {
        $preference = MercadoPagoFake::getStoredPreference($preferenceId);

        $notificationUrl = $preference['notification_url'] ?? null;

        $paymentId = (string) random_int(1000000000, 9999999999);
        $merchantOrderId = (string) random_int(1000000000, 9999999999);
        $externalReference = $preference['external_reference'] ?? '';
        $item = $preference['items'][0] ?? [];

        $merchantOrder = [
            'id' => $merchantOrderId,
            'status' => 'closed',
            'order_status' => $status,
            'external_reference' => $externalReference,
            'preference_id' => $preferenceId,
            'total_amount' => $item['unit_price'] ?? 0,
            'paid_amount' => $status === 'approved' ? ($item['unit_price'] ?? 0) : 0,
            'refunded_amount' => 0,
            'items' => $preference['items'] ?? [],
            'payer' => $preference['payer'] ?? [],
            'payments' => [
                [
                    'id' => $paymentId,
                    'transaction_amount' => $item['unit_price'] ?? 0,
                    'total_paid_amount' => $status === 'approved' ? ($item['unit_price'] ?? 0) : 0,
                    'status' => $status,
                    'status_detail' => $status === 'approved' ? 'accredited' : 'cc_rejected_other_reason',
                    'date_approved' => $status === 'approved' ? now()->toIso8601String() : null,
                    'date_created' => now()->toIso8601String(),
                ],
            ],
            'date_created' => now()->toIso8601String(),
        ];

        MercadoPagoFake::storeMerchantOrder($merchantOrderId, $merchantOrder);

        if ($notificationUrl) {
            $ipnUrl = Uri::of($notificationUrl)
                ->withQuery([
                    'topic' => 'merchant_order',
                    'id' => $merchantOrderId,
                ]);

            Http::post((string) $ipnUrl);
        }

        $backUrl = $status === 'approved'
            ? ($preference['back_urls']['success'] ?? '/')
            : ($preference['back_urls']['failure'] ?? '/');

        $redirectUrl = Uri::of($backUrl)
            ->withQuery([
                'collection_id' => $paymentId,
                'collection_status' => $status,
                'payment_id' => $paymentId,
                'status' => $status,
                'preference_id' => $preferenceId,
                'external_reference' => $externalReference,
                'payment_type' => 'credit_card',
                'merchant_order_id' => $merchantOrderId,
            ]);

        return redirect($redirectUrl);
    }
}

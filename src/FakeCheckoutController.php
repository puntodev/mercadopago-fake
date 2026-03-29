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

        if ($notificationUrl) {
            Http::post($notificationUrl, [
                'action' => 'payment.created',
                'api_version' => 'v1',
                'data' => [
                    'id' => $paymentId,
                ],
                'date_created' => now()->toIso8601String(),
                'id' => random_int(10000000000, 99999999999),
                'live_mode' => false,
                'type' => 'payment',
                'user_id' => 'FAKE_USER_ID',
            ]);
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
                'external_reference' => $preference['external_reference'] ?? '',
                'payment_type' => 'credit_card',
                'merchant_order_id' => (string) random_int(1000000000, 9999999999),
            ]);

        return redirect($redirectUrl);
    }
}

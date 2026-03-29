<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MercadoPago Checkout</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Proxima Nova", -apple-system, Helvetica Neue, Helvetica, Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            width: 100%;
            background-color: #fff;
            padding: 14px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e6e6e6;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-logo svg {
            height: 34px;
            width: auto;
        }

        .header-user {
            font-size: 14px;
            color: #333;
        }

        .main {
            flex: 1;
            display: flex;
            justify-content: center;
            padding: 32px 24px 64px;
            gap: 24px;
            max-width: 960px;
            margin: 0 auto;
            width: 100%;
        }

        .left-panel {
            flex: 1;
            max-width: 560px;
        }

        .right-panel {
            width: 300px;
            flex-shrink: 0;
        }

        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 24px;
        }

        .payment-card {
            background: #fff;
            border-radius: 8px;
            border: 2px solid #009ee3;
            padding: 20px;
            margin-bottom: 12px;
        }

        .payment-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .radio-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #009ee3;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .radio-dot::after {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #009ee3;
        }

        .card-icon {
            width: 40px;
            height: 26px;
            background: #1a1f71;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }

        .card-details {
            flex: 1;
        }

        .card-name {
            font-size: 15px;
            font-weight: 500;
            color: #333;
        }

        .card-type {
            font-size: 13px;
            color: #999;
        }

        .badge-recommended {
            font-size: 11px;
            font-weight: 600;
            color: #009ee3;
            background-color: #e8f4fd;
            padding: 3px 10px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .other-methods {
            display: block;
            margin-top: 16px;
            font-size: 15px;
            color: #009ee3;
            text-decoration: none;
            font-weight: 500;
            cursor: default;
        }

        .summary-card {
            background: #f7f7f7;
            border-radius: 8px;
            padding: 24px;
        }

        .summary-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 16px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            gap: 16px;
        }

        .summary-item .item-description {
            font-size: 14px;
            color: #666;
            flex: 1;
        }

        .summary-item .item-amount {
            font-size: 14px;
            color: #333;
            font-weight: 500;
            white-space: nowrap;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid #e0e0e0;
            margin-bottom: 24px;
        }

        .summary-total .total-label {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .summary-total .total-amount {
            font-size: 20px;
            font-weight: 700;
            color: #333;
        }

        .btn-pay {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            background-color: #009ee3;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-pay:hover {
            background-color: #007eb5;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 12px;
            font-size: 13px;
            color: #999;
        }

        .btn-decline {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #f23d4f;
            background-color: #fff;
            border: 1px solid #f23d4f;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-decline:hover {
            background-color: #fef0f1;
        }

        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 12px;
            font-size: 14px;
            color: #009ee3;
            text-decoration: none;
            font-weight: 500;
        }

        .cancel-link:hover {
            text-decoration: underline;
        }

        @if(!empty($payer['name']) || !empty($payer['email']))
        .payer-section {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e0e0e0;
            font-size: 13px;
            color: #999;
        }
        @endif

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #999;
        }

        .footer a {
            color: #009ee3;
            text-decoration: none;
        }

        .fake-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #fef3cd;
            border-top: 1px solid #ffc107;
            padding: 8px;
            text-align: center;
            font-size: 12px;
            color: #856404;
            z-index: 10;
        }

        @media (max-width: 720px) {
            .main {
                flex-direction: column;
                padding: 20px 16px 64px;
            }

            .right-panel {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">
            <svg viewBox="0 0 195 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="24" fill="#009ee3"/>
                <path d="M24 10c-7.7 0-14 6.3-14 14s6.3 14 14 14 14-6.3 14-14-6.3-14-14-14zm6.8 11.4c0 .3-.1.6-.2.9l-2.4 6.9c-.2.5-.7.8-1.3.8h-7.7c-.6 0-1-.4-1-1v-7.6c0-.6.4-1 1-1h3.5l1.2-3.7c.3-.8 1.2-1.2 2-.9.8.3 1.2 1.2.9 2l-.6 1.8h3c.6 0 1.1.3 1.4.7.1.4.2.7.2 1.1z" fill="#fff"/>
                <text x="56" y="32" font-family="Proxima Nova, Arial, sans-serif" font-size="22" font-weight="600" fill="#333">mercado</text>
                <text x="56" y="32" font-family="Proxima Nova, Arial, sans-serif" font-size="22" font-weight="300" fill="#009ee3" dx="85">pago</text>
            </svg>
        </div>
        <div class="header-user">{{ $payer['name'] ?? '' }} {{ $payer['surname'] ?? '' }}</div>
    </div>

    <div class="main">
        <div class="left-panel">
            <div class="section-title">¿Cómo querés pagar?</div>

            <div class="payment-card">
                <div class="payment-card-header">
                    <div class="radio-dot"></div>
                    <div class="card-icon">VISA</div>
                    <div class="card-details">
                        <div class="card-name">Tarjeta de crédito **** 3197</div>
                        <div class="card-type">Visa Crédito</div>
                    </div>
                    <div class="badge-recommended">Recomendado</div>
                </div>
            </div>

            <span class="other-methods">Elegir otro medio de pago &rsaquo;</span>
        </div>

        <div class="right-panel">
            <div class="summary-card">
                <div class="summary-title">Detalles del pago</div>

                @if($description)
                    <div class="summary-item">
                        <span class="item-description">{{ $description }}</span>
                        <span class="item-amount">${{ number_format((float)$amount, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="summary-total">
                    <span class="total-label">Pagás</span>
                    <span class="total-amount">${{ number_format((float)$amount, 0, ',', '.') }}</span>
                </div>

                <form method="POST" action="{{ url("/mercadopago-fake/checkout/$preferenceId/approve") }}">
                    <button type="submit" class="btn-pay" dusk="fake-pay">Pagar</button>
                </form>

                <div class="secure-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Pago seguro
                </div>

                <form method="POST" action="{{ url("/mercadopago-fake/checkout/$preferenceId/decline") }}">
                    <button type="submit" class="btn-decline" dusk="fake-decline">Rechazar pago</button>
                </form>

                <a href="{{ url("/mercadopago-fake/checkout/$preferenceId/cancel") }}" class="cancel-link" dusk="fake-cancel">
                    Cancelar y volver
                </a>

                @if(!empty($payer['email']))
                    <div class="payer-section">
                        {{ $payer['email'] }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="footer">
        Procesado por Mercado Pago
    </div>

    <div class="fake-banner">Fake Checkout (testing)</div>
</body>
</html>

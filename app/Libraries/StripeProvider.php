<?php

declare(strict_types=1);

namespace App\Libraries;

use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeProvider implements PaymentProviderInterface
{
    private StripeClient $stripe;
    private string $webhookSecret;

    public function __construct()
    {
        if (!class_exists(StripeClient::class)) {
            throw new \RuntimeException('Stripe SDK no está instalado. Ejecuta: composer install (o composer require stripe/stripe-php).');
        }

        $this->stripe = new StripeClient((string) env('STRIPE_SECRET_KEY', ''));
        $this->webhookSecret = (string) env('STRIPE_WEBHOOK_SECRET', '');
    }

    public function createCheckoutSession(array $data): array
    {
        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower((string) ($data['currency'] ?? 'mxn')),
                    'product_data' => [
                        'name' => 'Activación Evento 13Bodas',
                        'description' => 'Evento: ' . ((string) ($data['event_title'] ?? 'Sin título')),
                    ],
                    'unit_amount' => (int) round(((float) $data['amount']) * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => (string) env('STRIPE_SUCCESS_URL', base_url('checkout/success')) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => (string) env('STRIPE_CANCEL_URL', base_url('checkout/cancel')),
            'customer_email' => $data['customer_email'] ?? null,
            'metadata' => [
                'event_id' => (string) $data['event_id'],
                'source' => '13bodas_checkout',
            ],
            'locale' => 'es',
            'billing_address_collection' => 'required',
        ]);

        return [
            'session_id' => (string) $session->id,
            'checkout_url' => (string) $session->url,
        ];
    }

    public function verifyWebhook(string $payload, string $signature): bool
    {
        try {
            Webhook::constructEvent($payload, $signature, $this->webhookSecret);
            return true;
        } catch (SignatureVerificationException|\UnexpectedValueException $exception) {
            log_message('error', 'Stripe webhook signature verification failed: {message}', ['message' => $exception->getMessage()]);
            return false;
        }
    }

    public function processPayment(string $payload): array
    {
        $eventData = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $eventType = (string) ($eventData['type'] ?? '');

        if ($eventType !== 'checkout.session.completed') {
            throw new \RuntimeException('Unsupported event type: ' . $eventType);
        }

        $sessionData = $eventData['data']['object'] ?? [];

        return [
            'event_id' => $sessionData['metadata']['event_id'] ?? null,
            'payment_reference' => (string) ($sessionData['payment_intent'] ?? ($sessionData['id'] ?? '')),
            'amount' => ((int) ($sessionData['amount_total'] ?? 0)) / 100,
            'currency' => strtoupper((string) ($sessionData['currency'] ?? 'MXN')),
            'status' => 'completed',
            'paid_at' => date('Y-m-d H:i:s', (int) ($sessionData['created'] ?? time())),
            'customer_email' => $sessionData['customer_details']['email'] ?? null,
            'customer_name' => $sessionData['customer_details']['name'] ?? null,
            'payment_method' => 'card',
        ];
    }
}

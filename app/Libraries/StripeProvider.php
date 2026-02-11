<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\URI;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeProvider implements PaymentProviderInterface
{
    private StripeClient $stripe;
    /** @var list<string> */
    private array $webhookSecrets;
    private string $successUrl;
    private string $cancelUrl;

    public function __construct()
    {
        if (!class_exists(StripeClient::class)) {
            throw new \RuntimeException('Stripe SDK no está instalado. Ejecuta: composer install (o composer require stripe/stripe-php).');
        }

        $secretKey = trim((string) env('STRIPE_SECRET_KEY', ''));
        if ($secretKey === '') {
            throw new \RuntimeException('Falta STRIPE_SECRET_KEY en tu .env para crear sesiones de Stripe.');
        }

        $this->stripe = new StripeClient($secretKey);
        $this->webhookSecrets = $this->parseWebhookSecrets((string) env('STRIPE_WEBHOOK_SECRET', ''));

        $this->successUrl = $this->resolveAbsoluteUrl(
            trim((string) env('STRIPE_SUCCESS_URL', route_to('checkout.success') !== '' ? site_url(route_to('checkout.success')) : base_url('checkout/success')))
        );
        $this->cancelUrl = $this->resolveAbsoluteUrl(
            trim((string) env('STRIPE_CANCEL_URL', route_to('checkout.cancel') !== '' ? site_url(route_to('checkout.cancel')) : base_url('checkout/cancel')))
        );
    }

    public function createCheckoutSession(array $data): array
    {
        $eventId = (string) ($data['event_id'] ?? '');

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
            'success_url' => $this->buildSuccessUrl($eventId),
            'cancel_url' => $this->buildCancelUrl($eventId),
            'customer_email' => $data['customer_email'] ?? null,
            'metadata' => [
                'event_id' => $eventId,
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
        if ($this->webhookSecrets === []) {
            throw new \RuntimeException('Falta STRIPE_WEBHOOK_SECRET en tu .env para validar webhooks de Stripe.');
        }

        foreach ($this->webhookSecrets as $webhookSecret) {
            try {
                // Intenta construir el evento. Si la firma es válida, esto pasará.
                Webhook::constructEvent($payload, $signature, $webhookSecret);

                return true;
            } catch (SignatureVerificationException | \UnexpectedValueException $e) {
                // CORRECCIÓN: Logueamos el mensaje exacto de la excepción para saber POR QUÉ falló
                // Ej: "No signatures found matching..." o "Timestamp outside tolerance..."
                log_message('error', 'Stripe Signature Error con secreto [...' . substr($webhookSecret, -4) . ']: ' . $e->getMessage());

                // Continuamos al siguiente secreto si hay más configurados
                continue;
            }
        }

        log_message('error', 'Stripe webhook signature verification failed for all configured secrets (count={count})', [
            'count' => count($this->webhookSecrets),
        ]);

        return false;
    }

    public function getCheckoutSessionStatus(string $sessionId): array
    {
        $session = $this->stripe->checkout->sessions->retrieve($sessionId, []);

        return [
            'session_id' => (string) ($session->id ?? ''),
            'payment_status' => (string) ($session->payment_status ?? ''),
            'status' => (string) ($session->status ?? ''),
            'event_id' => (string) ($session->metadata->event_id ?? ''),
            'payment_reference' => (string) ($session->payment_intent ?? ($session->id ?? '')),
            'amount' => ((int) ($session->amount_total ?? 0)) / 100,
            'currency' => strtoupper((string) ($session->currency ?? 'MXN')),
            'paid_at' => date('Y-m-d H:i:s', (int) ($session->created ?? time())),
            'customer_email' => $session->customer_details->email ?? null,
            'customer_name' => $session->customer_details->name ?? null,
            'payment_method' => 'card',
        ];
    }

    public function processPayment(string $payload): array
    {
        $eventData = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        $eventType = (string) ($eventData['type'] ?? '');

        if ($eventType !== 'checkout.session.completed') {
            return [
                'should_process' => false,
                'provider_event_id' => (string) ($eventData['id'] ?? ''),
            ];
        }

        $sessionData = $eventData['data']['object'] ?? [];

        return [
            'should_process' => true,
            'provider_event_id' => (string) ($eventData['id'] ?? ''),
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

    private function resolveAbsoluteUrl(string $url): string
    {
        if ($url === '') {
            throw new \RuntimeException('Configura STRIPE_SUCCESS_URL y STRIPE_CANCEL_URL con URLs válidas en tu .env.');
        }

        $uri = new URI($url);
        if ($uri->getScheme() === '' || $uri->getHost() === '') {
            throw new \RuntimeException('Las URLs de Stripe deben ser absolutas (https://dominio/ruta). Revisa tu .env.');
        }

        return $url;
    }

    private function buildSuccessUrl(string $eventId): string
    {
        $urlWithSessionId = str_contains($this->successUrl, '{CHECKOUT_SESSION_ID}')
            ? $this->successUrl
            : $this->appendQueryParam($this->successUrl, 'session_id={CHECKOUT_SESSION_ID}');

        if ($eventId === '' || str_contains($urlWithSessionId, 'event_id=')) {
            return $urlWithSessionId;
        }

        return $this->appendQueryParam($urlWithSessionId, 'event_id=' . urlencode($eventId));
    }

    private function buildCancelUrl(string $eventId): string
    {
        if ($eventId === '' || str_contains($this->cancelUrl, 'event_id=')) {
            return $this->cancelUrl;
        }

        return $this->appendQueryParam($this->cancelUrl, 'event_id=' . urlencode($eventId));
    }

    private function appendQueryParam(string $url, string $query): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . $query;
    }

    /**
     * Permite uno o más secrets separados por coma o salto de línea.
     * Útil para rotación y para entornos locales con Stripe CLI.
     *
     * @return list<string>
     */
    private function parseWebhookSecrets(string $rawWebhookSecrets): array
    {
        $parts = preg_split('/[\r\n,]+/', $rawWebhookSecrets) ?: [];

        $secrets = [];
        foreach ($parts as $part) {
            $candidate = trim($part);
            if ($candidate !== '') {
                $secrets[] = $candidate;
            }
        }

        return $secrets;
    }
}

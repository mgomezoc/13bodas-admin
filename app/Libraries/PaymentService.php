<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\EventModel;
use App\Models\EventPaymentModel;
use App\Models\PaymentSettingModel;

class PaymentService
{
    private PaymentProviderInterface $provider;
    private string $providerName;

    public function __construct(
        private readonly EventModel $eventModel = new EventModel(),
        private readonly EventPaymentModel $paymentModel = new EventPaymentModel(),
        private readonly PaymentSettingModel $settingModel = new PaymentSettingModel(),
        ?string $providerName = null,
    ) {
        $this->providerName = $providerName
            ?? (string) $this->settingModel->getValue('default_payment_provider', env('PAYMENT_DEFAULT_PROVIDER', 'stripe'));
        $this->provider = $this->loadProvider($this->providerName);
    }

    public function createCheckout(string $eventId): array
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            throw new \RuntimeException('Evento no encontrado.');
        }

        if ((int) $event['is_paid'] === 1) {
            throw new \RuntimeException('Este evento ya está activado.');
        }

        return $this->provider->createCheckoutSession([
            'event_id' => $eventId,
            'event_title' => (string) $event['couple_title'],
            'amount' => $this->settingModel->getEventPrice(),
            'currency' => (string) env('STRIPE_CURRENCY', 'MXN'),
            'customer_email' => $event['primary_contact_email'] ?: null,
        ]);
    }

    public function getCheckoutSessionStatus(string $sessionId): array
    {
        if (trim($sessionId) === '') {
            throw new \InvalidArgumentException('Session ID inválido.');
        }

        return $this->provider->getCheckoutSessionStatus($sessionId);
    }

    public function finalizeCheckoutSession(string $sessionId): array
    {
        $sessionData = $this->getCheckoutSessionStatus($sessionId);
        $paymentStatus = (string) ($sessionData['payment_status'] ?? 'unknown');
        $checkoutStatus = (string) ($sessionData['status'] ?? 'unknown');

        log_message('info', 'PaymentService::finalizeCheckoutSession session={sessionId} payment_status={paymentStatus} checkout_status={checkoutStatus}', [
            'sessionId' => $sessionId,
            'paymentStatus' => $paymentStatus,
            'checkoutStatus' => $checkoutStatus,
        ]);

        if ($paymentStatus !== 'paid' || $checkoutStatus !== 'complete') {
            return [
                'is_paid' => false,
                'event_id' => (string) ($sessionData['event_id'] ?? ''),
                'payment_status' => $paymentStatus,
                'already_processed' => false,
            ];
        }

        $eventId = (string) ($sessionData['event_id'] ?? '');
        if ($eventId === '') {
            throw new \RuntimeException('La sesión Stripe no incluye event_id en metadata.');
        }

        $reference = (string) ($sessionData['payment_reference'] ?? '');
        if ($reference === '') {
            throw new \RuntimeException('La sesión Stripe no incluye referencia de pago.');
        }

        $this->validateExpectedAmount(
            $eventId,
            (float) ($sessionData['amount'] ?? 0.0),
            (string) ($sessionData['currency'] ?? 'MXN')
        );

        $result = $this->persistSuccessfulPayment([
            'event_id' => $eventId,
            'payment_provider' => $this->currentProviderName(),
            'payment_reference' => $reference,
            'amount' => (float) ($sessionData['amount'] ?? 0.0),
            'currency' => strtoupper((string) ($sessionData['currency'] ?? 'MXN')),
            'status' => 'completed',
            'customer_email' => $sessionData['customer_email'] ?? null,
            'customer_name' => $sessionData['customer_name'] ?? null,
            'payment_method' => $sessionData['payment_method'] ?? 'card',
            'paid_at' => (string) ($sessionData['paid_at'] ?? date('Y-m-d H:i:s')),
            'provider_event_id' => $sessionId,
            'webhook_received_at' => date('Y-m-d H:i:s'),
            'webhook_payload' => json_encode([
                'source' => 'checkout_success_finalize',
                'session_id' => $sessionId,
            ], JSON_THROW_ON_ERROR),
        ], $sessionId);

        return [
            'is_paid' => true,
            'event_id' => $eventId,
            'payment_status' => $paymentStatus,
            'already_processed' => $result['already_processed'],
        ];
    }

    public function processWebhook(string $payload, string $signature, string $correlationId = ''): bool
    {
        if (!$this->provider->verifyWebhook($payload, $signature)) {
            return false;
        }

        $paymentData = $this->provider->processPayment($payload);
        if (($paymentData['should_process'] ?? true) !== true) {
            return true;
        }

        $eventId = (string) ($paymentData['event_id'] ?? '');
        $reference = (string) ($paymentData['payment_reference'] ?? '');

        if ($eventId === '' || $reference === '') {
            throw new \InvalidArgumentException('Webhook Stripe sin event_id/payment_reference.');
        }

        $this->validateExpectedAmount(
            $eventId,
            (float) ($paymentData['amount'] ?? 0.0),
            (string) ($paymentData['currency'] ?? 'MXN')
        );

        $this->persistSuccessfulPayment([
            'event_id' => $eventId,
            'payment_provider' => $this->currentProviderName(),
            'payment_reference' => $reference,
            'amount' => (float) ($paymentData['amount'] ?? 0.0),
            'currency' => strtoupper((string) ($paymentData['currency'] ?? 'MXN')),
            'status' => (string) ($paymentData['status'] ?? 'completed'),
            'customer_email' => $paymentData['customer_email'] ?? null,
            'customer_name' => $paymentData['customer_name'] ?? null,
            'payment_method' => $paymentData['payment_method'] ?? 'card',
            'paid_at' => (string) ($paymentData['paid_at'] ?? date('Y-m-d H:i:s')),
            'provider_event_id' => $paymentData['provider_event_id'] ?? null,
            'webhook_received_at' => date('Y-m-d H:i:s'),
            'webhook_payload' => $payload,
        ], $correlationId !== '' ? $correlationId : (string) ($paymentData['provider_event_id'] ?? $reference));

        return true;
    }

    public function markPaidManually(string $eventId, array $auditMeta = [], ?string $reason = null): array
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            throw new \RuntimeException('Evento no encontrado.');
        }

        $isAlreadyActive = (int) ($event['is_paid'] ?? 0) === 1
            && (int) ($event['is_demo'] ?? 1) === 0
            && (string) ($event['service_status'] ?? '') === 'active'
            && !empty($event['paid_until']);

        if ($isAlreadyActive) {
            return [
                'already_processed' => true,
                'event_id' => $eventId,
                'payment_reference' => null,
            ];
        }

        $manualReference = sprintf(
            'manual-%s-%s-%s',
            substr(str_replace('-', '', $eventId), 0, 8),
            date('YmdHis'),
            bin2hex(random_bytes(3))
        );

        $auditPayload = [
            'source' => 'admin_manual_mark_paid',
            'timestamp' => date(DATE_ATOM),
            'admin_user_id' => $auditMeta['admin_user_id'] ?? null,
            'admin_email' => $auditMeta['admin_email'] ?? null,
            'ip' => $auditMeta['ip'] ?? null,
            'user_agent' => $auditMeta['user_agent'] ?? null,
            'reason' => $reason,
        ];

        $result = $this->persistSuccessfulPayment([
            'event_id' => $eventId,
            'payment_provider' => 'manual',
            'payment_reference' => $manualReference,
            'amount' => $this->settingModel->getEventPrice(),
            'currency' => strtoupper((string) env('STRIPE_CURRENCY', 'MXN')),
            'status' => 'completed',
            'customer_email' => $event['primary_contact_email'] ?? null,
            'customer_name' => $event['couple_title'] ?? null,
            'payment_method' => 'manual_override',
            'paid_at' => date('Y-m-d H:i:s'),
            'provider_event_id' => null,
            'webhook_received_at' => date('Y-m-d H:i:s'),
            'notes' => sprintf(
                'Manual override by admin %s%s',
                (string) ($auditMeta['admin_user_id'] ?? 'unknown'),
                $reason !== null && trim($reason) !== '' ? ' | reason: ' . trim($reason) : ''
            ),
            'webhook_payload' => json_encode($auditPayload, JSON_THROW_ON_ERROR),
        ], $manualReference);

        return [
            'already_processed' => $result['already_processed'] ?? false,
            'event_id' => $eventId,
            'payment_reference' => $manualReference,
        ];
    }

    private function persistSuccessfulPayment(array $paymentData, string $correlationId): array
    {
        $db = \Config\Database::connect();
        $db->transException(true)->transStart();

        try {
            $provider = (string) $paymentData['payment_provider'];
            $reference = (string) $paymentData['payment_reference'];
            $eventId = (string) $paymentData['event_id'];

            if ($this->paymentModel->existsByReference($provider, $reference)) {
                $this->ensureEventActivated($eventId, $provider, $reference);
                $db->transComplete();

                log_message('info', 'PaymentService::persistSuccessfulPayment duplicate reference={reference} correlation={correlationId}', [
                    'reference' => $reference,
                    'correlationId' => $correlationId,
                ]);

                return ['already_processed' => true];
            }

            try {
                $this->paymentModel->createFromWebhook($paymentData);
            } catch (\RuntimeException $exception) {
                if (!str_contains(strtolower($exception->getMessage()), 'duplicate')) {
                    throw $exception;
                }
            }

            $this->ensureEventActivated($eventId, $provider, $reference);
            $db->transComplete();

            log_message('info', 'PaymentService::persistSuccessfulPayment processed event={eventId} reference={reference} correlation={correlationId}', [
                'eventId' => $eventId,
                'reference' => $reference,
                'correlationId' => $correlationId,
            ]);

            return ['already_processed' => false];
        } catch (\Throwable $exception) {
            $db->transRollback();
            throw $exception;
        }
    }

    private function validateExpectedAmount(string $eventId, float $amount, string $currency): void
    {
        if ($amount <= 0) {
            throw new \RuntimeException('Monto de pago inválido.');
        }

        $expectedAmount = round($this->settingModel->getEventPrice(), 2);
        $receivedAmount = round($amount, 2);

        if (abs($expectedAmount - $receivedAmount) > 0.01) {
            throw new \RuntimeException('Monto recibido no coincide con configuración de pago.');
        }

        $expectedCurrency = strtoupper((string) env('STRIPE_CURRENCY', 'MXN'));
        if (strtoupper($currency) !== $expectedCurrency) {
            throw new \RuntimeException('Moneda inválida para el pago.');
        }

        if (!$this->eventModel->find($eventId)) {
            throw new \RuntimeException('Evento no encontrado para pago.');
        }
    }

    private function activateEvent(string $eventId, string $provider, string $reference): void
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            throw new \RuntimeException('Event not found: ' . $eventId);
        }

        $eventDate = new \DateTime((string) $event['event_date_start']);
        $validDays = (int) $this->settingModel->getValue('payment_valid_days_after_event', 30);
        $paidUntil = $eventDate->modify(sprintf('+%d days', $validDays))->format('Y-m-d H:i:s');

        $updateData = [
            'is_demo' => 0,
            'is_paid' => 1,
            'service_status' => 'active',
            'visibility' => 'public',
            'paid_until' => $paidUntil,
        ];

        if (!$this->eventModel->update($eventId, $updateData)) {
            throw new \RuntimeException('Failed to activate event: ' . $eventId);
        }
    }

    private function ensureEventActivated(string $eventId, string $provider, string $reference): void
    {
        $event = $this->eventModel->find($eventId);
        if (!$event) {
            throw new \RuntimeException('Event not found: ' . $eventId);
        }

        $isAlreadyActive = (int) ($event['is_paid'] ?? 0) === 1
            && (int) ($event['is_demo'] ?? 1) === 0
            && (string) ($event['service_status'] ?? '') === 'active'
            && !empty($event['paid_until']);

        if ($isAlreadyActive) {
            return;
        }

        $this->activateEvent($eventId, $provider, $reference);
    }

    private function currentProviderName(): string
    {
        return $this->providerName;
    }

    private function loadProvider(string $name): PaymentProviderInterface
    {
        return match ($name) {
            'stripe' => new StripeProvider(),
            default => throw new \RuntimeException('Unsupported payment provider: ' . $name),
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\EventModel;
use App\Models\EventPaymentModel;
use App\Models\PaymentSettingModel;

class PaymentService
{
    private PaymentProviderInterface $provider;

    public function __construct(
        private readonly EventModel $eventModel = new EventModel(),
        private readonly EventPaymentModel $paymentModel = new EventPaymentModel(),
        private readonly PaymentSettingModel $settingModel = new PaymentSettingModel(),
        ?string $providerName = null,
    ) {
        $providerName ??= (string) $this->settingModel->getValue('default_payment_provider', env('PAYMENT_DEFAULT_PROVIDER', 'stripe'));
        $this->provider = $this->loadProvider($providerName);
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
            throw new \RuntimeException('Session ID inválido.');
        }

        return $this->provider->getCheckoutSessionStatus($sessionId);
    }

    public function processWebhook(string $payload, string $signature): bool
    {
        if (!$this->provider->verifyWebhook($payload, $signature)) {
            return false;
        }

        $paymentData = $this->provider->processPayment($payload);
        $eventId = (string) ($paymentData['event_id'] ?? '');
        if ($eventId === '') {
            throw new \RuntimeException('Event ID not found in webhook payload.');
        }

        $provider = (string) $this->settingModel->getValue('default_payment_provider', env('PAYMENT_DEFAULT_PROVIDER', 'stripe'));
        $reference = (string) $paymentData['payment_reference'];
        if ($this->paymentModel->existsByReference($provider, $reference)) {
            return true;
        }

        $paymentId = $this->paymentModel->createFromWebhook([
            'event_id' => $eventId,
            'payment_provider' => $provider,
            'payment_reference' => $reference,
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'],
            'status' => $paymentData['status'],
            'customer_email' => $paymentData['customer_email'],
            'customer_name' => $paymentData['customer_name'],
            'payment_method' => $paymentData['payment_method'],
            'paid_at' => $paymentData['paid_at'],
            'webhook_received_at' => date('Y-m-d H:i:s'),
            'webhook_payload' => $payload,
        ]);

        if (!$paymentId) {
            throw new \RuntimeException('Failed to store payment.');
        }

        $this->activateEvent($eventId, $provider, $reference);

        return true;
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

        $this->eventModel->update($eventId, [
            'is_demo' => 0,
            'is_paid' => 1,
            'service_status' => 'active',
            'visibility' => 'public',
            'payment_provider' => $provider,
            'payment_reference' => $reference,
            'paid_until' => $paidUntil,
        ]);
    }

    private function loadProvider(string $name): PaymentProviderInterface
    {
        return match ($name) {
            'stripe' => new StripeProvider(),
            default => throw new \RuntimeException('Unsupported payment provider: ' . $name),
        };
    }
}

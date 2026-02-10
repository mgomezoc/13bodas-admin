<?php

declare(strict_types=1);

namespace App\Libraries;

interface PaymentProviderInterface
{
    public function createCheckoutSession(array $data): array;

    public function verifyWebhook(string $payload, string $signature): bool;

    public function processPayment(string $payload): array;

    public function getCheckoutSessionStatus(string $sessionId): array;
}

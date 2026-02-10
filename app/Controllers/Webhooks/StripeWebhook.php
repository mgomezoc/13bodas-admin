<?php

declare(strict_types=1);

namespace App\Controllers\Webhooks;

use App\Controllers\BaseController;
use App\Libraries\PaymentService;
use CodeIgniter\HTTP\ResponseInterface;

class StripeWebhook extends BaseController
{
    private ?PaymentService $paymentService = null;

    public function handle(): ResponseInterface
    {
        $correlationId = bin2hex(random_bytes(8));
        $payload = $this->request->getBody();
        $signature = trim($this->request->getHeaderLine('Stripe-Signature'));

        if ($payload === '' || $signature === '') {
            log_message('warning', '[{correlationId}] Stripe webhook missing payload/signature', [
                'correlationId' => $correlationId,
            ]);

            return $this->response->setStatusCode(400)->setBody('Bad Request');
        }

        try {
            $processed = $this->paymentService()->processWebhook($payload, $signature, $correlationId);

            if ($processed) {
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            return $this->response->setStatusCode(400)->setBody('Invalid signature');
        } catch (\InvalidArgumentException $exception) {
            log_message('warning', '[{correlationId}] Stripe webhook validation failed: {message}', [
                'correlationId' => $correlationId,
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(400)->setBody('Bad Request');
        } catch (\Throwable $exception) {
            log_message('error', '[{correlationId}] Stripe webhook internal error: {message}', [
                'correlationId' => $correlationId,
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(500)->setBody('Internal Server Error');
        }
    }

    private function paymentService(): PaymentService
    {
        if ($this->paymentService === null) {
            $this->paymentService = new PaymentService(providerName: 'stripe');
        }

        return $this->paymentService;
    }
}

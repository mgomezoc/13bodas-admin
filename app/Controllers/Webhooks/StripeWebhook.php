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
        $payload = $this->request->getBody();
        $signature = (string) $this->request->getHeaderLine('Stripe-Signature');

        if ($payload === '' || $signature === '') {
            return $this->response->setStatusCode(400)->setBody('Bad Request');
        }

        try {
            if ($this->paymentService()->processWebhook($payload, $signature)) {
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            return $this->response->setStatusCode(400)->setBody('Invalid signature');
        } catch (\Throwable $exception) {
            log_message('error', 'Stripe webhook error: {message}', ['message' => $exception->getMessage()]);
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

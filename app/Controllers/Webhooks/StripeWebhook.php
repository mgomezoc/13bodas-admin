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

        // CORRECCIÓN CRÍTICA: Usamos el flujo de entrada directo de PHP.
        // Esto evita que el framework altere el payload (trim, encoding, etc.)
        // lo cual es la causa #1 de fallos de firma en Stripe.
        $payload = @file_get_contents('php://input');

        $signature = trim($this->request->getHeaderLine('Stripe-Signature'));

        if (empty($payload) || empty($signature)) {
            log_message('warning', '[{correlationId}] Stripe webhook rechazado: Falta payload o firma.', [
                'correlationId' => $correlationId,
            ]);
            return $this->response->setStatusCode(400)->setBody('Bad Request: Missing data');
        }

        // Logs para depuración (puedes comentarlos en producción)
        log_message('debug', "[{correlationId}] Inicio Webhook. Payload: {bytes} bytes. Firma: {sig}...", [
            'correlationId' => $correlationId,
            'bytes'         => strlen($payload),
            'sig'           => substr($signature, 0, 15)
        ]);

        try {
            $processed = $this->paymentService()->processWebhook($payload, $signature, $correlationId);

            if ($processed) {
                return $this->response->setStatusCode(200)->setBody('OK');
            }

            // Si llegamos aquí, falló la verificación en el Provider
            return $this->response->setStatusCode(400)->setBody('Invalid signature');
        } catch (\InvalidArgumentException $exception) {
            log_message('warning', '[{correlationId}] Validación fallida: {message}', [
                'correlationId' => $correlationId,
                'message'       => $exception->getMessage(),
            ]);
            return $this->response->setStatusCode(400)->setBody('Bad Request');
        } catch (\Throwable $exception) {
            log_message('error', '[{correlationId}] Error interno: {message}', [
                'correlationId' => $correlationId,
                'message'       => $exception->getMessage(),
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

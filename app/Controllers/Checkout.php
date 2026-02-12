<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\PaymentService;
use App\Models\EventModel;
use App\Models\PaymentSettingModel;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Checkout extends BaseController
{
    private ?PaymentService $paymentService = null;

    public function __construct(
        private readonly EventModel $eventModel = new EventModel(),
        private readonly PaymentSettingModel $settingModel = new PaymentSettingModel(),
    ) {
    }

    public function index(string $eventId): string|ResponseInterface
    {
        if (!$this->canAccessEvent($eventId)) {
            return redirect()->route('admin.dashboard')->with('error', 'No tienes acceso a este evento.');
        }

        $event = $this->eventModel->find($eventId);
        if (!$event) {
            return redirect()->route('admin.events.index')->with('error', 'Evento no encontrado.');
        }

        if ((int) $event['is_paid'] === 1) {
            return redirect()->to(site_url(route_to('admin.events.view', $eventId)))->with('info', 'Este evento ya está activado.');
        }

        return view('checkout/index', [
            'event' => $event,
            'price' => $this->settingModel->getEventPrice(),
        ]);
    }

    public function createSession(string $eventId): ResponseInterface
    {
        try {
            if (!$this->canAccessEvent($eventId)) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Sin acceso.']);
            }

            $session = $this->paymentService()->createCheckout($eventId);

            log_message('info', 'Checkout::createSession event={eventId} session={sessionId}', [
                'eventId' => $eventId,
                'sessionId' => $session['session_id'] ?? '',
            ]);

            return $this->response->setJSON([
                'success' => true,
                'session_id' => $session['session_id'],
                'checkout_url' => $session['checkout_url'],
            ]);
        } catch (Throwable $exception) {
            $errorId = bin2hex(random_bytes(6));
            $isDebugEnabled = ENVIRONMENT !== 'production';

            log_message('error', '[{errorId}] Checkout::createSession error: {message}', [
                'errorId' => $errorId,
                'message' => $exception->getMessage(),
            ]);

            $response = [
                'success' => false,
                'message' => 'No fue posible inicializar el pago. Verifica la configuración de Stripe.',
                'error_id' => $errorId,
            ];

            // Solo exponer detalles técnicos en entorno de desarrollo
            if (ENVIRONMENT === 'development') {
                $response['debug_detail'] = $exception->getMessage();
            }

            return $this->response->setStatusCode(400)->setJSON($response);
        }
    }

    public function success(): string|ResponseInterface
    {
        $sessionId = trim((string) $this->request->getGet('session_id'));
        $eventIdFromQuery = trim((string) $this->request->getGet('event_id'));

        log_message('info', 'Checkout::success CALLED session={s} event={e}', [
            's' => $sessionId,
            'e' => $eventIdFromQuery,
        ]);

        if ($sessionId === '') {
            return redirect()->route('admin.events.index')->with('error', 'No se recibió el identificador de sesión de Stripe.');
        }

        try {
            $finalization = $this->paymentService()->finalizeCheckoutSession($sessionId);
            $eventId = (string) ($finalization['event_id'] ?? '');

            log_message('info', 'Checkout::success session={sessionId} event={eventId} paid={paid} processed={processed}', [
                'sessionId' => $sessionId,
                'eventId' => $eventId !== '' ? $eventId : $eventIdFromQuery,
                'paid' => ($finalization['is_paid'] ?? false) ? '1' : '0',
                'processed' => ($finalization['already_processed'] ?? false) ? '1' : '0',
            ]);

            if (($finalization['is_paid'] ?? false) === true) {
                // CORREGIDO: Redirigir al evento específico, no al dashboard general
                $redirectEventId = $eventId !== '' ? $eventId : $eventIdFromQuery;
                if ($redirectEventId !== '') {
                    return redirect()->to(site_url(route_to('admin.events.view', $redirectEventId)))
                        ->with('success', '¡Pago exitoso! Tu evento ha sido activado correctamente.');
                }
                return redirect()->route('admin.events.index')
                    ->with('success', 'Pago confirmado. Tu evento fue activado correctamente.');
            }

            return view('checkout/success', [
                'sessionId' => $sessionId,
                'isPaid' => false,
                'paymentStatus' => (string) ($finalization['payment_status'] ?? 'unknown'),
                'eventId' => $eventId,
                'errorMessage' => 'Stripe aún no confirma el pago. Si ya pagaste, recarga esta página en unos segundos para reintentar la verificación.',
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Checkout::success validation error: {message}', ['message' => $exception->getMessage()]);

            return view('checkout/success', [
                'sessionId' => $sessionId,
                'isPaid' => false,
                'paymentStatus' => 'unknown',
                'eventId' => '',
                'errorMessage' => 'No fue posible verificar el estado del pago en este momento.',
            ]);
        }
    }

    public function cancel(): string|ResponseInterface
    {
        $eventId = trim((string) $this->request->getGet('event_id'));

        if ($eventId !== '' && $this->canAccessEvent($eventId)) {
            return redirect()->to(site_url(route_to('checkout.index', $eventId)))->with('info', 'El pago fue cancelado. Puedes intentarlo de nuevo cuando gustes.');
        }

        return view('checkout/cancel', [
            'eventId' => $eventId,
        ]);
    }

    private function canAccessEvent(string $eventId): bool
    {
        $roles = session()->get('user_roles');
        $roles = is_array($roles) ? $roles : [];

        if (in_array('superadmin', $roles, true) || in_array('admin', $roles, true) || in_array('staff', $roles, true)) {
            return true;
        }

        $clientId = (string) (session()->get('client_id') ?? '');
        if ($clientId === '') {
            return false;
        }

        $event = $this->eventModel->find($eventId);

        return $event && (string) $event['client_id'] === $clientId;
    }

    private function paymentService(): PaymentService
    {
        if ($this->paymentService === null) {
            $this->paymentService = new PaymentService();
        }

        return $this->paymentService;
    }
}

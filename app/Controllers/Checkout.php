<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\PaymentService;
use App\Models\EventModel;
use App\Models\PaymentSettingModel;
use CodeIgniter\HTTP\ResponseInterface;

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
            return redirect()->to(base_url('admin/events/view/' . $eventId))->with('info', 'Este evento ya está activado.');
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

            return $this->response->setJSON([
                'success' => true,
                'session_id' => $session['session_id'],
                'checkout_url' => $session['checkout_url'],
            ]);
        } catch (\Throwable $exception) {
            log_message('error', 'Checkout::createSession error: {message}', ['message' => $exception->getMessage()]);
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    public function success(): string
    {
        return view('checkout/success', ['sessionId' => (string) $this->request->getGet('session_id')]);
    }

    public function cancel(): string
    {
        return view('checkout/cancel');
    }

    private function canAccessEvent(string $eventId): bool
    {
        $roles = session()->get('user_roles') ?? [];

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

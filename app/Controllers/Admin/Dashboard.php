<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\PaymentService;
use App\Models\ClientModel;
use App\Models\EventModel;
use App\Models\LeadModel;
use App\Models\GuestModel;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

class Dashboard extends BaseController
{
    public function index(): string|RedirectResponse
    {
        $checkoutRedirect = $this->handleCheckoutReturn();
        if ($checkoutRedirect instanceof RedirectResponse) {
            return $checkoutRedirect;
        }

        $session = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isClient = in_array('client', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles);
        
        $data = [
            'pageTitle' => 'Dashboard'
        ];

        if ($isClient) {
            // Dashboard del cliente
            return $this->clientDashboard($data);
        }
        
        // Dashboard del admin
        return $this->adminDashboard($data);
    }

    /**
     * Dashboard para Admin/Staff
     */
    protected function adminDashboard(array $data): string
    {
        $clientModel = new ClientModel();
        $eventModel = new EventModel();
        $leadModel = new LeadModel();

        // Estadísticas generales
        $data['stats'] = [
            'total_clients' => $clientModel->countAllResults(),
            'total_events' => $eventModel->countAllResults(),
            'active_events' => $eventModel->where('service_status', 'active')->countAllResults(),
            'total_leads' => $leadModel->countAllResults(),
            'new_leads' => $leadModel->where('status', 'new')->countAllResults(),
            'leads_this_month' => $leadModel->where('created_at >=', date('Y-m-01'))->countAllResults(),
        ];

        // Últimos leads
        $data['recent_leads'] = $leadModel->orderBy('created_at', 'DESC')->findAll(5);

        // Próximos eventos
        $data['upcoming_events'] = $eventModel
            ->select('events.*, users.full_name as client_name')
            ->join('clients', 'clients.id = events.client_id')
            ->join('users', 'users.id = clients.user_id')
            ->where('events.event_date_start >=', date('Y-m-d'))
            ->orderBy('events.event_date_start', 'ASC')
            ->findAll(5);

        // Eventos recientes
        $data['recent_events'] = $eventModel
            ->select('events.*, users.full_name as client_name')
            ->join('clients', 'clients.id = events.client_id')
            ->join('users', 'users.id = clients.user_id')
            ->orderBy('events.created_at', 'DESC')
            ->findAll(5);

        return view('admin/dashboard/index', $data);
    }

    /**
     * Dashboard para Clientes
     */
    protected function clientDashboard(array $data): string
    {
        $session = session();
        $clientId = $session->get('client_id');
        
        $eventModel = new EventModel();
        $guestModel = new GuestModel();
        
        // Obtener evento del cliente
        $event = $eventModel->where('client_id', $clientId)->first();
        
        if (!$event) {
            $data['has_event'] = false;
            return view('admin/dashboard/client', $data);
        }

        $data['has_event'] = true;
        $data['event'] = $event;
        
        // Estadísticas del evento
        $data['stats'] = $eventModel->getEventStats($event['id']);
        $data['rsvp_stats'] = $guestModel->getRsvpStatsByEvent($event['id']);

        // URL de la invitación
        $data['invitation_url'] = base_url('i/' . $event['slug']);

        return view('admin/dashboard/client', $data);
    }

    private function handleCheckoutReturn(): ?RedirectResponse
    {
        $sessionId = trim((string) $this->request->getGet('session_id'));
        if ($sessionId === '') {
            return null;
        }

        try {
            $finalization = (new PaymentService())->finalizeCheckoutSession($sessionId);
            $eventId = trim((string) ($finalization['event_id'] ?? (string) $this->request->getGet('event_id')));

            if (($finalization['is_paid'] ?? false) !== true) {
                return redirect()->route('admin.events.index')
                    ->with('warning', 'Stripe aún no confirma el pago. Recarga la página en unos segundos.');
            }

            if ($eventId !== '') {
                return redirect()->to(site_url(route_to('admin.events.view', $eventId)))
                    ->with('success', 'Pago confirmado. Tu evento fue activado correctamente.');
            }

            return redirect()->route('admin.events.index')
                ->with('success', 'Pago confirmado. Tu evento fue activado correctamente.');
        } catch (Throwable $exception) {
            log_message('error', 'Dashboard::handleCheckoutReturn error: {message}', ['message' => $exception->getMessage()]);

            return redirect()->route('admin.events.index')
                ->with('error', 'No fue posible confirmar el pago desde el dashboard.');
        }
    }
}

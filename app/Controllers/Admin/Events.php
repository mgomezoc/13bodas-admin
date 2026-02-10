<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\ClientModel;
use App\Models\GuestModel;
use App\Models\ContentModuleModel;
use App\Models\EventTemplateModel;
use App\Models\TemplateModel;

class Events extends BaseController
{
    protected EventModel $eventModel;
    protected ClientModel $clientModel;
    protected EventTemplateModel $eventTemplateModel;
    protected TemplateModel $templateModel;

    public function __construct()
    {
        $this->eventModel         = new EventModel();
        $this->clientModel        = new ClientModel();
        $this->eventTemplateModel = new EventTemplateModel();
        $this->templateModel      = new TemplateModel();
    }

    public function index()
    {
        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];

        $isClient = in_array('client', $userRoles, true)
            && !in_array('admin', $userRoles, true)
            && !in_array('superadmin', $userRoles, true);

        if ($isClient) {
            $clientId = $session->get('client_id');
            $event    = $this->eventModel->where('client_id', $clientId)->first();

            if ($event) {
                return redirect()->to(base_url('admin/events/edit/' . $event['id']));
            }

            return view('admin/events/no_event');
        }

        return view('admin/events/index', ['pageTitle' => 'Eventos']);
    }

    public function list()
    {
        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];

        $isClient = in_array('client', $userRoles, true)
            && !in_array('admin', $userRoles, true)
            && !in_array('superadmin', $userRoles, true);

        $filters = [
            'search'         => $this->request->getGet('search'),
            'service_status' => $this->request->getGet('service_status'),
        ];

        if ($isClient) {
            $filters['client_id'] = $session->get('client_id');
        }

        $events = $this->eventModel->listWithClients($filters);

        foreach ($events as &$event) {
            $stats = $this->eventModel->getEventStats($event['id']);
            $event['guest_count']     = $stats['total_guests'] ?? 0;
            $event['confirmed_count'] = $stats['confirmed'] ?? 0;

            // (Opcional) exponer plantilla activa para listados
            $event['template_id'] = $this->eventTemplateModel->getActiveTemplateId($event['id']);
        }
        unset($event);

        return $this->response->setJSON([
            'total' => count($events),
            'rows'  => $events,
        ]);
    }

    public function create()
    {
        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isAdmin   = in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);

        $clients          = $this->clientModel->listWithUsers();
        $selectedClientId = $this->request->getGet('client_id');
        return view('admin/events/create', [
            'pageTitle'        => 'Nuevo Evento',
            'clients'          => $clients,
            'selectedClientId' => $selectedClientId,
            'timezones'        => $this->getTimezones(),
            'isAdmin'          => $isAdmin,
        ]);
    }

    public function store()
    {
        $rules = [
            'client_id'        => 'required',
            'couple_title'     => 'required|min_length[3]|max_length[255]',
            'slug'             => 'required|alpha_dash|min_length[3]|max_length[100]|is_unique[events.slug]',
            'event_date_start' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Enums reales en tu BD:
        // events.service_status: draft|active|suspended|archived
        // events.visibility: public|private
        $eventData = [
            'client_id'             => $this->request->getPost('client_id'),
            'couple_title'          => $this->request->getPost('couple_title'),
            'slug'                  => $this->request->getPost('slug'),
            'primary_contact_email' => $this->request->getPost('primary_contact_email'),
            'time_zone'             => $this->request->getPost('time_zone') ?: 'America/Mexico_City',
            'event_date_start'      => $this->formatDateTime($this->request->getPost('event_date_start')),
            'event_date_end'        => $this->formatDateTime($this->request->getPost('event_date_end')),
            'rsvp_deadline'         => $this->formatDateTime($this->request->getPost('rsvp_deadline')),
            'venue_name'            => $this->request->getPost('venue_name'),
            'venue_address'         => $this->request->getPost('venue_address'),
            'venue_geo_lat'         => $this->request->getPost('venue_geo_lat') ?: null,
            'venue_geo_lng'         => $this->request->getPost('venue_geo_lng') ?: null,

            // Defaults correctos
            'service_status'        => 'draft',
            'visibility'            => 'private',
            'access_mode'           => 'open',
        ];

        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isAdmin   = in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);

        if ($isAdmin) {
            $serviceStatus = $this->request->getPost('service_status');
            if (!empty($serviceStatus)) {
                $allowed = ['draft', 'active', 'suspended', 'archived'];
                if (!in_array($serviceStatus, $allowed, true)) {
                    return redirect()->back()->withInput()->with('error', 'service_status inválido.');
                }
                $eventData['service_status'] = $serviceStatus;
            }

            $visibility = $this->request->getPost('visibility');
            if (!empty($visibility)) {
                $allowed = ['public', 'private'];
                if (!in_array($visibility, $allowed, true)) {
                    return redirect()->back()->withInput()->with('error', 'visibility inválido.');
                }
                $eventData['visibility'] = $visibility;
            }

            $accessMode = $this->request->getPost('access_mode');
            if (!empty($accessMode)) {
                $allowed = ['open', 'invite_code'];
                if (!in_array($accessMode, $allowed, true)) {
                    return redirect()->back()->withInput()->with('error', 'access_mode inválido.');
                }
                $eventData['access_mode'] = $accessMode;
            }

            $eventData['is_demo'] = $this->request->getPost('is_demo') ? 1 : 0;
            $eventData['is_paid'] = $this->request->getPost('is_paid') ? 1 : 0;
            $paidUntil = $this->request->getPost('paid_until');
            $eventData['paid_until'] = $eventData['is_paid'] ? $this->formatDateTime($paidUntil) : null;

        }

        $eventId = $this->eventModel->createEvent($eventData);

        if ($eventId) {
            (new ContentModuleModel())->createDefaultModules($eventId);

            // (Opcional) si viene plantilla seleccionada en create, persistirla
            $templateId = $this->request->getPost('template_id');
            if ($templateId !== null && $templateId !== '') {
                $this->eventTemplateModel->setActiveTemplate($eventId, (int)$templateId);
            }

            return redirect()
                ->to(base_url('admin/events/edit/' . $eventId))
                ->with('success', 'Evento creado correctamente. Ahora puedes completar la información.');
        }

        return redirect()->back()->withInput()->with('error', 'Error al crear el evento. Por favor intenta de nuevo.');
    }

    public function view(string $id)
    {
        $event = $this->eventModel->getWithClient($id);

        if (!$event) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        if (!$this->canAccessEvent($id)) {
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'No tienes acceso a este evento.');
        }

        $stats     = $this->eventModel->getEventStats($id);
        $rsvpStats = (new GuestModel())->getRsvpStatsByEvent($id);

        // plantilla activa
        $event['template_id'] = $this->eventTemplateModel->getActiveTemplateId($id);

        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isAdmin   = in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);

        return view('admin/events/view', [
            'pageTitle'     => $event['couple_title'],
            'event'         => $event,
            'stats'         => $stats,
            'rsvpStats'     => $rsvpStats,
            'isAdmin'       => $isAdmin,
            'invitationUrl' => base_url('i/' . $event['slug']),
        ]);
    }

    public function edit(string $id)
    {
        $event = $this->eventModel->getWithClient($id);

        if (!$event) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        if (!$this->canAccessEvent($id)) {
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'No tienes acceso a este evento.');
        }

        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];

        $isClient = in_array('client', $userRoles, true)
            && !in_array('admin', $userRoles, true)
            && !in_array('superadmin', $userRoles, true);

        $isAdmin = in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);

        $modules   = (new ContentModuleModel())->getByEvent($id);
        $stats     = $this->eventModel->getEventStats($id);
        $rsvpStats = (new GuestModel())->getRsvpStatsByEvent($id);

        // IMPORTANTÍSIMO: plantilla activa viene de event_templates
        $event['template_id'] = $this->eventTemplateModel->getActiveTemplateId($id);

        return view('admin/events/edit', [
            'pageTitle'     => 'Editar: ' . $event['couple_title'],
            'event'         => $event,
            'modules'       => $modules,
            'stats'         => $stats,
            'rsvpStats'     => $rsvpStats,
            'timezones'     => $this->getTimezones(),
            'isClient'      => $isClient,
            'isAdmin'       => $isAdmin,
            'invitationUrl' => base_url('i/' . $event['slug']),
            'clients'       => $isClient ? [] : $this->clientModel->listWithUsers(),
            'templates'     => $this->getTemplateOptions($event['template_id']),
        ]);
    }

    public function updateSettings(string $id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Evento no encontrado.',
            ]);
        }

        if (!$this->canAccessEvent($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sin acceso.',
            ]);
        }

        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isAdmin   = in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);

        if (!$isAdmin) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permisos para modificar la configuración del evento.',
            ]);
        }

        $eventData = [];

        $serviceStatus = $this->request->getPost('service_status');
        if ($serviceStatus !== null && $serviceStatus !== '') {
            $allowedStatuses = ['draft', 'active', 'suspended', 'archived'];
            if (!in_array($serviceStatus, $allowedStatuses, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'service_status inválido.',
                ]);
            }
            $eventData['service_status'] = $serviceStatus;
        }

        $visibility = $this->request->getPost('visibility');
        if ($visibility !== null && $visibility !== '') {
            $allowedVisibility = ['public', 'private'];
            if (!in_array($visibility, $allowedVisibility, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'visibility inválido.',
                ]);
            }
            $eventData['visibility'] = $visibility;
        }

        $accessMode = $this->request->getPost('access_mode');
        if ($accessMode !== null && $accessMode !== '') {
            $allowedAccessMode = ['open', 'invite_code'];
            if (!in_array($accessMode, $allowedAccessMode, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'access_mode inválido.',
                ]);
            }
            $eventData['access_mode'] = $accessMode;
        }

        $eventData['is_demo'] = $this->request->getPost('is_demo') ? 1 : 0;
        $isPaid               = $this->request->getPost('is_paid') ? 1 : 0;
        $eventData['is_paid'] = $isPaid;

        if ($isPaid === 1) {
            $paidUntil = $this->request->getPost('paid_until');
            $eventData['paid_until'] = !empty($paidUntil) ? $this->formatDateTime($paidUntil) : null;
        } else {
            $eventData['paid_until'] = null;
        }

        if (!empty($eventData)) {
            $updated = $this->eventModel->update($id, $eventData);
            if (!$updated) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudo actualizar la configuración.',
                    'errors'  => $this->eventModel->errors(),
                ]);
            }
        }

        $templateId = $this->request->getPost('template_id');
        if ($templateId !== null && $templateId !== '') {
            $template = $this->templateModel->find((int) $templateId);
            if (!$template) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Template inválido.',
                ]);
            }

            $templateUpdated = $this->eventTemplateModel->setActiveTemplate($id, (int) $templateId);
            if (!$templateUpdated) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudo actualizar el template del evento.',
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Configuración actualizada.',
        ]);
    }

    private function getTemplateOptions(?int $activeTemplateId): array
    {
        $templates = $this->templateModel
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        if ($activeTemplateId !== null) {
            $existsInList = false;
            foreach ($templates as $template) {
                if ((int) ($template['id'] ?? 0) === $activeTemplateId) {
                    $existsInList = true;
                    break;
                }
            }

            if (!$existsInList) {
                $activeTemplate = $this->templateModel->find($activeTemplateId);
                if ($activeTemplate) {
                    $templates[] = $activeTemplate;
                }
            }
        }

        return $templates;
    }

    public function update(string $id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            return $this->jsonOrRedirect(false, 'Evento no encontrado.', base_url('admin/events'));
        }

        if (!$this->canAccessEvent($id)) {
            return $this->jsonOrRedirect(false, 'Sin acceso.', base_url('admin/dashboard'));
        }

        $rules = [
            'couple_title'     => 'required|min_length[3]|max_length[255]',
            'slug'             => "required|alpha_dash|min_length[3]|max_length[100]|is_unique[events.slug,id,{$id}]",
            'event_date_start' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->jsonOrRedirect(false, 'Error de validación.', null, $this->validator->getErrors());
        }

        $eventData = [
            'couple_title'          => $this->request->getPost('couple_title'),
            'bride_name'            => $this->request->getPost('bride_name') ?: null,
            'groom_name'            => $this->request->getPost('groom_name') ?: null,
            'slug'                  => $this->request->getPost('slug'),
            'primary_contact_email' => $this->request->getPost('primary_contact_email'),
            'time_zone'             => $this->request->getPost('time_zone') ?: 'America/Mexico_City',
            'event_date_start'      => $this->formatDateTime($this->request->getPost('event_date_start')),
            'event_date_end'        => $this->formatDateTime($this->request->getPost('event_date_end')),
            'rsvp_deadline'         => $this->formatDateTime($this->request->getPost('rsvp_deadline')),
            'venue_name'            => $this->request->getPost('venue_name'),
            'venue_address'         => $this->request->getPost('venue_address'),
            'venue_geo_lat'         => $this->request->getPost('venue_geo_lat') ?: null,
            'venue_geo_lng'         => $this->request->getPost('venue_geo_lng') ?: null,
        ];

        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isAdmin   = in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);

        // Campos restringidos + template_id (pivote) + nuevos campos admin-only de events
        $restrictedKeys  = [
            'client_id',
            'service_status',
            'visibility',

            // Paso 2: directos en events (admin-only)
            'access_mode',
            'is_demo',
            'is_paid',
            'paid_until',
        ];
        $triedRestricted = false;

        foreach ($restrictedKeys as $k) {
            $v = $this->request->getPost($k);
            if ($v !== null && $v !== '') {
                $triedRestricted = true;
                break;
            }
        }

        if (!$isAdmin && $triedRestricted) {
            return $this->jsonOrRedirect(false, 'No tienes permisos para modificar la configuración del evento.');
        }

        // Admin: validar/normalizar enums + nuevos campos
        if ($isAdmin) {
            $clientId = $this->request->getPost('client_id');
            if ($clientId !== null && $clientId !== '') {
                $eventData['client_id'] = $clientId;
            }

            $serviceStatus = $this->request->getPost('service_status');
            if ($serviceStatus !== null && $serviceStatus !== '') {
                $allowed = ['draft', 'active', 'suspended', 'archived'];
                if (!in_array($serviceStatus, $allowed, true)) {
                    return $this->jsonOrRedirect(false, 'service_status inválido.');
                }
                $eventData['service_status'] = $serviceStatus;
            }

            $visibility = $this->request->getPost('visibility');
            if ($visibility !== null && $visibility !== '') {
                $allowed = ['public', 'private'];
                if (!in_array($visibility, $allowed, true)) {
                    return $this->jsonOrRedirect(false, 'visibility inválido.');
                }
                $eventData['visibility'] = $visibility;
            }

            // Paso 2: directos en events (admin-only)
            $accessMode = $this->request->getPost('access_mode');
            if ($accessMode !== null && $accessMode !== '') {
                $allowed = ['open', 'invite_code'];
                if (!in_array($accessMode, $allowed, true)) {
                    return $this->jsonOrRedirect(false, 'access_mode inválido.');
                }
                $eventData['access_mode'] = $accessMode;
            }

            // checkboxes: si no vienen, quedan 0 (en UI mandamos hidden 0 para que siempre lleguen)
            $eventData['is_demo'] = $this->request->getPost('is_demo') ? 1 : 0;

            $isPaid = $this->request->getPost('is_paid') ? 1 : 0;
            $eventData['is_paid'] = $isPaid;

            // Regla: cuando is_paid = 0 => paid_until = NULL
            if ($isPaid === 1) {
                $paidUntil = $this->request->getPost('paid_until');
                $eventData['paid_until'] = !empty($paidUntil) ? $this->formatDateTime($paidUntil) : null;
            } else {
                $eventData['paid_until'] = null;
            }

        }

        // 1) Actualizar tabla events
        $updated = $this->eventModel->update($id, $eventData);
        if (!$updated) {
            return $this->jsonOrRedirect(false, 'No se pudo actualizar el evento.', null, $this->eventModel->errors());
        }

        // 2) Guardar plantilla activa (EN PIVOTE) para admin o cliente dueño del evento
        $templateId = $this->request->getPost('template_id');
        if ($templateId !== null && $templateId !== '') {
            $template = $this->templateModel->find((int) $templateId);
            if (!$template) {
                return $this->jsonOrRedirect(false, 'Template no encontrado.');
            }

            $isTemplateAssignable = (int) ($template['is_active'] ?? 0) === 1
                && ($isAdmin || (int) ($template['is_public'] ?? 0) === 1);

            if (!$isTemplateAssignable) {
                return $this->jsonOrRedirect(false, 'No tienes permisos para asignar este template.');
            }

            $ok = $this->eventTemplateModel->setActiveTemplate($id, (int) $templateId);
            if (!$ok) {
                return $this->jsonOrRedirect(false, 'El evento se guardó, pero falló la actualización de la plantilla.');
            }
        }

        // Mensaje consistente cuando no hubo cambios en events; si cambió plantilla, igual es éxito.
        $affected = $this->eventModel->db->affectedRows();
        if ((int)$affected === 0) {
            return $this->jsonOrRedirect(true, 'Guardado sin cambios (no hubo modificaciones).');
        }

        return $this->jsonOrRedirect(true, 'Evento actualizado correctamente.');
    }

    public function delete(string $id)
    {
        try {
            $event = $this->eventModel->find($id);

            if (!$event) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Evento no encontrado.',
                    ]);
                }

                return redirect()->back()->with('error', 'Evento no encontrado.');
            }

            $session   = session();
            $userRoles = $session->get('user_roles') ?? [];
            $isAdmin   = in_array('superadmin', $userRoles, true) || in_array('admin', $userRoles, true);

            if (!$isAdmin) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Sin acceso.',
                    ]);
                }

                return redirect()->back()->with('error', 'Sin acceso.');
            }

            $deleted = $this->eventModel->delete($id);

            if ($deleted) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Evento eliminado correctamente.',
                    ]);
                }

                return redirect()->to(base_url('admin/events'))
                    ->with('success', 'Evento eliminado correctamente.');
            }

            throw new \Exception('No se pudo eliminar el evento.');
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar: ' . $e->getMessage(),
                ]);
            }

            return redirect()->back()->with('error', 'Error al eliminar el evento.');
        }
    }

    public function checkSlug()
    {
        $slug      = $this->request->getPost('slug');
        $excludeId = $this->request->getPost('exclude_id');

        $available = $this->eventModel->isSlugAvailable($slug, $excludeId);

        return $this->response->setJSON([
            'available' => $available,
            'message'   => $available ? 'Slug disponible' : 'Este slug ya está en uso',
        ]);
    }

    public function preview(string $id)
    {
        $event = $this->eventModel->find($id);

        if (!$event || !$this->canAccessEvent($id)) {
            return redirect()->to(base_url('admin/events'));
        }

        return redirect()->to(base_url('i/' . $event['slug'] . '?preview=1'));
    }

    protected function canAccessEvent(string $eventId): bool
    {
        $session   = session();
        $userRoles = $session->get('user_roles') ?? [];

        if (
            in_array('superadmin', $userRoles, true) ||
            in_array('admin', $userRoles, true) ||
            in_array('staff', $userRoles, true)
        ) {
            return true;
        }

        $clientId = $session->get('client_id');
        if ($clientId) {
            $event = $this->eventModel->find($eventId);
            return $event && $event['client_id'] === $clientId;
        }

        return false;
    }

    protected function formatDateTime(?string $datetime): ?string
    {
        if (empty($datetime)) return null;

        if (strlen($datetime) === 19) return $datetime;           // YYYY-MM-DD HH:MM:SS
        if (strlen($datetime) === 16) return $datetime . ':00';   // YYYY-MM-DD HH:MM
        if (strlen($datetime) === 10) return $datetime . ' 00:00:00';

        return $datetime;
    }

    protected function jsonOrRedirect(bool $success, string $message, ?string $redirectUrl = null, $errors = null)
    {
        if ($this->request->isAJAX()) {
            $payload = ['success' => $success, 'message' => $message];
            if (!empty($errors)) $payload['errors'] = $errors;
            return $this->response->setJSON($payload);
        }

        if ($success) return redirect()->back()->with('success', $message);
        if ($redirectUrl) return redirect()->to($redirectUrl)->with('error', $message);

        return redirect()->back()->withInput()->with('errors', $errors ?: [])->with('error', $message);
    }

    protected function getTimezones(): array
    {
        return [
            'America/Mexico_City'  => '(GMT-6) Ciudad de México',
            'America/Monterrey'    => '(GMT-6) Monterrey',
            'America/Cancun'       => '(GMT-5) Cancún',
            'America/Tijuana'      => '(GMT-8) Tijuana',
            'America/Hermosillo'   => '(GMT-7) Hermosillo',
            'America/Los_Angeles'  => '(GMT-8) Los Ángeles',
            'America/New_York'     => '(GMT-5) Nueva York',
            'America/Chicago'      => '(GMT-6) Chicago',
            'America/Denver'       => '(GMT-7) Denver',
            'America/Bogota'       => '(GMT-5) Bogotá',
            'America/Lima'         => '(GMT-5) Lima',
            'America/Santiago'     => '(GMT-4) Santiago',
            'America/Buenos_Aires' => '(GMT-3) Buenos Aires',
            'Europe/Madrid'        => '(GMT+1) Madrid',
            'Europe/London'        => '(GMT+0) Londres',
        ];
    }
}

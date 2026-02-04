<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\ClientModel;
use App\Models\GuestModel;
use App\Models\ContentModuleModel;
use App\Models\TemplateModel;
use App\Models\EventTemplateModel;

class Events extends BaseController
{
    protected EventModel $eventModel;
    protected ClientModel $clientModel;
    protected EventTemplateModel $eventTemplateModel;

    public function __construct()
    {
        $this->eventModel         = new EventModel();
        $this->clientModel        = new ClientModel();
        $this->eventTemplateModel = new EventTemplateModel();
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
        $clients          = $this->clientModel->listWithUsers();
        $selectedClientId = $this->request->getGet('client_id');

        return view('admin/events/create', [
            'pageTitle'        => 'Nuevo Evento',
            'clients'          => $clients,
            'selectedClientId' => $selectedClientId,
            'timezones'        => $this->getTimezones(),
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
        // events.site_mode: auto|pre|live|post
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

            // Defaults correctos
            'service_status'        => 'draft',
            'site_mode'             => 'auto',
            'visibility'            => 'private',
        ];

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

        return view('admin/events/view', [
            'pageTitle'     => $event['couple_title'],
            'event'         => $event,
            'stats'         => $stats,
            'rsvpStats'     => $rsvpStats,
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

        $templates = (new TemplateModel())->where('is_public', 1)->findAll();

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
            'templates'     => $templates,
        ]);
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
            'site_mode',
            'visibility',
            'template_id',

            // Paso 2: directos en events (admin-only)
            'access_mode',
            'is_demo',
            'is_paid',
            'paid_until',
            'venue_config',
            'theme_config',
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

            $siteMode = $this->request->getPost('site_mode');
            if ($siteMode !== null && $siteMode !== '') {
                $allowed = ['auto', 'pre', 'live', 'post'];
                if (!in_array($siteMode, $allowed, true)) {
                    return $this->jsonOrRedirect(false, 'site_mode inválido.');
                }
                $eventData['site_mode'] = $siteMode;
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

            // JSON libre con validación básica
            // ✅ Permite limpiar: si el campo llega vacío => NULL
            $venueConfig = $this->request->getPost('venue_config');
            if ($venueConfig !== null) {
                $venueConfig = trim((string)$venueConfig);
                if ($venueConfig === '') {
                    $eventData['venue_config'] = null;
                } else {
                    json_decode($venueConfig);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $this->jsonOrRedirect(false, 'venue_config no es JSON válido.');
                    }
                    $eventData['venue_config'] = $venueConfig;
                }
            }

            $themeConfig = $this->request->getPost('theme_config');
            if ($themeConfig !== null) {
                $themeConfig = trim((string)$themeConfig);
                if ($themeConfig === '') {
                    $eventData['theme_config'] = null;
                } else {
                    json_decode($themeConfig);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return $this->jsonOrRedirect(false, 'theme_config no es JSON válido.');
                    }
                    $eventData['theme_config'] = $themeConfig;
                }
            }
        }

        // 1) Actualizar tabla events
        $updated = $this->eventModel->update($id, $eventData);
        if (!$updated) {
            return $this->jsonOrRedirect(false, 'No se pudo actualizar el evento.', null, $this->eventModel->errors());
        }

        // 2) Guardar plantilla activa (EN PIVOTE) si es admin y viene template_id
        if ($isAdmin) {
            $templateId = $this->request->getPost('template_id');
            if ($templateId !== null && $templateId !== '') {
                $ok = $this->eventTemplateModel->setActiveTemplate($id, (int)$templateId);
                if (!$ok) {
                    return $this->jsonOrRedirect(false, 'El evento se guardó, pero falló la actualización de la plantilla.');
                }
            }
        }

        // Mensaje consistente cuando no hubo cambios en events; si cambió plantilla, igual es éxito.
        $affected = $this->eventModel->db->affectedRows();
        if ((int)$affected === 0) {
            return $this->jsonOrRedirect(true, 'Guardado sin cambios (no hubo modificaciones).');
        }

        return $this->jsonOrRedirect(true, 'Evento actualizado correctamente.');
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

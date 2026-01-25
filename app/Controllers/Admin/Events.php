<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\ClientModel;
use App\Models\GuestModel;
use App\Models\ContentModuleModel;
use App\Models\TemplateModel;

class Events extends BaseController
{
    protected $eventModel;
    protected $clientModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->clientModel = new ClientModel();
    }

    /**
     * Lista de eventos
     */
    public function index()
    {
        $session = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isClient = in_array('client', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles);

        if ($isClient) {
            $clientId = $session->get('client_id');
            $event = $this->eventModel->where('client_id', $clientId)->first();
            
            if ($event) {
                return redirect()->to(base_url('admin/events/edit/' . $event['id']));
            }
            
            return view('admin/events/no_event');
        }

        return view('admin/events/index', ['pageTitle' => 'Eventos']);
    }

    /**
     * API: Lista de eventos para Bootstrap Table
     */
    public function list()
    {
        $session = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isClient = in_array('client', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles);

        $filters = [
            'search' => $this->request->getGet('search'),
            'service_status' => $this->request->getGet('service_status'),
        ];

        if ($isClient) {
            $filters['client_id'] = $session->get('client_id');
        }

        $events = $this->eventModel->listWithClients($filters);

        foreach ($events as &$event) {
            $stats = $this->eventModel->getEventStats($event['id']);
            $event['guest_count'] = $stats['total_guests'];
            $event['confirmed_count'] = $stats['confirmed'];
        }

        return $this->response->setJSON([
            'total' => count($events),
            'rows' => $events
        ]);
    }

    /**
     * Formulario para crear evento
     */
    public function create()
    {
        $clients = $this->clientModel->listWithUsers();
        $selectedClientId = $this->request->getGet('client_id');

        return view('admin/events/create', [
            'pageTitle' => 'Nuevo Evento',
            'clients' => $clients,
            'selectedClientId' => $selectedClientId,
            'timezones' => $this->getTimezones()
        ]);
    }

    /**
     * Guardar nuevo evento
     */
    public function store()
    {
        $rules = [
            'client_id' => 'required',
            'couple_title' => 'required|min_length[3]|max_length[255]',
            'slug' => 'required|alpha_dash|min_length[3]|max_length[100]|is_unique[events.slug]',
            'event_date_start' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $eventData = [
            'client_id' => $this->request->getPost('client_id'),
            'couple_title' => $this->request->getPost('couple_title'),
            'slug' => $this->request->getPost('slug'),
            'primary_contact_email' => $this->request->getPost('primary_contact_email'),
            'time_zone' => $this->request->getPost('time_zone') ?: 'America/Mexico_City',
            'event_date_start' => $this->formatDateTime($this->request->getPost('event_date_start')),
            'event_date_end' => $this->formatDateTime($this->request->getPost('event_date_end')),
            'rsvp_deadline' => $this->formatDateTime($this->request->getPost('rsvp_deadline')),
            'venue_name' => $this->request->getPost('venue_name'),
            'venue_address' => $this->request->getPost('venue_address'),
            'service_status' => 'pending',
            'site_mode' => 'draft',
            'visibility' => 'private'
        ];

        $eventId = $this->eventModel->createEvent($eventData);

        if ($eventId) {
            $contentModule = new ContentModuleModel();
            $contentModule->createDefaultModules($eventId);

            return redirect()->to(base_url('admin/events/edit/' . $eventId))
                ->with('success', 'Evento creado correctamente. Ahora puedes completar la información.');
        }

        return redirect()->back()->withInput()
            ->with('error', 'Error al crear el evento. Por favor intenta de nuevo.');
    }

    /**
     * Ver detalle del evento
     */
    public function view(string $id)
    {
        $event = $this->eventModel->getWithClient($id);

        if (!$event) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        if (!$this->canAccessEvent($id)) {
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'No tienes acceso a este evento.');
        }

        $stats = $this->eventModel->getEventStats($id);
        $guestModel = new GuestModel();
        $rsvpStats = $guestModel->getRsvpStatsByEvent($id);

        return view('admin/events/view', [
            'pageTitle' => $event['couple_title'],
            'event' => $event,
            'stats' => $stats,
            'rsvpStats' => $rsvpStats,
            'invitationUrl' => base_url('i/' . $event['slug'])
        ]);
    }

    /**
     * Formulario para editar evento (wizard/tabs)
     */
    public function edit(string $id)
    {
        $event = $this->eventModel->getWithClient($id);

        if (!$event) {
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        if (!$this->canAccessEvent($id)) {
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'No tienes acceso a este evento.');
        }

        $session = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isClient = in_array('client', $userRoles) && !in_array('admin', $userRoles) && !in_array('superadmin', $userRoles);

        $contentModule = new ContentModuleModel();
        $modules = $contentModule->getByEvent($id);

        $stats = $this->eventModel->getEventStats($id);
        $guestModel = new GuestModel();
        $rsvpStats = $guestModel->getRsvpStatsByEvent($id);

        // Obtener templates disponibles
        $templateModel = new TemplateModel();
        $templates = $templateModel->where('is_public', 1)->findAll();

        return view('admin/events/edit', [
            'pageTitle' => 'Editar: ' . $event['couple_title'],
            'event' => $event,
            'modules' => $modules,
            'stats' => $stats,
            'rsvpStats' => $rsvpStats,
            'timezones' => $this->getTimezones(),
            'isClient' => $isClient,
            'invitationUrl' => base_url('i/' . $event['slug']),
            'clients' => $isClient ? [] : $this->clientModel->listWithUsers(),
            'templates' => $templates
        ]);
    }

    /**
     * Actualizar evento
     */
    public function update(string $id)
    {
        $event = $this->eventModel->find($id);

        if (!$event) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Evento no encontrado.']);
            }
            return redirect()->to(base_url('admin/events'))->with('error', 'Evento no encontrado.');
        }

        if (!$this->canAccessEvent($id)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
            }
            return redirect()->to(base_url('admin/dashboard'))->with('error', 'Sin acceso.');
        }

        $rules = [
            'couple_title' => 'required|min_length[3]|max_length[255]',
            'slug' => "required|alpha_dash|min_length[3]|max_length[100]|is_unique[events.slug,id,{$id}]",
            'event_date_start' => 'required',
        ];

        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $this->validator->getErrors()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $eventData = [
            'couple_title' => $this->request->getPost('couple_title'),
            'slug' => $this->request->getPost('slug'),
            'primary_contact_email' => $this->request->getPost('primary_contact_email'),
            'time_zone' => $this->request->getPost('time_zone') ?: 'America/Mexico_City',
            'event_date_start' => $this->formatDateTime($this->request->getPost('event_date_start')),
            'event_date_end' => $this->formatDateTime($this->request->getPost('event_date_end')),
            'rsvp_deadline' => $this->formatDateTime($this->request->getPost('rsvp_deadline')),
            'venue_name' => $this->request->getPost('venue_name'),
            'venue_address' => $this->request->getPost('venue_address'),
            'venue_geo_lat' => $this->request->getPost('venue_geo_lat') ?: null,
            'venue_geo_lng' => $this->request->getPost('venue_geo_lng') ?: null,
        ];

        // Solo admin puede cambiar estos campos
        $session = session();
        $userRoles = $session->get('user_roles') ?? [];
        $isAdmin = in_array('superadmin', $userRoles) || in_array('admin', $userRoles);

        if ($isAdmin) {
            if ($this->request->getPost('client_id')) {
                $eventData['client_id'] = $this->request->getPost('client_id');
            }
            if ($this->request->getPost('service_status')) {
                $eventData['service_status'] = $this->request->getPost('service_status');
            }
            if ($this->request->getPost('site_mode')) {
                $eventData['site_mode'] = $this->request->getPost('site_mode');
            }
            if ($this->request->getPost('visibility')) {
                $eventData['visibility'] = $this->request->getPost('visibility');
            }
            if ($this->request->getPost('template_id')) {
                $eventData['template_id'] = $this->request->getPost('template_id');
            }
        }

        $this->eventModel->update($id, $eventData);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Evento actualizado correctamente.'
            ]);
        }

        return redirect()->back()->with('success', 'Evento actualizado correctamente.');
    }

    /**
     * Verificar disponibilidad de slug (AJAX)
     */
    public function checkSlug()
    {
        $slug = $this->request->getPost('slug');
        $excludeId = $this->request->getPost('exclude_id');

        $available = $this->eventModel->isSlugAvailable($slug, $excludeId);

        return $this->response->setJSON([
            'available' => $available,
            'message' => $available ? 'Slug disponible' : 'Este slug ya está en uso'
        ]);
    }

    /**
     * Preview del evento
     */
    public function preview(string $id)
    {
        $event = $this->eventModel->find($id);

        if (!$event || !$this->canAccessEvent($id)) {
            return redirect()->to(base_url('admin/events'));
        }

        return redirect()->to(base_url('i/' . $event['slug'] . '?preview=1'));
    }

    /**
     * Verificar si el usuario puede acceder al evento
     */
    protected function canAccessEvent(string $eventId): bool
    {
        $session = session();
        $userRoles = $session->get('user_roles') ?? [];
        
        if (in_array('superadmin', $userRoles) || in_array('admin', $userRoles) || in_array('staff', $userRoles)) {
            return true;
        }

        $clientId = $session->get('client_id');
        if ($clientId) {
            $event = $this->eventModel->find($eventId);
            return $event && $event['client_id'] === $clientId;
        }

        return false;
    }

    /**
     * Formatear fecha/hora
     */
    protected function formatDateTime(?string $datetime): ?string
    {
        if (empty($datetime)) {
            return null;
        }
        
        if (strlen($datetime) === 19) return $datetime;
        if (strlen($datetime) === 16) return $datetime . ':00';
        if (strlen($datetime) === 10) return $datetime . ' 00:00:00';
        
        return $datetime;
    }

    /**
     * Obtener lista de zonas horarias
     */
    protected function getTimezones(): array
    {
        return [
            'America/Mexico_City' => '(GMT-6) Ciudad de México',
            'America/Monterrey' => '(GMT-6) Monterrey',
            'America/Cancun' => '(GMT-5) Cancún',
            'America/Tijuana' => '(GMT-8) Tijuana',
            'America/Hermosillo' => '(GMT-7) Hermosillo',
            'America/Los_Angeles' => '(GMT-8) Los Ángeles',
            'America/New_York' => '(GMT-5) Nueva York',
            'America/Chicago' => '(GMT-6) Chicago',
            'America/Denver' => '(GMT-7) Denver',
            'America/Bogota' => '(GMT-5) Bogotá',
            'America/Lima' => '(GMT-5) Lima',
            'America/Santiago' => '(GMT-4) Santiago',
            'America/Buenos_Aires' => '(GMT-3) Buenos Aires',
            'Europe/Madrid' => '(GMT+1) Madrid',
            'Europe/London' => '(GMT+0) Londres',
        ];
    }
}

<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\TemplateModel;
use App\Models\ContentModuleModel;

class Invitation extends BaseController
{
    public function view($slug)
    {
        $db = \Config\Database::connect();

        // 1. Buscar el evento por slug
        $eventModel = new EventModel();
        $event = $eventModel->where('slug', $slug)->first();

        if (!$event) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Evento no encontrado");
        }

        // 2. Buscar el template ACTIVO para este evento
        // Hacemos un JOIN manual porque la relación está en otra tabla
        $builder = $db->table('event_templates');
        $builder->select('templates.*');
        $builder->join('templates', 'templates.id = event_templates.template_id');
        $builder->where('event_templates.event_id', $event['id']);
        $builder->where('event_templates.is_active', 1);
        $template = $builder->get()->getRowArray();

        if (!$template) {
            die("Error: Este evento no tiene un template activo asignado.");
        }

        // 3. Cargar módulos de contenido (Título, Historia, etc.)
        $moduleModel = new ContentModuleModel();
        // Usamos 'getByEvent' si existe en tu modelo, si no, usamos el where estándar
        $modules = $moduleModel->where('event_id', $event['id'])
            ->where('is_enabled', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        // 4. Preparar datos para la vista
        $data = [
            'event'    => $event,
            'modules'  => $modules,
            'template' => $template, // Datos del template (colores base, fuentes)
            'theme'    => json_decode($event['theme_config'] ?? '{}', true) // Personalización del evento
        ];

        // 5. Cargar la vista basada en el CÓDIGO del template
        // Convención: app/Views/templates/{code}/index.php
        $viewPath = 'templates/' . $template['code'] . '/index';

        if (!file_exists(APPPATH . 'Views/' . $viewPath . '.php')) {
            die("Error: El archivo del template '$viewPath' no existe.");
        }

        return view($viewPath, $data);
    }

    public function submitRsvp(string $slug)
    {
        // Respuesta estandarizada para AJAX
        $resp = ['success' => false, 'message' => 'Solicitud inválida.', 'data' => null];

        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON($resp);
        }

        $eventModel = new \App\Models\EventModel();
        $event = $eventModel->where('slug', $slug)->first();

        if (!$event || ($event['service_status'] ?? '') !== 'active') {
            $resp['message'] = 'Evento no disponible.';
            return $this->response->setJSON($resp);
        }

        $name = trim((string)$this->request->getPost('name'));
        $email = trim((string)$this->request->getPost('email'));
        $attending = (string)$this->request->getPost('attending');
        $message = trim((string)$this->request->getPost('message'));

        if ($name === '' || !in_array($attending, ['accepted', 'declined'], true)) {
            $resp['message'] = 'Completa tu nombre y selecciona si asistirás.';
            return $this->response->setJSON($resp);
        }

        // Split simple del nombre
        $parts = preg_split('/\s+/', $name, 2);
        $first = $parts[0] ?? $name;
        $last  = $parts[1] ?? '';

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Crear GuestGroup + Guest (modo open)
            $groupModel = new \App\Models\GuestGroupModel();
            $guestModel = new \App\Models\GuestModel();
            $rsvpModel  = new \App\Models\RsvpResponseModel();

            $groupId = $groupModel->createGroup([
                'event_id' => $event['id'],
                'group_name' => $name,
                'max_additional_guests' => 0,
                'is_vip' => 0,
                'notes' => 'RSVP público (open)',
                'current_status' => 'responded',
                'responded_at' => date('Y-m-d H:i:s'),
                'access_code' => substr(bin2hex(random_bytes(8)), 0, 12),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $guestId = $guestModel->createGuest([
                'group_id' => $groupId,
                'first_name' => $first,
                'last_name' => $last,
                'email' => ($email !== '' ? $email : null),
                'is_primary_contact' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $rsvpModel->saveResponse([
                'guest_id' => $guestId,
                'attending' => ($attending === 'accepted') ? 1 : 0,
                'attendee_count' => ($attending === 'accepted') ? 1 : 0,
                'dietary_notes' => null,
                'message_to_couple' => ($message !== '' ? $message : null),
                'submitted_at' => date('Y-m-d H:i:s'),
                'meta' => json_encode([
                    'source' => 'public',
                    'ip' => $this->request->getIPAddress(),
                    'ua' => (string)$this->request->getUserAgent(),
                ], JSON_UNESCAPED_UNICODE),
            ]);

            $db->transComplete();

            $resp['success'] = true;
            $resp['message'] = 'Confirmación registrada. ¡Gracias!';
            $resp['data'] = ['guest_id' => $guestId];

            return $this->response->setJSON($resp);
        } catch (\Throwable $e) {
            $db->transRollback();
            $resp['message'] = 'No fue posible registrar tu confirmación.';
            return $this->response->setJSON($resp);
        }
    }
}

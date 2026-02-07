<?php

namespace App\Controllers;

use App\Models\EventModel;
use App\Models\EventFaqItemModel;
use App\Models\EventScheduleItemModel;
use App\Models\ContentModuleModel;
use App\Models\TemplateModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Invitation extends BaseController
{
    public function view($slug)
    {
        $db = \Config\Database::connect();

        // 1) Buscar el evento por slug
        $eventModel = new EventModel();
        $event = $eventModel->where('slug', $slug)->first();

        if (!$event) {
            throw PageNotFoundException::forPageNotFound('Evento no encontrado');
        }

        // 2) Buscar template ACTIVO para el evento (usando el modelo)
        $templateModel = new TemplateModel();
        $template = $templateModel->getActiveForEvent($event['id']);

        if (!$template) {
            // No matamos la app con die; devolvemos 404 para mantener consistencia
            throw PageNotFoundException::forPageNotFound('Este evento no tiene un template activo asignado.');
        }

        // 3) Cargar módulos de contenido
        $moduleModel = new ContentModuleModel();
        $modules = $moduleModel->where('event_id', $event['id'])
            ->where('is_enabled', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        /**
         * 4) GALERÍA
         * Fuente primaria: media_assets
         * Fallback: filesystem /public/uploads/events/{event_id}/gallery/
         */
        $galleryAssets = [];

        try {
            // Nota: tu tabla media_assets NO tiene file_name/file_path; solo URLs.
            $galleryRows = $db->table('media_assets')
                ->select('id, file_url_original, file_url_thumbnail, file_url_large, alt_text, caption, sort_order, created_at, category, category_tag, is_private')
                ->where('event_id', $event['id'])
                ->where('is_private', 0)
                ->groupStart()
                ->where('category', 'gallery')
                ->orWhere('category_tag', 'gallery')
                ->groupEnd()
                ->orderBy('sort_order', 'ASC')
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($galleryRows as $r) {
                $full  = (string)($r['file_url_large'] ?? '');
                $thumb = (string)($r['file_url_thumbnail'] ?? '');
                $orig  = (string)($r['file_url_original'] ?? '');

                // Normalizar a URLs absolutas si vienen relativas
                if ($full !== '' && !preg_match('#^https?://#i', $full))  $full  = base_url($full);
                if ($thumb !== '' && !preg_match('#^https?://#i', $thumb)) $thumb = base_url($thumb);
                if ($orig !== '' && !preg_match('#^https?://#i', $orig))  $orig  = base_url($orig);

                $finalFull = $full !== '' ? $full : $orig;
                if ($finalFull === '') continue;

                $galleryAssets[] = [
                    'id'         => $r['id'] ?? null,
                    'full'       => $finalFull,
                    'thumb'      => $thumb !== '' ? $thumb : $finalFull,
                    'alt'        => (string)($r['alt_text'] ?? ($event['couple_title'] ?? 'Galería')),
                    'caption'    => (string)($r['caption'] ?? ''),
                    'sort_order' => (int)($r['sort_order'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {
            $galleryAssets = [];
        }

        // Fallback: filesystem si la BD no trae nada
        if (empty($galleryAssets)) {
            $eventId = $event['id'];

            $dir = rtrim(FCPATH, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR . 'uploads'
                . DIRECTORY_SEPARATOR . 'events'
                . DIRECTORY_SEPARATOR . $eventId
                . DIRECTORY_SEPARATOR . 'gallery';

            if (is_dir($dir)) {
                $files = glob($dir . DIRECTORY_SEPARATOR . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) ?: [];

                usort($files, function ($a, $b) {
                    return filemtime($a) <=> filemtime($b);
                });

                foreach ($files as $i => $abs) {
                    $filename = basename($abs);
                    $rel = 'uploads/events/' . $eventId . '/gallery/' . $filename;
                    $url = base_url($rel);

                    $galleryAssets[] = [
                        'id'         => null,
                        'full'       => $url,
                        'thumb'      => $url,
                        'alt'        => (string)($event['couple_title'] ?? 'Galería'),
                        'caption'    => '',
                        'sort_order' => $i + 1,
                    ];
                }
            }
        }

        /**
         * 5) REGALOS (registry_items)
         */
        $registryItems = [];
        $registryStats = [
            'total'       => 0,
            'claimed'     => 0,
            'available'   => 0,
            'total_value' => 0.0
        ];

        try {
            $registryItems = $db->table('registry_items')
                ->select('id, title, name, description, category, image_url, product_url, external_url, price, currency_code, is_fund, fund_goal, goal_amount, current_amount, amount_collected, quantity_requested, quantity_fulfilled, is_claimed, is_priority, is_visible, sort_order, claimed_by, claimed_at, created_at')
                ->where('event_id', $event['id'])
                ->where('is_visible', 1)
                ->orderBy('is_priority', 'DESC')
                ->orderBy('sort_order', 'ASC')
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();

            $registryStats['total'] = count($registryItems);

            foreach ($registryItems as $it) {
                $price = (float)($it['price'] ?? 0);
                $registryStats['total_value'] += $price;

                $claimed = (int)($it['is_claimed'] ?? 0) === 1;
                if ($claimed) $registryStats['claimed']++;
                else $registryStats['available']++;
            }
        } catch (\Throwable $e) {
            $registryItems = [];
            $registryStats = ['total' => 0, 'claimed' => 0, 'available' => 0, 'total_value' => 0.0];
        }

        /**
         * 6) Datos relacionados adicionales
         */
        $guestGroups = [];
        $guests = [];
        $rsvpResponses = [];
        $menuOptions = [];
        $weddingPartyMembers = [];
        $faqs = [];
        $scheduleItems = [];

        try {
            $guestGroups = $db->table('guest_groups')
                ->where('event_id', $event['id'])
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            $guestGroups = [];
        }

        try {
            $guests = $db->table('guests')
                ->where('event_id', $event['id'])
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            $guests = [];
        }

        try {
            $rsvpResponses = $db->table('rsvp_responses')
                ->where('event_id', $event['id'])
                ->orderBy('responded_at', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            $rsvpResponses = [];
        }

        try {
            $menuOptions = $db->table('menu_options')
                ->where('event_id', $event['id'])
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            $menuOptions = [];
        }

        try {
            $weddingPartyMembers = $db->table('wedding_party_members')
                ->where('event_id', $event['id'])
                ->orderBy('sort_order', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $e) {
            $weddingPartyMembers = [];
        }

        try {
            $faqModel = new EventFaqItemModel();
            $faqs = $faqModel->where('event_id', $event['id'])
                ->where('is_visible', 1)
                ->orderBy('sort_order', 'ASC')
                ->findAll();
        } catch (\Throwable $e) {
            $faqs = [];
        }

        try {
            $scheduleModel = new EventScheduleItemModel();
            $scheduleItems = $scheduleModel->where('event_id', $event['id'])
                ->where('is_visible', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('starts_at', 'ASC')
                ->findAll();
        } catch (\Throwable $e) {
            $scheduleItems = [];
        }

        /**
         * 7) Media assets por categoría (hero, bride, groom, etc.)
         */
        $mediaByCategory = [];
        try {
            $allMedia = $db->table('media_assets')
                ->where('event_id', $event['id'])
                ->where('is_private', 0)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($allMedia as $m) {
                $cat = $m['category'] ?? 'other';
                $mediaByCategory[$cat][] = $m;
            }
        } catch (\Throwable $e) {
            $mediaByCategory = [];
        }

        /**
         * 8) Template meta/defaults
         */
        $templateMeta = [];
        if (!empty($template['meta_json'])) {
            $templateMeta = json_decode($template['meta_json'], true) ?: [];
        }

        // Parsear venue_config
        $venueConfig = [];
        if (!empty($event['venue_config'])) {
            $venueConfig = json_decode($event['venue_config'], true) ?: [];
        }

        // Preparar datos para la vista
        $data = [
            'event'           => $event,
            'modules'         => $modules,
            'template'        => $template,
            'templateMeta'    => $templateMeta,
            'theme'           => json_decode($event['theme_config'] ?? '{}', true),
            'venueConfig'     => $venueConfig,
            'mediaByCategory' => $mediaByCategory,

            'galleryAssets'   => $galleryAssets,
            'registryItems'   => $registryItems,
            'registryStats'   => $registryStats,
            'guestGroups'     => $guestGroups,
            'guests'          => $guests,
            'rsvpResponses'   => $rsvpResponses,
            'menuOptions'     => $menuOptions,
            'weddingParty'    => $weddingPartyMembers,
            'faqs'            => $faqs,
            'scheduleItems'   => $scheduleItems,
        ];

        // 7) Cargar la vista basada en el CÓDIGO del template
        $viewPath = 'templates/' . $template['code'] . '/index';

        if (!is_file(APPPATH . 'Views/' . $viewPath . '.php')) {
            throw PageNotFoundException::forPageNotFound("El archivo del template '$viewPath' no existe.");
        }

        return view($viewPath, $data);
    }

    /**
     * RSVP público (cuando access_mode = open).
     * - guest_groups / guests / rsvp_responses (según tu esquema actual)
     */
    public function submitRsvp(string $slug)
    {
        $resp = ['success' => false, 'message' => 'Solicitud inválida.', 'data' => null];

        // Acepta AJAX o POST normal, pero siempre responde JSON
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON($resp);
        }

        $eventModel = new EventModel();
        $event = $eventModel->where('slug', $slug)->first();

        if (!$event) {
            $resp['message'] = 'Evento no encontrado.';
            return $this->response->setJSON($resp);
        }

        // Reglas mínimas de disponibilidad (ajusta si quieres permitir draft)
        if (($event['service_status'] ?? '') !== 'active') {
            $resp['message'] = 'Evento no disponible.';
            return $this->response->setJSON($resp);
        }

        // Si el evento NO es open, no aceptamos RSVP público
        if (($event['access_mode'] ?? 'open') !== 'open') {
            $resp['message'] = 'Este evento requiere código de invitación.';
            return $this->response->setJSON($resp);
        }

        $name      = trim((string) $this->request->getPost('name'));
        $email     = trim((string) $this->request->getPost('email'));
        $phone     = trim((string) $this->request->getPost('phone'));
        $attending = (string) $this->request->getPost('attending'); // accepted|declined
        $message   = trim((string) $this->request->getPost('message'));
        $song      = trim((string) $this->request->getPost('song_request'));

        if ($name === '' || !in_array($attending, ['accepted', 'declined'], true)) {
            $resp['message'] = 'Completa tu nombre y selecciona si asistirás.';
            return $this->response->setJSON($resp);
        }

        // Separar nombre en first/last (simple)
        $parts = preg_split('/\s+/', $name, 2);
        $first = $parts[0] ?? $name;
        $last  = $parts[1] ?? '';

        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $db->transStart();

        try {
            // 1) Crear grupo
            $accessCode = substr(bin2hex(random_bytes(8)), 0, 12);

            $db->table('guest_groups')->insert([
                'event_id'              => $event['id'],
                'group_name'            => $name,
                'access_code'           => $accessCode,
                'max_additional_guests' => 0,
                'is_vip'                => 0,
                'current_status'        => 'responded',
                'responded_at'          => $now,
                'invited_at'            => null,
                'first_viewed_at'       => $now,
                'last_viewed_at'        => $now,
                // created_at tiene default
            ]);

            // Como el PK es UUID por trigger, recuperamos por uq (event_id, access_code)
            $group = $db->table('guest_groups')
                ->select('id')
                ->where('event_id', $event['id'])
                ->where('access_code', $accessCode)
                ->get()
                ->getRowArray();

            if (!$group || empty($group['id'])) {
                throw new \RuntimeException('No se pudo crear el grupo.');
            }

            $groupId = $group['id'];

            // 2) Crear guest primario
            $db->table('guests')->insert([
                'group_id'           => $groupId,
                'first_name'         => $first,
                'last_name'          => $last,
                'email'              => ($email !== '' ? $email : null),
                'phone_number'       => ($phone !== '' ? $phone : null),
                'is_child'           => 0,
                'is_primary_contact' => 1,
                'rsvp_status'        => $attending, // enum pending/accepted/declined (en guests)
                // created_at default, updated_at auto on update
            ]);

            // Obtener guest_id (UUID trigger) por query del último insert no aplica con UUID trigger
            // Recuperamos por (group_id, first_name, last_name, email) más reciente:
            $guest = $db->table('guests')
                ->select('id')
                ->where('group_id', $groupId)
                ->orderBy('created_at', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if (!$guest || empty($guest['id'])) {
                throw new \RuntimeException('No se pudo crear el invitado.');
            }

            $guestId = $guest['id'];

            // 3) Registrar respuesta RSVP en rsvp_responses (tu esquema)
            $db->table('rsvp_responses')->insert([
                'guest_id'                 => $guestId,
                'attending_status'         => $attending, // enum pending/accepted/declined
                'meal_option_id'           => null,
                'dietary_restrictions'     => null,
                'transportation_requested' => 0,
                'song_request'             => ($song !== '' ? $song : null),
                'message_to_couple'        => ($message !== '' ? $message : null),
                'responded_at'             => $now,
                'response_method'          => 'public',
                // created_at default
            ]);

            $db->transComplete();

            $resp['success'] = true;
            $resp['message'] = 'Confirmación registrada. ¡Gracias!';
            $resp['data'] = [
                'group_id'  => $groupId,
                'guest_id'  => $guestId,
                'status'    => $attending,
            ];

            return $this->response->setJSON($resp);
        } catch (\Throwable $e) {
            $db->transRollback();
            $resp['message'] = 'No fue posible registrar tu confirmación.';
            return $this->response->setJSON($resp);
        }
    }
}

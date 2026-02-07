<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventModel;
use App\Models\MediaAssetModel;

class Gallery extends BaseController
{
    protected $eventModel;
    protected $mediaModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->mediaModel = new MediaAssetModel();
    }

    /**
     * Galería del evento (soporta ?category= para hero, bride, groom, story, event, etc.)
     */
    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        // Categoría activa (default: gallery)
        $category = $this->request->getGet('category') ?? 'gallery';
        $allowedCategories = [
            'gallery'      => 'Galería',
            'hero'         => 'Portada (Hero)',
            'bride'        => 'Foto Novia',
            'groom'        => 'Foto Novio',
            'story'        => 'Historia (Story)',
            'event'        => 'Lugar del Evento',
            'countdown_bg' => 'Fondo Countdown',
            'cta_bg'       => 'Fondo CTA',
            'rsvp_bg'      => 'Fondo RSVP',
        ];

        if (!array_key_exists($category, $allowedCategories)) {
            $category = 'gallery';
        }

        $images = $this->mediaModel
            ->where('event_id', $eventId)
            ->where('category', $category)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Contar assets por categoría para badges
        $categoryCounts = [];
        try {
            $countsRaw = \Config\Database::connect()
                ->table('media_assets')
                ->select('category, COUNT(*) as total')
                ->where('event_id', $eventId)
                ->where('is_private', 0)
                ->groupBy('category')
                ->get()
                ->getResultArray();
            foreach ($countsRaw as $row) {
                $categoryCounts[$row['category']] = (int) $row['total'];
            }
        } catch (\Throwable $e) {
            $categoryCounts = [];
        }

        $stats = $this->eventModel->getEventStats($eventId);

        return view('admin/gallery/index', [
            'pageTitle' => $allowedCategories[$category] . ': ' . $event['couple_title'],
            'event' => $event,
            'images' => $images,
            'stats' => $stats,
            'currentCategory' => $category,
            'allowedCategories' => $allowedCategories,
            'categoryCounts' => $categoryCounts,
        ]);
    }

    /**
     * Subir imágenes
     */
    public function upload(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $files = $this->request->getFiles();
        
        if (empty($files['images'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se seleccionaron archivos.']);
        }

        $category = $this->request->getPost('category') ?? 'gallery';
        $allowedCategories = [
            'gallery',
            'hero',
            'bride',
            'groom',
            'story',
            'event',
            'countdown_bg',
            'cta_bg',
            'rsvp_bg',
        ];

        if (!in_array($category, $allowedCategories, true)) {
            $category = 'gallery';
        }

        $subfolder = match ($category) {
            'bride', 'groom' => 'couple',
            'countdown_bg', 'cta_bg', 'rsvp_bg' => 'backgrounds',
            default => $category,
        };

        $uploadPath = FCPATH . 'uploads/events/' . $eventId . '/' . $subfolder . '/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $uploaded = [];
        $errors = [];

        foreach ($files['images'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                
                if (!in_array($file->getMimeType(), $allowedTypes)) {
                    $errors[] = $file->getClientName() . ': Tipo de archivo no permitido';
                    continue;
                }

                if ($file->getSize() > 10 * 1024 * 1024) {
                    $errors[] = $file->getClientName() . ': El archivo excede 10MB';
                    continue;
                }

                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);

                $mediaId = $this->mediaModel->createMedia([
                    'event_id' => $eventId,
                    'category' => $category,
                    'file_url_original' => 'uploads/events/' . $eventId . '/' . $subfolder . '/' . $newName,
                    'alt_text' => pathinfo($file->getClientName(), PATHINFO_FILENAME),
                    'sort_order' => 999
                ]);

                if ($mediaId) {
                    $uploaded[] = [
                        'id' => $mediaId,
                        'url' => base_url('uploads/events/' . $eventId . '/' . $subfolder . '/' . $newName),
                        'name' => $file->getClientName()
                    ];
                }
            }
        }

        $uploadedCount = count($uploaded);
        $message = $uploadedCount . ' imagen(es) subida(s) correctamente';
        if (!empty($errors)) {
            $message .= '. ' . count($errors) . ' archivo(s) con error.';
        }

        return $this->response->setJSON([
            'success' => $uploadedCount > 0,
            'uploaded' => $uploaded,
            'errors' => $errors,
            'message' => $message,
        ]);
    }

    /**
     * Actualizar imagen
     */
    public function update(string $eventId, string $mediaId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $media = $this->mediaModel->find($mediaId);
        if (!$media || $media['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Imagen no encontrada.']);
        }

        $this->mediaModel->update($mediaId, [
            'alt_text' => $this->request->getPost('alt_text'),
            'caption' => $this->request->getPost('caption'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Imagen actualizada correctamente.'
        ]);
    }

    /**
     * Eliminar imagen
     */
    public function delete(string $eventId, string $mediaId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $media = $this->mediaModel->find($mediaId);
        if (!$media || $media['event_id'] !== $eventId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Imagen no encontrada.']);
        }

        // Eliminar archivo físico
        $filePath = FCPATH . $media['file_url_original'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->mediaModel->delete($mediaId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Imagen eliminada correctamente.'
        ]);
    }

    /**
     * Reordenar imágenes
     */
    public function reorder(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $order = $this->request->getPost('order');
        
        if (is_array($order)) {
            foreach ($order as $index => $mediaId) {
                $this->mediaModel->update($mediaId, ['sort_order' => $index]);
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Orden actualizado correctamente.'
        ]);
    }

    /**
     * Verificar acceso al evento
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
}

<?php

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
     * Galería del evento
     */
    public function index(string $eventId)
    {
        $event = $this->eventModel->find($eventId);
        
        if (!$event || !$this->canAccessEvent($eventId)) {
            return redirect()->to(base_url('admin/events'))
                ->with('error', 'Evento no encontrado.');
        }

        $images = $this->mediaModel
            ->where('event_id', $eventId)
            ->where('category', 'gallery')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $stats = $this->eventModel->getEventStats($eventId);

        return view('admin/gallery/index', [
            'pageTitle' => 'Galería: ' . $event['couple_title'],
            'event' => $event,
            'images' => $images,
            'stats' => $stats
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

        $uploadPath = FCPATH . 'uploads/events/' . $eventId . '/gallery/';
        
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

                if ($file->getSize() > 5 * 1024 * 1024) {
                    $errors[] = $file->getClientName() . ': El archivo excede 5MB';
                    continue;
                }

                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);

                $mediaId = $this->mediaModel->createMedia([
                    'event_id' => $eventId,
                    'category' => 'gallery',
                    'file_url_original' => 'uploads/events/' . $eventId . '/gallery/' . $newName,
                    'alt_text' => pathinfo($file->getClientName(), PATHINFO_FILENAME),
                    'sort_order' => 999
                ]);

                if ($mediaId) {
                    $uploaded[] = [
                        'id' => $mediaId,
                        'url' => base_url('uploads/events/' . $eventId . '/gallery/' . $newName),
                        'name' => $file->getClientName()
                    ];
                }
            }
        }

        return $this->response->setJSON([
            'success' => count($uploaded) > 0,
            'uploaded' => $uploaded,
            'errors' => $errors,
            'message' => count($uploaded) . ' imagen(es) subida(s) correctamente'
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

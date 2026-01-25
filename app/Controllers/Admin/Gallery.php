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

        $images = $this->mediaModel->getByEvent($eventId);
        $categories = $this->mediaModel->getCategories($eventId);

        $data = [
            'pageTitle' => 'Galería: ' . $event['couple_title'],
            'event' => $event,
            'images' => $images,
            'categories' => $categories
        ];

        return view('admin/gallery/index', $data);
    }

    /**
     * Subir imagen(es)
     */
    public function upload(string $eventId)
    {
        if (!$this->canAccessEvent($eventId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin acceso.']);
        }

        $files = $this->request->getFiles();
        
        if (empty($files['images'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se recibieron archivos.']);
        }

        $uploadPath = FCPATH . 'uploads/events/' . $eventId . '/gallery/';
        
        // Crear directorio si no existe
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $uploaded = [];
        $errors = [];

        foreach ($files['images'] as $file) {
            if (!$file->isValid()) {
                $errors[] = $file->getName() . ': Archivo inválido';
                continue;
            }

            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file->getMimeType(), $allowedTypes)) {
                $errors[] = $file->getName() . ': Tipo de archivo no permitido';
                continue;
            }

            // Validar tamaño (max 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                $errors[] = $file->getName() . ': El archivo excede 10MB';
                continue;
            }

            // Generar nombre único
            $newName = $file->getRandomName();
            
            // Mover archivo
            if ($file->move($uploadPath, $newName)) {
                $originalPath = $uploadPath . $newName;
                
                // Crear thumbnail
                $thumbnailName = 'thumb_' . $newName;
                $this->createThumbnail($originalPath, $uploadPath . $thumbnailName, 300, 300);
                
                // Crear versión grande
                $largeName = 'large_' . $newName;
                $this->createThumbnail($originalPath, $uploadPath . $largeName, 1200, 1200);

                // Obtener dimensiones
                $imageInfo = getimagesize($originalPath);
                $aspectRatio = $imageInfo ? round($imageInfo[0] / $imageInfo[1], 2) : 1;

                // Guardar en base de datos
                $mediaData = [
                    'event_id' => $eventId,
                    'file_url_original' => 'uploads/events/' . $eventId . '/gallery/' . $newName,
                    'file_url_thumbnail' => 'uploads/events/' . $eventId . '/gallery/' . $thumbnailName,
                    'file_url_large' => 'uploads/events/' . $eventId . '/gallery/' . $largeName,
                    'mime_type' => $file->getMimeType(),
                    'file_size_bytes' => $file->getSize(),
                    'aspect_ratio' => $aspectRatio,
                    'original_filename' => $file->getName(),
                    'category_tag' => $this->request->getPost('category') ?: 'general',
                    'sort_order' => $this->mediaModel->where('event_id', $eventId)->countAllResults() + 1
                ];

                $mediaId = $this->mediaModel->createAsset($mediaData);
                
                if ($mediaId) {
                    $uploaded[] = [
                        'id' => $mediaId,
                        'thumbnail' => base_url($mediaData['file_url_thumbnail']),
                        'original' => base_url($mediaData['file_url_original']),
                        'name' => $file->getName()
                    ];
                }
            } else {
                $errors[] = $file->getName() . ': Error al guardar';
            }
        }

        return $this->response->setJSON([
            'success' => count($uploaded) > 0,
            'message' => count($uploaded) . ' imagen(es) subida(s)' . (count($errors) > 0 ? ', ' . count($errors) . ' error(es)' : ''),
            'uploaded' => $uploaded,
            'errors' => $errors
        ]);
    }

    /**
     * Actualizar imagen (alt_text, category)
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

        $updateData = [
            'alt_text' => $this->request->getPost('alt_text'),
            'category_tag' => $this->request->getPost('category_tag'),
        ];

        $this->mediaModel->update($mediaId, $updateData);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Imagen actualizada.'
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

        // Eliminar archivos físicos
        $basePath = FCPATH;
        if ($media['file_url_original'] && file_exists($basePath . $media['file_url_original'])) {
            unlink($basePath . $media['file_url_original']);
        }
        if ($media['file_url_thumbnail'] && file_exists($basePath . $media['file_url_thumbnail'])) {
            unlink($basePath . $media['file_url_thumbnail']);
        }
        if ($media['file_url_large'] && file_exists($basePath . $media['file_url_large'])) {
            unlink($basePath . $media['file_url_large']);
        }

        // Eliminar de base de datos
        $this->mediaModel->delete($mediaId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Imagen eliminada.'
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
        
        if (!is_array($order)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos inválidos.']);
        }

        foreach ($order as $position => $mediaId) {
            $this->mediaModel->update($mediaId, ['sort_order' => $position + 1]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Orden actualizado.'
        ]);
    }

    /**
     * Crear thumbnail de imagen
     */
    protected function createThumbnail(string $source, string $destination, int $maxWidth, int $maxHeight): bool
    {
        $imageInfo = getimagesize($source);
        if (!$imageInfo) {
            return false;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mime = $imageInfo['mime'];

        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        if ($ratio >= 1) {
            // La imagen ya es más pequeña, solo copiar
            copy($source, $destination);
            return true;
        }

        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Crear imagen desde el original
        switch ($mime) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($source);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }

        if (!$sourceImage) {
            return false;
        }

        // Crear nueva imagen
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia para PNG y GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Redimensionar
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Guardar
        $result = false;
        switch ($mime) {
            case 'image/jpeg':
                $result = imagejpeg($newImage, $destination, 85);
                break;
            case 'image/png':
                $result = imagepng($newImage, $destination, 8);
                break;
            case 'image/gif':
                $result = imagegif($newImage, $destination);
                break;
            case 'image/webp':
                $result = imagewebp($newImage, $destination, 85);
                break;
        }

        // Liberar memoria
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return $result;
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

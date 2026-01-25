<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentModuleModel extends Model
{
    protected $table            = 'content_modules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'event_id',
        'module_type',
        'css_id',
        'sort_order',
        'is_enabled',
        'content_payload',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Tipos de módulos disponibles
    const MODULE_TYPES = [
        'couple_info'    => 'Información de la Pareja',
        'timeline'       => 'Línea de Tiempo',
        'gallery'        => 'Galería de Fotos',
        'venue'          => 'Lugar del Evento',
        'countdown'      => 'Cuenta Regresiva',
        'rsvp'           => 'Formulario RSVP',
        'wedding_party'  => 'Cortejo Nupcial',
        'registry'       => 'Lista de Regalos',
        'schedule'       => 'Itinerario del Evento',
        'accommodation'  => 'Alojamiento',
        'faq'            => 'Preguntas Frecuentes',
        'music'          => 'Música de Fondo',
        'custom_html'    => 'Contenido Personalizado'
    ];

    /**
     * Obtener módulos de un evento ordenados
     */
    public function getByEvent(string $eventId, bool $onlyEnabled = false): array
    {
        $builder = $this->where('event_id', $eventId);
        
        if ($onlyEnabled) {
            $builder->where('is_enabled', 1);
        }
        
        return $builder->orderBy('sort_order', 'ASC')->findAll();
    }

    /**
     * Crear módulo
     */
    public function createModule(array $data): ?string
    {
        $moduleId = UserModel::generateUUID();
        $data['id'] = $moduleId;
        $data['is_enabled'] = $data['is_enabled'] ?? 1;
        
        // Obtener siguiente orden
        if (!isset($data['sort_order'])) {
            $maxOrder = $this->where('event_id', $data['event_id'])
                ->selectMax('sort_order')
                ->first();
            $data['sort_order'] = ($maxOrder['sort_order'] ?? 0) + 1;
        }

        // Convertir content_payload a JSON si es array
        if (isset($data['content_payload']) && is_array($data['content_payload'])) {
            $data['content_payload'] = json_encode($data['content_payload']);
        }

        if ($this->insert($data)) {
            return $moduleId;
        }
        
        return null;
    }

    /**
     * Actualizar orden de módulos
     */
    public function updateOrder(string $eventId, array $moduleIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($moduleIds as $index => $moduleId) {
            $this->update($moduleId, ['sort_order' => $index + 1]);
        }

        $db->transComplete();
        return $db->transStatus();
    }

    /**
     * Obtener módulo con payload decodificado
     */
    public function getWithDecodedPayload(string $moduleId): ?array
    {
        $module = $this->find($moduleId);
        if ($module && $module['content_payload']) {
            $module['content_payload'] = json_decode($module['content_payload'], true);
        }
        return $module;
    }

    /**
     * Actualizar payload del módulo
     */
    public function updatePayload(string $moduleId, array $payload): bool
    {
        return $this->update($moduleId, [
            'content_payload' => json_encode($payload)
        ]);
    }

    /**
     * Crear módulos por defecto para un evento
     */
    public function createDefaultModules(string $eventId): void
    {
        $defaults = [
            ['module_type' => 'couple_info', 'css_id' => 'pareja', 'content_payload' => json_encode([])],
            ['module_type' => 'countdown', 'css_id' => 'cuenta-regresiva', 'content_payload' => json_encode([])],
            ['module_type' => 'venue', 'css_id' => 'lugar', 'content_payload' => json_encode([])],
            ['module_type' => 'timeline', 'css_id' => 'nuestra-historia', 'content_payload' => json_encode(['events' => []])],
            ['module_type' => 'gallery', 'css_id' => 'galeria', 'content_payload' => json_encode(['images' => []])],
            ['module_type' => 'rsvp', 'css_id' => 'confirmacion', 'content_payload' => json_encode([])],
        ];

        foreach ($defaults as $index => $module) {
            $module['event_id'] = $eventId;
            $module['sort_order'] = $index + 1;
            $module['is_enabled'] = 1;
            $this->createModule($module);
        }
    }
}

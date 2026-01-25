<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateModel extends Model
{
    protected $table            = 'templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'code',
        'name',
        'description',
        'preview_url',
        'thumbnail_url',
        'is_public',
        'schema_json',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtener templates públicos
     */
    public function getPublic(): array
    {
        return $this->where('is_public', 1)->findAll();
    }

    /**
     * Obtener template por código
     */
    public function findByCode(string $code): ?array
    {
        return $this->where('code', $code)->first();
    }

    /**
     * Asignar template a evento
     */
    public function assignToEvent(int $templateId, string $eventId): bool
    {
        $db = \Config\Database::connect();
        
        // Desactivar templates anteriores
        $db->table('event_templates')
            ->where('event_id', $eventId)
            ->update(['is_active' => 0]);

        // Verificar si ya existe la relación
        $existing = $db->table('event_templates')
            ->where('event_id', $eventId)
            ->where('template_id', $templateId)
            ->get()
            ->getRow();

        if ($existing) {
            return $db->table('event_templates')
                ->where('event_id', $eventId)
                ->where('template_id', $templateId)
                ->update([
                    'is_active' => 1,
                    'applied_at' => date('Y-m-d H:i:s')
                ]);
        }

        return $db->table('event_templates')->insert([
            'event_id' => $eventId,
            'template_id' => $templateId,
            'is_active' => 1,
            'applied_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Obtener template activo de un evento
     */
    public function getActiveForEvent(string $eventId): ?array
    {
        $db = \Config\Database::connect();
        
        return $db->table('event_templates')
            ->select('templates.*')
            ->join('templates', 'templates.id = event_templates.template_id')
            ->where('event_templates.event_id', $eventId)
            ->where('event_templates.is_active', 1)
            ->get()
            ->getRowArray();
    }
}

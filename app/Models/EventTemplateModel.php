<?php

namespace App\Models;

use CodeIgniter\Model;

class EventTemplateModel extends Model
{
    protected $table            = 'event_templates';
    protected $primaryKey       = 'event_id'; // CI no soporta PK compuesta nativa; usaremos queries por event_id/template_id
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'event_id',
        'template_id',
        'is_active',
        'applied_at',
    ];

    /**
     * Devuelve el template_id activo del evento (o null si no hay).
     */
    public function getActiveTemplateId(string $eventId): ?int
    {
        $row = $this->where('event_id', $eventId)
            ->where('is_active', 1)
            ->orderBy('applied_at', 'DESC')
            ->first();

        return $row ? (int)$row['template_id'] : null;
    }

    /**
     * Marca como activa la plantilla (desactiva las demás del evento).
     * - Si ya existe la fila (event_id, template_id), la actualiza.
     * - Si no existe, la inserta.
     */
    public function setActiveTemplate(string $eventId, int $templateId): bool
    {
        $db = $this->db;
        $db->transStart();

        // 1) Desactivar todas las plantillas del evento
        $db->table($this->table)
            ->where('event_id', $eventId)
            ->update(['is_active' => 0]);

        // 2) Upsert por PK compuesta (event_id + template_id)
        $exists = $db->table($this->table)
            ->where('event_id', $eventId)
            ->where('template_id', $templateId)
            ->countAllResults() > 0;

        $payload = [
            'event_id'    => $eventId,
            'template_id' => $templateId,
            'is_active'   => 1,
            'applied_at'  => date('Y-m-d H:i:s'),
        ];

        if ($exists) {
            $db->table($this->table)
                ->where('event_id', $eventId)
                ->where('template_id', $templateId)
                ->update($payload);
        } else {
            $db->table($this->table)->insert($payload);
        }

        $db->transComplete();
        return $db->transStatus();
    }
}

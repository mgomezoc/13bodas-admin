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
        'is_active',
        'sort_order',
        'schema_json',
        'meta_json',
        // Nota: NO incluimos created_at/updated_at para evitar seteos por POST.
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'code' => 'required|max_length[80]|is_unique[templates.code,id,{id}]',
        'name' => 'required|max_length[120]',
    ];

    protected $validationMessages = [
        'code' => [
            'is_unique' => 'Este código ya está en uso.',
            'required'  => 'El código es obligatorio.',
        ],
        'name' => [
            'required' => 'El nombre es obligatorio.',
        ],
    ];

    protected $skipValidation = false;

    /**
     * Indica si un template tiene relaciones en event_templates.
     * OJO: cuenta histórico, no solo el activo.
     */
    public function isTemplateInUse(int $templateId): array
    {
        $db = $this->db ?? \Config\Database::connect();

        $count = $db->table('event_templates')
            ->where('template_id', $templateId)
            ->countAllResults();

        return [
            'in_use' => $count > 0,
            'count'  => $count,
        ];
    }

    /**
     * Lista templates con conteo de uso:
     * - usage_count: eventos donde ES ACTIVO actualmente
     * - usage_count_total: eventos donde existe en historial
     */
    public function listWithUsageCount(array $filters = []): array
    {
        // MySQL: COUNT(DISTINCT IF(cond, value, NULL))
        $builder = $this->select([
            'templates.*',
            'COUNT(DISTINCT IF(event_templates.is_active = 1, event_templates.event_id, NULL)) AS usage_count',
            'COUNT(DISTINCT event_templates.event_id) AS usage_count_total',
        ])
            ->join('event_templates', 'event_templates.template_id = templates.id', 'left')
            ->groupBy('templates.id');

        if (!empty($filters['search'])) {
            $s = trim((string) $filters['search']);
            $builder->groupStart()
                ->like('templates.name', $s)
                ->orLike('templates.code', $s)
                ->orLike('templates.description', $s)
                ->groupEnd();
        }

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $builder->where('templates.is_active', (int) $filters['is_active']);
        }

        if (array_key_exists('is_public', $filters) && $filters['is_public'] !== '' && $filters['is_public'] !== null) {
            $builder->where('templates.is_public', (int) $filters['is_public']);
        }

        return $builder->orderBy('templates.sort_order', 'ASC')
            ->orderBy('templates.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Obtiene el template activo para un evento usando event_templates.
     * Acepta:
     *  - string $eventId (UUID)
     *  - array $event (con clave 'id')
     */
    public function getActiveForEvent($event): ?array
    {
        $eventId = is_array($event) ? ($event['id'] ?? null) : $event;

        if (!is_string($eventId) || trim($eventId) === '') {
            return null;
        }

        $db = $this->db ?? \Config\Database::connect();

        // 1) Template marcado como activo para el evento
        $tpl = $db->table('event_templates et')
            ->select('t.*')
            ->join('templates t', 't.id = et.template_id', 'inner')
            ->where('et.event_id', $eventId)
            ->where('et.is_active', 1)
            ->orderBy('et.applied_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($tpl) {
            return $tpl;
        }

        // 2) Fallback: último aplicado (si por data legacy no hay is_active=1)
        $tpl = $db->table('event_templates et')
            ->select('t.*')
            ->join('templates t', 't.id = et.template_id', 'inner')
            ->where('et.event_id', $eventId)
            ->orderBy('et.applied_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($tpl) {
            return $tpl;
        }

        // 3) Fallback final: primer template activo del catálogo
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();
    }

    /**
     * Deja un solo template activo por evento (manteniendo historial).
     * - pone is_active=0 a todos los del evento
     * - hace upsert (event_id, template_id) con is_active=1 y applied_at=NOW()
     */
    public function setActiveForEvent(string $eventId, int $templateId): bool
    {
        $eventId = trim($eventId);
        if ($eventId === '' || $templateId <= 0) {
            return false;
        }

        $db = $this->db ?? \Config\Database::connect();

        $db->transStart();

        // 1) apagar todos
        $db->table('event_templates')
            ->where('event_id', $eventId)
            ->set(['is_active' => 0])
            ->update();

        // 2) upsert del seleccionado
        $exists = $db->table('event_templates')
            ->where('event_id', $eventId)
            ->where('template_id', $templateId)
            ->countAllResults() > 0;

        $now = date('Y-m-d H:i:s');

        if ($exists) {
            $db->table('event_templates')
                ->where('event_id', $eventId)
                ->where('template_id', $templateId)
                ->set([
                    'is_active'   => 1,
                    'applied_at'  => $now,
                ])
                ->update();
        } else {
            $db->table('event_templates')->insert([
                'event_id'    => $eventId,
                'template_id' => $templateId,
                'is_active'   => 1,
                'applied_at'  => $now,
            ]);
        }

        $db->transComplete();

        return $db->transStatus();
    }

    public function getByCode(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        return $this->where('code', $code)->first();
    }
}

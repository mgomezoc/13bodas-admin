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
        // Nota: si NO quieres que se seteen por POST, podrías quitarlos,
        // pero como ya los tenías, los dejo para no romper compatibilidad.
        'created_at',
        'updated_at',
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
        $db = \Config\Database::connect();
        $count = $db->table('event_templates')
            ->where('template_id', $templateId)
            ->countAllResults();

        return [
            'in_use' => $count > 0,
            'count'  => (int) $count,
        ];
    }

    /**
     * Lista templates con conteo de uso:
     * - usage_count: eventos donde ES ACTIVO actualmente (event_templates.is_active = 1)
     * - usage_count_total: eventos donde existe en historial (cualquier registro en event_templates)
     *
     * Filtros:
     * - search (code/name/description)
     * - is_active (0|1)
     * - is_public (0|1)
     */
    public function listWithUsageCount(array $filters = []): array
    {
        // MySQL/MariaDB: COUNT(DISTINCT IF(cond, value, NULL))
        $builder = $this->select([
            'templates.*',
            'COUNT(DISTINCT IF(event_templates.is_active = 1, event_templates.event_id, NULL)) AS usage_count',
            'COUNT(DISTINCT event_templates.event_id) AS usage_count_total',
        ])
            ->join('event_templates', 'event_templates.template_id = templates.id', 'left')
            ->groupBy('templates.id');

        // Search
        if (!empty($filters['search'])) {
            $s = trim((string) $filters['search']);
            if ($s !== '') {
                $builder->groupStart()
                    ->like('templates.name', $s)
                    ->orLike('templates.code', $s)
                    ->orLike('templates.description', $s)
                    ->groupEnd();
            }
        }

        // is_active
        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $builder->where('templates.is_active', (int) $filters['is_active']);
        }

        // is_public
        if (array_key_exists('is_public', $filters) && $filters['is_public'] !== '' && $filters['is_public'] !== null) {
            $builder->where('templates.is_public', (int) $filters['is_public']);
        }

        return $builder->orderBy('templates.sort_order', 'ASC')
            ->orderBy('templates.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Devuelve el template activo para un evento (o null si no hay).
     */
    public function getActiveForEvent(string $eventId): ?array
    {
        return $this->select('templates.*')
            ->join('event_templates', 'event_templates.template_id = templates.id', 'inner')
            ->where('event_templates.event_id', $eventId)
            ->where('event_templates.is_active', 1)
            ->orderBy('event_templates.applied_at', 'DESC')
            ->first();
    }
}

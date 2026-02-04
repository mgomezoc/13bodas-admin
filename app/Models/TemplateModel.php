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
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'code' => 'required|max_length[50]|is_unique[templates.code,id,{id}]',
        'name' => 'required|max_length[100]',
    ];

    protected $validationMessages = [
        'code' => [
            'is_unique' => 'Este código ya está en uso.',
            'required'  => 'El código es obligatorio.'
        ],
        'name' => [
            'required' => 'El nombre es obligatorio.'
        ]
    ];

    protected $skipValidation = false;

    public function isTemplateInUse(int $templateId): array
    {
        $db = \Config\Database::connect();
        $count = $db->table('event_templates')
            ->where('template_id', $templateId)
            ->countAllResults();

        return [
            'in_use' => $count > 0,
            'count'  => $count
        ];
    }

    public function listWithUsageCount(array $filters = []): array
    {
        $builder = $this->select('templates.*, COUNT(event_templates.event_id) as usage_count')
            ->join('event_templates', 'event_templates.template_id = templates.id', 'left')
            ->groupBy('templates.id');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('templates.name', $filters['search'])
                ->orLike('templates.code', $filters['search'])
                ->orLike('templates.description', $filters['search'])
            ->groupEnd();
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $builder->where('templates.is_active', $filters['is_active']);
        }

        if (isset($filters['is_public']) && $filters['is_public'] !== '') {
            $builder->where('templates.is_public', $filters['is_public']);
        }

        return $builder->orderBy('templates.sort_order', 'ASC')
                       ->orderBy('templates.created_at', 'DESC')
                       ->findAll();
    }
}

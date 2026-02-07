<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuOptionModel extends Model
{
    protected $table            = 'menu_options';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'event_id',
        'name',
        'description',
        'is_vegan',
        'is_gluten_free',
        'is_kid_friendly',
        'sort_order',
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Obtener opciones de un evento
     */
    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Crear opción de menú
     */
    public function createOption(array $data): ?string
    {
        $optionId = UserModel::generateUUID();
        $data['id'] = $optionId;
        $data['is_vegan'] = $data['is_vegan'] ?? 0;
        $data['is_gluten_free'] = $data['is_gluten_free'] ?? 0;
        $data['is_kid_friendly'] = $data['is_kid_friendly'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        // Obtener siguiente orden
        if (!isset($data['sort_order'])) {
            $maxOrder = $this->where('event_id', $data['event_id'])
                ->selectMax('sort_order')
                ->first();
            $data['sort_order'] = ($maxOrder['sort_order'] ?? 0) + 1;
        }

        $data = $this->filterExistingFields($data);

        if ($this->insert($data)) {
            return $optionId;
        }
        
        return null;
    }

    public function hasColumn(string $column): bool
    {
        $fields = $this->db->getFieldNames($this->table);
        return in_array($column, $fields, true);
    }

    protected function filterExistingFields(array $data): array
    {
        $fields = $this->db->getFieldNames($this->table);
        return array_intersect_key($data, array_flip($fields));
    }
}

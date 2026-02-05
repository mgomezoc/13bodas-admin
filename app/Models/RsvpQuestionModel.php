<?php

namespace App\Models;

use CodeIgniter\Model;

class RsvpQuestionModel extends Model
{
    protected $table            = 'rsvp_questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'event_id',
        'code',
        'label',
        'type',
        'options_json',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $useTimestamps = false;

    public function getByEvent(string $eventId): array
    {
        return $this->where('event_id', $eventId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    public function createQuestion(array $data): ?string
    {
        $questionId = UserModel::generateUUID();
        $data['id'] = $questionId;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_required'] = $data['is_required'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? 1;

        if ($this->insert($data)) {
            return $questionId;
        }

        return null;
    }
}

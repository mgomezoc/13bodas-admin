<?php

namespace App\Models;

use CodeIgniter\Model;

class RsvpAnswerModel extends Model
{
    protected $table            = 'rsvp_answers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id',
        'rsvp_response_id',
        'question_id',
        'value_text',
        'value_json',
    ];

    protected $useTimestamps = false;
}

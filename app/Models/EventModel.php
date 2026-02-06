<?php

namespace App\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table            = 'events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $protectFields = true;

    // NOTA: NO incluir created_at/updated_at para evitar que se puedan setear por POST.
    protected $allowedFields = [
        'id',
        'client_id',
        'slug',
        'couple_title',
        'bride_name',
        'groom_name',
        'primary_contact_email',
        'time_zone',
        'event_date_start',
        'event_date_end',
        'rsvp_deadline',
        'site_mode',
        'visibility',
        'access_mode',
        'venue_name',
        'venue_address',
        'venue_geo_lat',
        'venue_geo_lng',
        'venue_config',
        'theme_config',
        'is_demo',
        'service_status',
        'is_paid',
        'paid_until',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Validación del modelo:
     * - Mantener SOLO reglas estructurales.
     * - is_unique se valida en Controller (insert/update) porque depende del contexto (id).
     */
    protected $validationRules = [
        'id'           => 'permit_empty',
        'slug'         => 'required|alpha_dash|min_length[3]|max_length[100]',
        'couple_title' => 'required|min_length[3]|max_length[255]',
        'time_zone'    => 'required|max_length[50]',
    ];

    protected $validationMessages = [
        'slug' => [
            'required'   => 'El slug es obligatorio.',
            'alpha_dash' => 'El slug solo puede contener letras, números, guiones y guiones bajos.',
            'min_length' => 'El slug debe tener al menos 3 caracteres.',
            'max_length' => 'El slug no puede exceder 100 caracteres.',
        ],
        'couple_title' => [
            'required'   => 'El título de la pareja es obligatorio.',
            'min_length' => 'El título debe tener al menos 3 caracteres.',
            'max_length' => 'El título no puede exceder 255 caracteres.',
        ],
        'time_zone' => [
            'required'   => 'La zona horaria es obligatoria.',
            'max_length' => 'La zona horaria no puede exceder 50 caracteres.',
        ],
    ];

    /**
     * Obtener evento por slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Obtener evento con datos del cliente
     */
    public function getWithClient(string $eventId): ?array
    {
        return $this->select('events.*, clients.company_name, users.email as client_email, users.full_name as client_name, users.phone as client_phone')
            ->join('clients', 'clients.id = events.client_id')
            ->join('users', 'users.id = clients.user_id')
            ->where('events.id', $eventId)
            ->first();
    }

    /**
     * Listar eventos con datos del cliente
     */
    public function listWithClients(array $filters = []): array
    {
        $builder = $this->select('events.*, users.full_name as client_name, users.email as client_email')
            ->join('clients', 'clients.id = events.client_id')
            ->join('users', 'users.id = clients.user_id');

        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('events.slug', $filters['search'])
                ->orLike('events.couple_title', $filters['search'])
                ->orLike('users.full_name', $filters['search'])
                ->orLike('users.email', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['service_status'])) {
            $builder->where('events.service_status', $filters['service_status']);
        }

        if (!empty($filters['client_id'])) {
            $builder->where('events.client_id', $filters['client_id']);
        }

        if (isset($filters['is_demo'])) {
            $builder->where('events.is_demo', $filters['is_demo']);
        }

        // Whitelist para evitar orderBy peligroso por GET
        $allowedSortFields = [
            'events.created_at',
            'events.updated_at',
            'events.couple_title',
            'events.slug',
            'events.service_status',
            'users.full_name',
            'users.email',
        ];

        $sortField = $filters['sort'] ?? 'events.created_at';
        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'events.created_at';
        }

        $sortOrder = strtoupper($filters['order'] ?? 'DESC');
        $sortOrder = in_array($sortOrder, ['ASC', 'DESC'], true) ? $sortOrder : 'DESC';

        return $builder->orderBy($sortField, $sortOrder)->findAll();
    }

    /**
     * Obtener eventos de un cliente
     */
    public function getByClientId(string $clientId): array
    {
        return $this->where('client_id', $clientId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Crear nuevo evento
     */
    public function createEvent(array $data): ?string
    {
        // Idealmente esto debería vivir en un helper/servicio UUID,
        // pero lo dejamos así por compatibilidad con tu código actual.
        $eventId    = UserModel::generateUUID();
        $data['id'] = $eventId;

        // Valores por defecto coherentes
        $data['site_mode']       = $data['site_mode']       ?? 'auto';
        $data['visibility']      = $data['visibility']      ?? 'private';
        $data['access_mode']     = $data['access_mode']     ?? 'open';
        $data['service_status']  = $data['service_status']  ?? 'draft';
        $data['is_demo']         = $data['is_demo']         ?? 0;
        $data['is_paid']         = $data['is_paid']         ?? 0;
        $data['time_zone']       = $data['time_zone']       ?? 'America/Mexico_City';

        if ($this->insert($data)) {
            return $eventId;
        }

        // Debug útil en desarrollo
        log_message('error', 'EventModel::createEvent insert failed. Errors: ' . json_encode($this->errors()));

        return null;
    }

    /**
     * Obtener estadísticas del evento
     */
    public function getEventStats(string $eventId): array
    {
        $db = \Config\Database::connect();

        $totalGuests = $db->table('guests')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->countAllResults();

        $confirmedGuests = $db->table('guests')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->where('guests.rsvp_status', 'accepted')
            ->countAllResults();

        $declinedGuests = $db->table('guests')
            ->join('guest_groups', 'guest_groups.id = guests.group_id')
            ->where('guest_groups.event_id', $eventId)
            ->where('guests.rsvp_status', 'declined')
            ->countAllResults();

        $totalGroups = $db->table('guest_groups')
            ->where('event_id', $eventId)
            ->countAllResults();

        return [
            'total_guests'  => $totalGuests,
            'confirmed'     => $confirmedGuests,
            'declined'      => $declinedGuests,
            'pending'       => $totalGuests - $confirmedGuests - $declinedGuests,
            'total_groups'  => $totalGroups,
        ];
    }

    /**
     * Verificar si el slug está disponible
     */
    public function isSlugAvailable(string $slug, ?string $excludeId = null): bool
    {
        $builder = $this->where('slug', $slug);

        if (!empty($excludeId)) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() === 0;
    }

    /**
     * Generar slug único a partir del título de la pareja
     */
    public function generateUniqueSlug(string $coupleTitle): string
    {
        $slug         = url_title($coupleTitle, '-', true);
        $originalSlug = $slug;
        $counter      = 1;

        while (!$this->isSlugAvailable($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

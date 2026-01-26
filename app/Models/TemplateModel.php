<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class TemplateModel extends Model
{
    protected $table            = 'templates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $protectFields = true;
    protected $allowedFields = [
        'code',
        'name',
        'description',
        'preview_url',
        'thumbnail_url',
        'is_public',
        'schema_json',
        // created_at / updated_at los maneja CI con $useTimestamps,
        // pero dejarlos aquí no rompe nada si en algún momento haces inserts manuales.
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtener templates públicos.
     */
    public function getPublic(): array
    {
        return $this->where('is_public', 1)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Obtener template por código.
     */
    public function findByCode(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        return $this->where('code', $code)->first();
    }

    /**
     * Asignar template a evento (deja solo 1 activo por evento).
     *
     * - Desactiva los templates activos previos del evento
     * - Activa (o inserta) el template indicado
     * - Opera en transacción
     */
    public function assignToEvent(int $templateId, string $eventId): bool
    {
        $eventId = trim($eventId);
        if ($templateId <= 0 || $eventId === '') {
            return false;
        }

        $db = $this->db; // Model ya trae conexión

        $db->transStart();

        // 1) Desactivar el activo actual (si existe)
        $db->table('event_templates')
            ->where('event_id', $eventId)
            ->where('is_active', 1)
            ->update([
                'is_active'  => 0,
                'applied_at' => Time::now()->toDateTimeString(), // opcional: deja traza del cambio
            ]);

        // 2) Revisar si ya existe la relación (event_id, template_id)
        $existing = $db->table('event_templates')
            ->select('event_id, template_id')
            ->where('event_id', $eventId)
            ->where('template_id', $templateId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('event_templates')
                ->where('event_id', $eventId)
                ->where('template_id', $templateId)
                ->update([
                    'is_active'  => 1,
                    'applied_at' => Time::now()->toDateTimeString(),
                ]);
        } else {
            $db->table('event_templates')->insert([
                'event_id'    => $eventId,
                'template_id' => $templateId,
                'is_active'   => 1,
                'applied_at'  => Time::now()->toDateTimeString(),
            ]);
        }

        $db->transComplete();

        return $db->transStatus();
    }

    /**
     * Obtener template activo de un evento.
     */
    public function getActiveForEvent(string $eventId): ?array
    {
        $eventId = trim($eventId);
        if ($eventId === '') {
            return null;
        }

        $db = $this->db;

        $row = $db->table('event_templates')
            ->select('templates.*')
            ->join('templates', 'templates.id = event_templates.template_id')
            ->where('event_templates.event_id', $eventId)
            ->where('event_templates.is_active', 1)
            ->orderBy('event_templates.applied_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * (Opcional) Obtener todos los templates vinculados a un evento (histórico).
     */
    public function getAllForEvent(string $eventId): array
    {
        $eventId = trim($eventId);
        if ($eventId === '') {
            return [];
        }

        $db = $this->db;

        return $db->table('event_templates')
            ->select('templates.*, event_templates.is_active, event_templates.applied_at')
            ->join('templates', 'templates.id = event_templates.template_id')
            ->where('event_templates.event_id', $eventId)
            ->orderBy('event_templates.applied_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}

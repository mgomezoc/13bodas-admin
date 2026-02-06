<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($event['couple_title']) ?><?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active"><?= esc($event['couple_title']) ?></li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= esc($event['couple_title']) ?></h1>
        <p class="page-subtitle mb-0">
            <?php
            // Enums reales:
            // service_status: draft|active|suspended|archived
            $statusClass = match ($event['service_status'] ?? 'draft') {
                'draft'     => 'bg-secondary',
                'active'    => 'bg-success',
                'suspended' => 'bg-warning text-dark',
                'archived'  => 'bg-dark',
                default     => 'bg-secondary'
            };

            $statusLabel = match ($event['service_status'] ?? 'draft') {
                'draft'     => 'Borrador',
                'active'    => 'Activo',
                'suspended' => 'Suspendido',
                'archived'  => 'Archivado',
                default     => (string)($event['service_status'] ?? 'draft')
            };
            ?>
            <span class="badge <?= $statusClass ?>"><?= esc($statusLabel) ?></span>

            <?php if (!empty($event['event_date_start'])): ?>
                <span class="ms-2 text-muted">
                    <i class="bi bi-calendar me-1"></i>
                    <?= date('d/m/Y H:i', strtotime($event['event_date_start'])) ?>
                    <?php if (!empty($event['event_date_end'])): ?>
                        - <?= date('H:i', strtotime($event['event_date_end'])) ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $invitationUrl ?>" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-eye me-2"></i>Ver Invitación
        </a>
        <a href="<?= base_url('admin/events/edit/' . $event['id']) ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-people"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= (int)($stats['total_guests'] ?? 0) ?></div>
                <div class="stat-label">Invitados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= (int)($rsvpStats['accepted'] ?? 0) ?></div>
                <div class="stat-label">Confirmados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger"><i class="bi bi-x-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= (int)($rsvpStats['declined'] ?? 0) ?></div>
                <div class="stat-label">No Asisten</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-clock"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= (int)($rsvpStats['pending'] ?? 0) ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Información del Evento -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Información del Evento
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Cliente</dt>
                    <dd class="col-sm-8">
                        <?php if (!empty($event['client_id'])): ?>
                            <a href="<?= base_url('admin/clients/view/' . $event['client_id']) ?>">
                                <?= esc($event['client_name'] ?? 'Cliente') ?>
                            </a>
                            <?php if (!empty($event['client_email'])): ?>
                                <br><small class="text-muted"><?= esc($event['client_email']) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">No asignado</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">URL</dt>
                    <dd class="col-sm-8">
                        <code><?= esc($event['slug'] ?? '') ?></code>
                    </dd>

                    <dt class="col-sm-4">Zona Horaria</dt>
                    <dd class="col-sm-8">
                        <?= !empty($event['time_zone']) ? esc($event['time_zone']) : '<span class="text-muted">No definido</span>' ?>
                    </dd>

                    <dt class="col-sm-4">RSVP Límite</dt>
                    <dd class="col-sm-8">
                        <?= !empty($event['rsvp_deadline'])
                            ? date('d/m/Y H:i', strtotime($event['rsvp_deadline']))
                            : '<span class="text-muted">No definido</span>' ?>
                    </dd>

                    <dt class="col-sm-4">Creado</dt>
                    <dd class="col-sm-8">
                        <?= !empty($event['created_at']) ? date('d/m/Y', strtotime($event['created_at'])) : '<span class="text-muted">-</span>' ?>
                    </dd>

                    <dt class="col-sm-4">Plantilla activa</dt>
                    <dd class="col-sm-8">
                        <?php if (!empty($activeTemplate['name'])): ?>
                            <span class="badge bg-light text-dark"><?= esc($activeTemplate['name']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">Sin plantilla</span>
                        <?php endif; ?>
                    </dd>
                </dl>

                <hr class="my-3">

                <!-- Config útil (sin romper roles; si tu layout no manda isAdmin, esto solo muestra lo básico) -->
                <dl class="row mb-0">
                    <dt class="col-sm-4">Modo</dt>
                    <dd class="col-sm-8">
                        <?= !empty($event['site_mode']) ? esc($event['site_mode']) : '<span class="text-muted">-</span>' ?>
                    </dd>

                    <dt class="col-sm-4">Visibilidad</dt>
                    <dd class="col-sm-8">
                        <?= !empty($event['visibility']) ? esc($event['visibility']) : '<span class="text-muted">-</span>' ?>
                    </dd>

                    <dt class="col-sm-4">Acceso</dt>
                    <dd class="col-sm-8">
                        <?= !empty($event['access_mode']) ? esc($event['access_mode']) : '<span class="text-muted">-</span>' ?>
                    </dd>

                    <dt class="col-sm-4">Demo</dt>
                    <dd class="col-sm-8">
                        <?= !empty($event['is_demo']) ? '<span class="badge bg-info">Sí</span>' : '<span class="badge bg-light text-dark">No</span>' ?>
                    </dd>

                    <dt class="col-sm-4">Pagado</dt>
                    <dd class="col-sm-8">
                        <?= !empty($event['is_paid']) ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-light text-dark">No</span>' ?>
                        <?php if (!empty($event['is_paid']) && !empty($event['paid_until'])): ?>
                            <div class="text-muted small mt-1">
                                Hasta: <?= date('d/m/Y H:i', strtotime($event['paid_until'])) ?>
                            </div>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Lugar -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-geo-alt me-2"></i>Lugar del Evento
            </div>
            <div class="card-body">
                <?php if (!empty($event['venue_name'])): ?>
                    <h5 class="mb-1"><?= esc($event['venue_name']) ?></h5>
                    <?php if (!empty($event['venue_address'])): ?>
                        <p class="text-muted mb-3"><?= nl2br(esc($event['venue_address'])) ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-3">Sin dirección</p>
                    <?php endif; ?>

                    <?php if (!empty($event['venue_geo_lat']) && !empty($event['venue_geo_lng'])): ?>
                        <?php
                        $lat = $event['venue_geo_lat'];
                        $lng = $event['venue_geo_lng'];
                        ?>
                        <div class="ratio ratio-16x9 mb-3">
                            <iframe
                                src="https://maps.google.com/maps?q=<?= esc($lat) ?>,<?= esc($lng) ?>&z=15&output=embed"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <a href="https://www.google.com/maps?q=<?= esc($lat) ?>,<?= esc($lng) ?>"
                            target="_blank"
                            class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-map me-1"></i>Ver en Google Maps
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-geo-alt text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0">Sin información de lugar</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Enlace de Invitación -->
<div class="card mt-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <label class="form-label small text-muted mb-1">URL de la Invitación</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-light" value="<?= esc($invitationUrl) ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?= esc($invitationUrl) ?>')">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="https://wa.me/?text=<?= urlencode('¡Estás invitado! ' . $invitationUrl) ?>"
                    target="_blank"
                    class="btn btn-success">
                    <i class="bi bi-whatsapp me-2"></i>Compartir por WhatsApp
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Acciones -->
<?php
$actionCards = [
    [
        'label' => 'Gestionar Invitados',
        'icon'  => 'bi-people',
        'class' => 'text-primary',
        'url'   => base_url('admin/events/' . $event['id'] . '/guests'),
    ],
    [
        'label' => 'Grupos de Invitados',
        'icon'  => 'bi-collection',
        'class' => 'text-info',
        'url'   => base_url('admin/events/' . $event['id'] . '/groups'),
    ],
    [
        'label' => 'Ver Confirmaciones',
        'icon'  => 'bi-check2-square',
        'class' => 'text-success',
        'url'   => base_url('admin/events/' . $event['id'] . '/rsvp'),
    ],
    [
        'label' => 'Galería de Fotos',
        'icon'  => 'bi-images',
        'class' => 'text-secondary',
        'url'   => base_url('admin/events/' . $event['id'] . '/gallery'),
    ],
    [
        'label' => 'Lista de Regalos',
        'icon'  => 'bi-gift',
        'class' => 'text-warning',
        'url'   => base_url('admin/events/' . $event['id'] . '/registry'),
    ],
    [
        'label' => 'Opciones de Menú',
        'icon'  => 'bi-egg-fried',
        'class' => 'text-danger',
        'url'   => base_url('admin/events/' . $event['id'] . '/menu'),
    ],
    [
        'label' => 'Cortejo',
        'icon'  => 'bi-hearts',
        'class' => 'text-danger',
        'url'   => base_url('admin/events/' . $event['id'] . '/party'),
    ],
    [
        'label' => 'Ubicaciones',
        'icon'  => 'bi-geo',
        'class' => 'text-primary',
        'url'   => base_url('admin/events/' . $event['id'] . '/locations'),
    ],
    [
        'label' => 'Agenda',
        'icon'  => 'bi-clock',
        'class' => 'text-info',
        'url'   => base_url('admin/events/' . $event['id'] . '/schedule'),
    ],
    [
        'label' => 'FAQ',
        'icon'  => 'bi-question-circle',
        'class' => 'text-secondary',
        'url'   => base_url('admin/events/' . $event['id'] . '/faq'),
    ],
    [
        'label' => 'Recomendaciones',
        'icon'  => 'bi-star',
        'class' => 'text-warning',
        'url'   => base_url('admin/events/' . $event['id'] . '/recommendations'),
    ],
    [
        'label' => 'Preguntas RSVP',
        'icon'  => 'bi-ui-checks',
        'class' => 'text-success',
        'url'   => base_url('admin/events/' . $event['id'] . '/rsvp-questions'),
    ],
    [
        'label' => 'Módulos',
        'icon'  => 'bi-grid',
        'class' => 'text-primary',
        'url'   => base_url('admin/events/' . $event['id'] . '/modules'),
    ],
];

if (!empty($isAdmin)) {
    $actionCards[] = [
        'label' => 'Dominios',
        'icon'  => 'bi-globe2',
        'class' => 'text-dark',
        'url'   => base_url('admin/events/' . $event['id'] . '/domains'),
    ];
}
?>
<div class="row mt-4 g-3">
    <?php foreach ($actionCards as $card): ?>
        <div class="col-md-4 col-lg-3">
            <a href="<?= esc($card['url']) ?>" class="card text-decoration-none h-100">
                <div class="card-body text-center py-4">
                    <i class="bi <?= esc($card['icon']) ?> <?= esc($card['class']) ?>" style="font-size: 2rem;"></i>
                    <h6 class="mt-2 mb-0"><?= esc($card['label']) ?></h6>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>

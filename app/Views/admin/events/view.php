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
        <p class="page-subtitle">
            <?php
            // Enums reales BD:
            // service_status: draft|active|suspended|archived
            // site_mode: auto|pre|live|post
            // visibility: public|private

            $statusClass = match ($event['service_status']) {
                'draft'     => 'bg-secondary',
                'active'    => 'bg-success',
                'suspended' => 'bg-warning',
                'archived'  => 'bg-dark',
                default     => 'bg-secondary'
            };

            $statusLabel = match ($event['service_status']) {
                'draft'     => 'Borrador',
                'active'    => 'Activo',
                'suspended' => 'Suspendido',
                'archived'  => 'Archivado',
                default     => $event['service_status']
            };

            $visibilityClass = match ($event['visibility'] ?? '') {
                'public'  => 'bg-primary',
                'private' => 'bg-secondary',
                default   => 'bg-secondary'
            };

            $visibilityLabel = match ($event['visibility'] ?? '') {
                'public'  => 'Público',
                'private' => 'Privado',
                default   => ($event['visibility'] ?? '')
            };

            $siteModeClass = match ($event['site_mode'] ?? '') {
                'auto' => 'bg-info',
                'pre'  => 'bg-info',
                'live' => 'bg-success',
                'post' => 'bg-secondary',
                default => 'bg-secondary'
            };

            $siteModeLabel = match ($event['site_mode'] ?? '') {
                'auto' => 'Auto',
                'pre'  => 'Pre',
                'live' => 'Live',
                'post' => 'Post',
                default => ($event['site_mode'] ?? '')
            };
            ?>
            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>

            <?php if (!empty($event['visibility'])): ?>
                <span class="badge <?= $visibilityClass ?> ms-1"><?= $visibilityLabel ?></span>
            <?php endif; ?>

            <?php if (!empty($event['site_mode'])): ?>
                <span class="badge <?= $siteModeClass ?> ms-1"><?= $siteModeLabel ?></span>
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
            <div class="stat-icon bg-primary">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['total_guests'] ?></div>
                <div class="stat-label">Invitados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $rsvpStats['accepted'] ?></div>
                <div class="stat-label">Confirmados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger">
                <i class="bi bi-x-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $rsvpStats['declined'] ?></div>
                <div class="stat-label">No Asisten</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $rsvpStats['pending'] ?></div>
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
                        <a href="<?= base_url('admin/clients/view/' . $event['client_id']) ?>">
                            <?= esc($event['client_name']) ?>
                        </a>
                        <br><small class="text-muted"><?= esc($event['client_email']) ?></small>
                    </dd>

                    <dt class="col-sm-4">Fecha</dt>
                    <dd class="col-sm-8">
                        <?= date('d/m/Y H:i', strtotime($event['event_date_start'])) ?>
                        <?php if ($event['event_date_end']): ?>
                            - <?= date('H:i', strtotime($event['event_date_end'])) ?>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Zona Horaria</dt>
                    <dd class="col-sm-8"><?= esc($event['time_zone']) ?></dd>

                    <dt class="col-sm-4">RSVP Límite</dt>
                    <dd class="col-sm-8">
                        <?= $event['rsvp_deadline'] ? date('d/m/Y H:i', strtotime($event['rsvp_deadline'])) : '<span class="text-muted">No definido</span>' ?>
                    </dd>

                    <dt class="col-sm-4">Creado</dt>
                    <dd class="col-sm-8"><?= date('d/m/Y', strtotime($event['created_at'])) ?></dd>
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
                <?php if ($event['venue_name']): ?>
                    <h5><?= esc($event['venue_name']) ?></h5>
                    <p class="text-muted mb-3"><?= nl2br(esc($event['venue_address'])) ?></p>

                    <?php if ($event['venue_geo_lat'] && $event['venue_geo_lng']): ?>
                        <a href="https://www.google.com/maps?q=<?= $event['venue_geo_lat'] ?>,<?= $event['venue_geo_lng'] ?>"
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
                    <input type="text" class="form-control bg-light" value="<?= $invitationUrl ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?= $invitationUrl ?>')">
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
<div class="row mt-4 g-3">
    <div class="col-md-4">
        <a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>" class="card text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                <h6 class="mt-2 mb-0">Gestionar Invitados</h6>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= base_url('admin/events/' . $event['id'] . '/rsvp') ?>" class="card text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-check2-square text-success" style="font-size: 2rem;"></i>
                <h6 class="mt-2 mb-0">Ver Confirmaciones</h6>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= base_url('admin/events/' . $event['id'] . '/gallery') ?>" class="card text-decoration-none h-100">
            <div class="card-body text-center py-4">
                <i class="bi bi-images text-purple" style="font-size: 2rem;"></i>
                <h6 class="mt-2 mb-0">Galería de Fotos</h6>
            </div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>
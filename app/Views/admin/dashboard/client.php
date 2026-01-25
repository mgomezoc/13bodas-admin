<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Mi Evento<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Mi Evento</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php if (!$has_event): ?>
    <!-- No tiene evento asignado -->
    <div class="card">
        <div class="card-body">
            <div class="empty-state py-5">
                <i class="bi bi-calendar-x empty-state-icon"></i>
                <h4 class="empty-state-title">Sin evento asignado</h4>
                <p class="empty-state-text">
                    Aún no tienes un evento asignado a tu cuenta.<br>
                    Por favor contacta al equipo de 13Bodas para configurar tu invitación.
                </p>
                <a href="https://wa.me/528115247741" target="_blank" class="btn btn-success">
                    <i class="bi bi-whatsapp me-2"></i>Contactar por WhatsApp
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Header del evento -->
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= esc($event['couple_title']) ?></h1>
            <p class="page-subtitle">
                <i class="bi bi-calendar me-1"></i>
                <?= date('d \d\e F, Y', strtotime($event['event_date_start'])) ?>
                <?php if ($event['venue_name']): ?>
                    <span class="mx-2">•</span>
                    <i class="bi bi-geo-alt me-1"></i>
                    <?= esc($event['venue_name']) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('admin/events/edit/' . $event['id']) ?>" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Editar Evento
            </a>
            <a href="<?= $invitation_url ?>" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-2"></i>Ver Invitación
            </a>
        </div>
    </div>

    <!-- URL de la invitación -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <label class="form-label small text-muted mb-1">URL de tu invitación</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light" value="<?= $invitation_url ?>" id="invitationUrl" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?= $invitation_url ?>')">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="https://wa.me/?text=<?= urlencode('¡Estás invitado! ' . $invitation_url) ?>" 
                       target="_blank" 
                       class="btn btn-success">
                        <i class="bi bi-whatsapp me-2"></i>Compartir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $stats['total_guests'] ?></div>
                    <div class="stat-label">Invitados Totales</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $rsvp_stats['accepted'] ?></div>
                    <div class="stat-label">Confirmados</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $rsvp_stats['declined'] ?></div>
                    <div class="stat-label">No Asisten</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $rsvp_stats['pending'] ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>" class="card text-decoration-none h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary me-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Gestionar Invitados</h6>
                        <small class="text-muted">Agregar, editar o importar invitados</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?= base_url('admin/events/' . $event['id'] . '/rsvp') ?>" class="card text-decoration-none h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success me-3">
                        <i class="bi bi-check2-square"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Ver Confirmaciones</h6>
                        <small class="text-muted">Respuestas RSVP de tus invitados</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?= base_url('admin/events/' . $event['id'] . '/gallery') ?>" class="card text-decoration-none h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-purple me-3">
                        <i class="bi bi-images"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Galería de Fotos</h6>
                        <small class="text-muted">Sube y organiza tus fotos</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?= base_url('admin/events/' . $event['id'] . '/registry') ?>" class="card text-decoration-none h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info me-3">
                        <i class="bi bi-gift"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Lista de Regalos</h6>
                        <small class="text-muted">Configura tu mesa de regalos</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?= base_url('admin/events/' . $event['id'] . '/menu') ?>" class="card text-decoration-none h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning me-3">
                        <i class="bi bi-cup-hot"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Opciones de Menú</h6>
                        <small class="text-muted">Configura las opciones de comida</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="<?= base_url('admin/events/' . $event['id'] . '/party') ?>" class="card text-decoration-none h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger me-3">
                        <i class="bi bi-hearts"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Cortejo Nupcial</h6>
                        <small class="text-muted">Padrinos y damas de honor</small>
                    </div>
                </div>
            </a>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>

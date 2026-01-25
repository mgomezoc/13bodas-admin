<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($event['couple_title']) ?><?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <?php if (!$isClient): ?>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?= esc($event['couple_title']) ?></li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header del Evento -->
<div class="page-header">
    <div>
        <h1 class="page-title"><?= esc($event['couple_title']) ?></h1>
        <p class="page-subtitle">
            <i class="bi bi-calendar me-1"></i>
            <?= date('d \d\e F, Y - H:i', strtotime($event['event_date_start'])) ?>
            <?php if ($event['venue_name']): ?>
                <span class="mx-2">•</span>
                <i class="bi bi-geo-alt me-1"></i>
                <?= esc($event['venue_name']) ?>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= $invitationUrl ?>" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-eye me-2"></i>Ver Invitación
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="copyToClipboard('<?= $invitationUrl ?>')">
            <i class="bi bi-clipboard me-2"></i>Copiar URL
        </button>
    </div>
</div>

<!-- Stats rápidas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-people"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['total_guests'] ?></div>
                <div class="stat-label">Invitados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $rsvpStats['accepted'] ?></div>
                <div class="stat-label">Confirmados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger"><i class="bi bi-x-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $rsvpStats['declined'] ?></div>
                <div class="stat-label">No Asisten</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-clock"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $rsvpStats['pending'] ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs de navegación -->
<ul class="nav nav-tabs mb-4" id="eventTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
            <i class="bi bi-info-circle"></i> Información
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>">
            <i class="bi bi-people"></i> Invitados
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/rsvp') ?>">
            <i class="bi bi-check2-square"></i> Confirmaciones
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/gallery') ?>">
            <i class="bi bi-images"></i> Galería
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/registry') ?>">
            <i class="bi bi-gift"></i> Regalos
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/menu') ?>">
            <i class="bi bi-cup-hot"></i> Menú
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/party') ?>">
            <i class="bi bi-hearts"></i> Cortejo
        </a>
    </li>
</ul>

<!-- Contenido del Tab -->
<div class="tab-content" id="eventTabsContent">
    <div class="tab-pane fade show active" id="info" role="tabpanel">
        <form id="eventForm" method="POST" action="<?= base_url('admin/events/update/' . $event['id']) ?>">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Información Principal -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-heart"></i> Información del Evento
                        </div>
                        <div class="card-body">
                            <?php if (!$isClient): ?>
                            <div class="mb-3">
                                <label class="form-label" for="client_id">Cliente</label>
                                <select id="client_id" name="client_id" class="form-select select2">
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client['id'] ?>" <?= $event['client_id'] === $client['id'] ? 'selected' : '' ?>>
                                            <?= esc($client['full_name']) ?> (<?= esc($client['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label class="form-label" for="couple_title">Título de la Pareja <span class="text-danger">*</span></label>
                                <input type="text" id="couple_title" name="couple_title" class="form-control" 
                                       value="<?= esc($event['couple_title']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="slug">URL Personalizada <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= base_url('i/') ?></span>
                                    <input type="text" id="slug" name="slug" class="form-control" 
                                           value="<?= esc($event['slug']) ?>" pattern="[a-z0-9\-_]+" required>
                                </div>
                                <div id="slugFeedback" class="mt-1"></div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="event_date_start">Fecha y Hora del Evento <span class="text-danger">*</span></label>
                                    <input type="text" id="event_date_start" name="event_date_start" 
                                           class="form-control datetimepicker" 
                                           value="<?= date('Y-m-d H:i', strtotime($event['event_date_start'])) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="event_date_end">Hora de Finalización</label>
                                    <input type="text" id="event_date_end" name="event_date_end" 
                                           class="form-control datetimepicker" 
                                           value="<?= $event['event_date_end'] ? date('Y-m-d H:i', strtotime($event['event_date_end'])) : '' ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="rsvp_deadline">Fecha Límite para Confirmar</label>
                                    <input type="text" id="rsvp_deadline" name="rsvp_deadline" 
                                           class="form-control datetimepicker" 
                                           value="<?= $event['rsvp_deadline'] ? date('Y-m-d H:i', strtotime($event['rsvp_deadline'])) : '' ?>">
                                    <div class="form-text">Hasta cuándo pueden los invitados confirmar asistencia</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="time_zone">Zona Horaria</label>
                                    <select id="time_zone" name="time_zone" class="form-select">
                                        <?php foreach ($timezones as $tz => $label): ?>
                                            <option value="<?= $tz ?>" <?= $event['time_zone'] === $tz ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="primary_contact_email">Email de Contacto</label>
                                <input type="email" id="primary_contact_email" name="primary_contact_email" 
                                       class="form-control" value="<?= esc($event['primary_contact_email']) ?>">
                                <div class="form-text">Recibirá notificaciones de confirmaciones</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lugar del Evento -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-geo-alt"></i> Lugar del Evento
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="venue_name">Nombre del Lugar</label>
                                <input type="text" id="venue_name" name="venue_name" class="form-control" 
                                       value="<?= esc($event['venue_name']) ?>" placeholder="Ej: Hacienda San José">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="venue_address">Dirección Completa</label>
                                <textarea id="venue_address" name="venue_address" class="form-control" 
                                          rows="2"><?= esc($event['venue_address']) ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="venue_geo_lat">Latitud</label>
                                    <input type="text" id="venue_geo_lat" name="venue_geo_lat" class="form-control" 
                                           value="<?= esc($event['venue_geo_lat']) ?>" placeholder="Ej: 25.6866">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="venue_geo_lng">Longitud</label>
                                    <input type="text" id="venue_geo_lng" name="venue_geo_lng" class="form-control" 
                                           value="<?= esc($event['venue_geo_lng']) ?>" placeholder="Ej: -100.3161">
                                </div>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Puedes obtener las coordenadas desde <a href="https://www.google.com/maps" target="_blank">Google Maps</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <?php if (!$isClient): ?>
                    <!-- Configuración (solo admin) -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-gear"></i> Configuración
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="service_status">Estado del Servicio</label>
                                <select id="service_status" name="service_status" class="form-select">
                                    <option value="pending" <?= $event['service_status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="active" <?= $event['service_status'] === 'active' ? 'selected' : '' ?>>Activo</option>
                                    <option value="completed" <?= $event['service_status'] === 'completed' ? 'selected' : '' ?>>Completado</option>
                                    <option value="cancelled" <?= $event['service_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="site_mode">Modo del Sitio</label>
                                <select id="site_mode" name="site_mode" class="form-select">
                                    <option value="draft" <?= $event['site_mode'] === 'draft' ? 'selected' : '' ?>>Borrador</option>
                                    <option value="pre" <?= $event['site_mode'] === 'pre' ? 'selected' : '' ?>>Pre-evento</option>
                                    <option value="live" <?= $event['site_mode'] === 'live' ? 'selected' : '' ?>>En vivo</option>
                                    <option value="post" <?= $event['site_mode'] === 'post' ? 'selected' : '' ?>>Post-evento</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" for="visibility">Visibilidad</label>
                                <select id="visibility" name="visibility" class="form-select">
                                    <option value="private" <?= $event['visibility'] === 'private' ? 'selected' : '' ?>>Privado</option>
                                    <option value="unlisted" <?= $event['visibility'] === 'unlisted' ? 'selected' : '' ?>>No listado</option>
                                    <option value="public" <?= $event['visibility'] === 'public' ? 'selected' : '' ?>>Público</option>
                                </select>
                            </div>
                            
                            <?php if (!empty($templates)): ?>
                            <div class="mb-0">
                                <label class="form-label" for="template_id">Plantilla de Diseño</label>
                                <select id="template_id" name="template_id" class="form-select">
                                    <option value="">Sin plantilla</option>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?= $template['id'] ?>" <?= ($event['template_id'] ?? '') == $template['id'] ? 'selected' : '' ?>>
                                            <?= esc($template['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Define el diseño visual de la invitación</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- URL de la Invitación -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-link-45deg"></i> Enlace de Invitación
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control bg-light" value="<?= $invitationUrl ?>" id="invitationUrl" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('<?= $invitationUrl ?>')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="<?= $invitationUrl ?>" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-eye me-2"></i>Ver Invitación
                                </a>
                                <a href="https://wa.me/?text=<?= urlencode('¡Estás invitado! ' . $invitationUrl) ?>" 
                                   target="_blank" class="btn btn-success">
                                    <i class="bi bi-whatsapp me-2"></i>Compartir por WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Acciones Rápidas -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="bi bi-lightning"></i> Acciones Rápidas
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-people me-2"></i>Gestionar Invitados
                                </a>
                                <a href="<?= base_url('admin/events/' . $event['id'] . '/gallery') ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-images me-2"></i>Subir Fotos
                                </a>
                                <a href="<?= base_url('admin/events/' . $event['id'] . '/rsvp') ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-check2-square me-2"></i>Ver Confirmaciones
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botón Guardar -->
            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <?php if (!$isClient): ?>
                    <a href="<?= base_url('admin/events') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver
                    </a>
                    <?php else: ?>
                    <div></div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Verificar disponibilidad del slug al cambiar
    let slugTimeout;
    $('#slug').on('input', function() {
        clearTimeout(slugTimeout);
        const slug = $(this).val();
        if (slug.length >= 3) {
            slugTimeout = setTimeout(function() {
                checkSlugAvailability(slug, '<?= $event['id'] ?>');
            }, 500);
        }
    });
    
    // Guardar formulario
    $('#eventForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i>Guardando...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                } else {
                    let msg = response.message || 'Error al guardar';
                    if (response.errors) {
                        msg = Object.values(response.errors).join('<br>');
                    }
                    Toast.fire({ icon: 'error', title: msg });
                }
            },
            error: function(xhr) {
                Toast.fire({ icon: 'error', title: 'Error de conexión' });
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});

function checkSlugAvailability(slug, excludeId) {
    $.post('<?= base_url('admin/events/check-slug') ?>', { slug: slug, exclude_id: excludeId })
        .done(function(response) {
            const feedback = $('#slugFeedback');
            if (response.available) {
                feedback.html('<span class="text-success small"><i class="bi bi-check-circle me-1"></i>Disponible</span>');
            } else {
                feedback.html('<span class="text-danger small"><i class="bi bi-x-circle me-1"></i>No disponible</span>');
            }
        });
}
</script>
<?= $this->endSection() ?>

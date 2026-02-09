<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Nuevo Evento<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active">Nuevo Evento</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Nuevo Evento</h1>
        <p class="page-subtitle">Crea una nueva invitación digital para tu cliente</p>
    </div>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= base_url('admin/events/store') ?>" method="POST" class="needs-validation" novalidate>
    <?= csrf_field() ?>
    <?php if (!empty($isAdmin)): ?>
        <input type="hidden" name="is_demo" value="0">
        <input type="hidden" name="is_paid" value="0">
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Información Principal -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-heart me-2"></i>Información del Evento
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="client_id">Cliente <span class="text-danger">*</span></label>
                        <select id="client_id" name="client_id" class="form-select select2" required>
                            <option value="">Seleccionar cliente...</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>" <?= $selectedClientId === $client['id'] ? 'selected' : '' ?>>
                                    <?= esc($client['full_name']) ?> (<?= esc($client['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            ¿No encuentras al cliente? <a href="<?= base_url('admin/clients/create') ?>">Crear nuevo cliente</a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="couple_title">Título de la Pareja <span class="text-danger">*</span></label>
                        <input type="text" 
                               id="couple_title" 
                               name="couple_title" 
                               class="form-control" 
                               value="<?= old('couple_title') ?>"
                               placeholder="Ej: Ana & Carlos"
                               required>
                        <div class="form-text">Este será el título principal de la invitación</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="slug">URL Personalizada <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><?= base_url('i/') ?></span>
                            <input type="text" 
                                   id="slug" 
                                   name="slug" 
                                   class="form-control" 
                                   value="<?= old('slug') ?>"
                                   placeholder="ana-y-carlos"
                                   pattern="[a-z0-9\-_]+"
                                   required>
                            <button type="button" class="btn btn-outline-secondary" id="btnCheckSlug">
                                <i class="bi bi-check-circle"></i> Verificar
                            </button>
                        </div>
                        <div class="form-text">Solo letras minúsculas, números, guiones (-) y guiones bajos (_)</div>
                        <div id="slugFeedback" class="mt-1"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="event_date_start">Fecha y Hora del Evento <span class="text-danger">*</span></label>
                            <input type="text" 
                                   id="event_date_start" 
                                   name="event_date_start" 
                                   class="form-control datetimepicker" 
                                   value="<?= old('event_date_start') ?>"
                                   required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="event_date_end">Hora de Finalización</label>
                            <input type="text" 
                                   id="event_date_end" 
                                   name="event_date_end" 
                                   class="form-control datetimepicker" 
                                   value="<?= old('event_date_end') ?>">
                            <div class="form-text">Opcional</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="rsvp_deadline">Fecha Límite de RSVP</label>
                            <input type="text" 
                                   id="rsvp_deadline" 
                                   name="rsvp_deadline" 
                                   class="form-control datetimepicker" 
                                   value="<?= old('rsvp_deadline') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="time_zone">Zona Horaria</label>
                            <select id="time_zone" name="time_zone" class="form-select">
                                <?php foreach ($timezones as $tz => $label): ?>
                                    <option value="<?= $tz ?>" <?= old('time_zone', 'America/Mexico_City') === $tz ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lugar del Evento -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt me-2"></i>Lugar del Evento
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="venue_name">Nombre del Lugar</label>
                        <input type="text" 
                               id="venue_name" 
                               name="venue_name" 
                               class="form-control" 
                               value="<?= old('venue_name') ?>"
                               placeholder="Ej: Hacienda San José">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="venue_address">Dirección Completa</label>
                        <textarea id="venue_address" 
                                  name="venue_address" 
                                  class="form-control" 
                                  rows="2"
                                  placeholder="Calle, número, colonia, ciudad, estado, CP"><?= old('venue_address') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="venue_geo_lat">Latitud</label>
                            <input type="text"
                                   id="venue_geo_lat"
                                   name="venue_geo_lat"
                                   class="form-control"
                                   value="<?= old('venue_geo_lat') ?>"
                                   placeholder="Ej: 25.6866">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="venue_geo_lng">Longitud</label>
                            <input type="text"
                                   id="venue_geo_lng"
                                   name="venue_geo_lng"
                                   class="form-control"
                                   value="<?= old('venue_geo_lng') ?>"
                                   placeholder="Ej: -100.3161">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <?php if (!empty($isAdmin)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-gear me-2"></i>Configuración (Admin)
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="service_status">Estado del Servicio</label>
                            <select id="service_status" name="service_status" class="form-select">
                                <option value="draft" <?= old('service_status', 'draft') === 'draft' ? 'selected' : '' ?>>Borrador</option>
                                <option value="active" <?= old('service_status') === 'active' ? 'selected' : '' ?>>Activo</option>
                                <option value="suspended" <?= old('service_status') === 'suspended' ? 'selected' : '' ?>>Suspendido</option>
                                <option value="archived" <?= old('service_status') === 'archived' ? 'selected' : '' ?>>Archivado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="visibility">Visibilidad</label>
                            <select id="visibility" name="visibility" class="form-select">
                                <option value="private" <?= old('visibility', 'private') === 'private' ? 'selected' : '' ?>>Privado</option>
                                <option value="public" <?= old('visibility') === 'public' ? 'selected' : '' ?>>Público</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="access_mode">Modo de Acceso</label>
                            <select id="access_mode" name="access_mode" class="form-select">
                                <option value="open" <?= old('access_mode', 'open') === 'open' ? 'selected' : '' ?>>Abierto</option>
                                <option value="invite_code" <?= old('access_mode') === 'invite_code' ? 'selected' : '' ?>>Con código</option>
                            </select>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="is_demo" name="is_demo" value="1"
                                <?= old('is_demo') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_demo">Es demo</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid" value="1"
                                <?= old('is_paid') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_paid">Pagado</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="paid_until">Pagado hasta</label>
                            <input type="text" id="paid_until" name="paid_until"
                                   class="form-control datetimepicker"
                                   value="<?= old('paid_until') ?>">
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <!-- Contacto -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-envelope me-2"></i>Contacto
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="primary_contact_email">Email de Contacto</label>
                        <input type="email" 
                               id="primary_contact_email" 
                               name="primary_contact_email" 
                               class="form-control" 
                               value="<?= old('primary_contact_email') ?>"
                               placeholder="pareja@email.com">
                        <div class="form-text">Para notificaciones de RSVP</div>
                    </div>
                </div>
            </div>
            
            <!-- Información -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <h6><i class="bi bi-info-circle me-2"></i>¿Qué sigue?</h6>
                        <p class="small mb-2">Después de crear el evento, podrás:</p>
                        <ul class="small mb-0">
                            <li>Completar información de la pareja</li>
                            <li>Agregar invitados</li>
                            <li>Subir fotos a la galería</li>
                            <li>Configurar la línea de tiempo</li>
                            <li>Agregar lista de regalos</li>
                            <li>Personalizar el diseño</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body d-flex justify-content-between">
            <a href="<?= base_url('admin/events') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-2"></i>Crear Evento
            </button>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Auto-generar slug desde el título
    $('#couple_title').on('blur', function() {
        if ($('#slug').val() === '') {
            const slug = generateSlug($(this).val());
            $('#slug').val(slug);
        }
    });
    
    // Verificar disponibilidad del slug
    $('#btnCheckSlug, #slug').on('click blur', function() {
        const slug = $('#slug').val();
        if (slug.length >= 3) {
            checkSlugAvailability(slug);
        }
    });
});

function syncPaidUntil() {
    const $isPaid = $('#is_paid');
    const $paidUntil = $('#paid_until');
    if (!$isPaid.length || !$paidUntil.length) return;
    if (!$isPaid.is(':checked')) {
        $paidUntil.val('').prop('disabled', true);
    } else {
        $paidUntil.prop('disabled', false);
    }
}

syncPaidUntil();
$('#is_paid').on('change', syncPaidUntil);

function checkSlugAvailability(slug) {
    $.post('<?= base_url('admin/events/check-slug') ?>', { slug: slug })
        .done(function(response) {
            const feedback = $('#slugFeedback');
            if (response.available) {
                feedback.html('<span class="text-success"><i class="bi bi-check-circle me-1"></i>Disponible</span>');
            } else {
                feedback.html('<span class="text-danger"><i class="bi bi-x-circle me-1"></i>No disponible, elige otro</span>');
            }
        });
}
</script>
<?= $this->endSection() ?>

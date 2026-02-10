<?php declare(strict_types=1); ?>
<!-- ✅ app/Views/admin/events/edit.php (COMPLETO) -->
<?= $this->extend('layouts/admin') ?>
<?php $highlightTemplateSelection = (string) service('request')->getGet('highlight') === 'template'; ?>

<?= $this->section('title') ?><?= esc($event['couple_title']) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<style>
.template-highlight {
    border: 2px solid #c89d67;
    border-radius: 12px;
    box-shadow: 0 0 0 0 rgba(200,157,103,.45);
    animation: templatePulse 1.8s ease-in-out infinite;
}
@keyframes templatePulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(200,157,103,.05); }
    50% { box-shadow: 0 0 0 8px rgba(200,157,103,.2); }
}
</style>
<?= $this->endSection() ?>

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
<?= view('admin/events/partials/_event_navigation', ['active' => 'informacion', 'event_id' => $event['id']]) ?>
<!-- Header del Evento -->
<div class="page-header">
    <div>
        <h1 class="page-title"><?= esc($event['couple_title']) ?></h1>
        <p class="page-subtitle">
            <i class="bi bi-calendar me-1"></i>
            <?= date('d \d\e F, Y - H:i', strtotime($event['event_date_start'])) ?>
            <?php if (!empty($event['venue_name'])): ?>
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

<?= view('admin/events/partials/_section_help', ['message' => 'Aquí configuras los datos base del evento: nombres, fechas, ubicación, visibilidad y ajustes generales de la invitación.']) ?>

<!-- Stats rápidas -->
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

<!-- Tabs de navegación -->

<!-- Contenido del Tab -->
<div class="tab-content" id="eventTabsContent">
    <div class="tab-pane fade show active" id="info" role="tabpanel">
        <form id="eventForm" method="POST" action="<?= base_url('admin/events/update/' . $event['id']) ?>">
            <?= csrf_field() ?>

            <!-- ✅ Nota: Hidden flags para que NUNCA se “pierdan” cuando el checkbox va desmarcado -->
            <?php if (!empty($isAdmin)): ?>
                <input type="hidden" name="is_demo" value="0">
                <input type="hidden" name="is_paid" value="0">
            <?php endif; ?>

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
                                        <?php foreach (($clients ?? []) as $client): ?>
                                            <option value="<?= $client['id'] ?>" <?= (string)$event['client_id'] === (string)$client['id'] ? 'selected' : '' ?>>
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

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="bride_name">Nombre de la Novia</label>
                                    <input type="text" id="bride_name" name="bride_name" class="form-control"
                                        value="<?= esc($event['bride_name'] ?? '') ?>" placeholder="Ej: María">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="groom_name">Nombre del Novio</label>
                                    <input type="text" id="groom_name" name="groom_name" class="form-control"
                                        value="<?= esc($event['groom_name'] ?? '') ?>" placeholder="Ej: Carlos">
                                </div>
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
                                        value="<?= !empty($event['event_date_start']) ? date('Y-m-d H:i', strtotime($event['event_date_start'])) : '' ?>"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="event_date_end">Hora de Finalización</label>
                                    <input type="text" id="event_date_end" name="event_date_end"
                                        class="form-control datetimepicker"
                                        value="<?= !empty($event['event_date_end']) ? date('Y-m-d H:i', strtotime($event['event_date_end'])) : '' ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="rsvp_deadline">Fecha Límite para Confirmar</label>
                                    <input type="text" id="rsvp_deadline" name="rsvp_deadline"
                                        class="form-control datetimepicker"
                                        value="<?= !empty($event['rsvp_deadline']) ? date('Y-m-d H:i', strtotime($event['rsvp_deadline'])) : '' ?>">
                                    <div class="form-text">Hasta cuándo pueden los invitados confirmar asistencia</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="time_zone">Zona Horaria</label>
                                    <select id="time_zone" name="time_zone" class="form-select">
                                        <?php foreach (($timezones ?? []) as $tz => $label): ?>
                                            <option value="<?= $tz ?>" <?= ($event['time_zone'] ?? '') === $tz ? 'selected' : '' ?>>
                                                <?= esc($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <?php
                            $contactEmail = $event['primary_contact_email'] ?? '';
                            if (trim((string)$contactEmail) === '') {
                                $contactEmail = $event['client_email'] ?? '';
                            }
                            ?>
                            <div class="mb-3">
                                <label class="form-label" for="primary_contact_email">Email de Contacto</label>
                                <input type="email" id="primary_contact_email" name="primary_contact_email"
                                    class="form-control" value="<?= esc($contactEmail) ?>">
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
                                    value="<?= esc($event['venue_name'] ?? '') ?>" placeholder="Ej: Hacienda San José">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="venue_address">Dirección Completa</label>
                                <textarea id="venue_address" name="venue_address" class="form-control"
                                    rows="2"><?= esc($event['venue_address'] ?? '') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="venue_geo_lat">Latitud</label>
                                    <input type="text" id="venue_geo_lat" name="venue_geo_lat" class="form-control"
                                        value="<?= esc($event['venue_geo_lat'] ?? '') ?>" placeholder="Ej: 25.6866">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="venue_geo_lng">Longitud</label>
                                    <input type="text" id="venue_geo_lng" name="venue_geo_lng" class="form-control"
                                        value="<?= esc($event['venue_geo_lng'] ?? '') ?>" placeholder="Ej: -100.3161">
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="geocodeVenue">
                                <i class="bi bi-map"></i> Buscar coordenadas
                            </button>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Usa la dirección o el nombre del lugar para buscar en OpenStreetMap.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <?php if (!empty($isAdmin)): ?>
                        <!-- ✅ Configuración (solo admin) -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="bi bi-gear"></i> Configuración
                            </div>
                            <div class="card-body">
                                <!-- ✅ Paso 1 (CRÍTICO) enums corregidos -->
                                <div class="mb-3">
                                    <label class="form-label" for="service_status">Estado del Servicio</label>
                                    <select id="service_status" name="service_status" class="form-select js-config-autosave">
                                        <option value="draft" <?= ($event['service_status'] ?? '') === 'draft' ? 'selected' : '' ?>>Borrador</option>
                                        <option value="active" <?= ($event['service_status'] ?? '') === 'active' ? 'selected' : '' ?>>Activo</option>
                                        <option value="suspended" <?= ($event['service_status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspendido</option>
                                        <option value="archived" <?= ($event['service_status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archivado</option>
                                    </select>
                                </div>


                                <div class="mb-3">
                                    <label class="form-label" for="visibility">Visibilidad</label>
                                    <select id="visibility" name="visibility" class="form-select js-config-autosave">
                                        <option value="private" <?= ($event['visibility'] ?? '') === 'private' ? 'selected' : '' ?>>Privado</option>
                                        <option value="public" <?= ($event['visibility'] ?? '') === 'public' ? 'selected' : '' ?>>Público</option>
                                    </select>
                                </div>

                                <!-- ✅ Paso 2 (admin-only) campos en events -->
                                <div class="mb-3">
                                    <label class="form-label" for="access_mode">Modo de Acceso</label>
                                    <select id="access_mode" name="access_mode" class="form-select js-config-autosave">
                                        <option value="open" <?= ($event['access_mode'] ?? '') === 'open' ? 'selected' : '' ?>>Abierto</option>
                                        <option value="invite_code" <?= ($event['access_mode'] ?? '') === 'invite_code' ? 'selected' : '' ?>>Con código</option>
                                    </select>
                                    <div class="form-text">Controla el acceso a la invitación</div>
                                </div>

                                <div id="templateSelectionCard" class="mb-3 <?= $highlightTemplateSelection ? 'template-highlight p-3' : '' ?>">
                                    <label class="form-label" for="template_id">Template</label>
                                    <?php if ($highlightTemplateSelection): ?>
                                        <div class="alert alert-warning py-2" role="alert">
                                            <i class="bi bi-palette me-1"></i>Selecciona la plantilla de tu invitación para continuar.
                                        </div>
                                    <?php endif; ?>
                                    <select id="template_id" name="template_id" class="form-select js-config-autosave">
                                        <option value="">Selecciona un template</option>
                                        <?php foreach (($templates ?? []) as $template): ?>
                                            <option value="<?= (int) ($template['id'] ?? 0) ?>" <?= (string) ($event['template_id'] ?? '') === (string) ($template['id'] ?? '') ? 'selected' : '' ?>>
                                                <?= esc($template['name'] ?? 'Sin nombre') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input js-config-autosave" type="checkbox" id="is_demo" name="is_demo" value="1"
                                        <?= !empty($event['is_demo']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_demo">Es demo</label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input js-config-autosave" type="checkbox" id="is_paid" name="is_paid" value="1"
                                        <?= !empty($event['is_paid']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_paid">Pagado</label>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="paid_until">Pagado hasta</label>
                                    <input type="text" id="paid_until" name="paid_until"
                                        class="form-control datetimepicker js-config-autosave"
                                        value="<?= !empty($event['paid_until']) ? date('Y-m-d H:i', strtotime($event['paid_until'])) : '' ?>">
                                    <div class="form-text">Si “Pagado” está desactivado, este campo se guarda como NULL.</div>
                                </div>

                                <div id="configAutosaveStatus" class="small text-muted" aria-live="polite"></div>

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
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
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

        // UX: si no está pagado, deshabilitar/limpiar paid_until
        function syncPaidUntil() {
            const $isPaid = $('#is_paid');
            const $paidUntil = $('#paid_until');

            if (!$isPaid.length || !$paidUntil.length) return;

            const isPaid = $isPaid.is(':checked');
            if (!isPaid) {
                $paidUntil.val('').prop('disabled', true);
            } else {
                $paidUntil.prop('disabled', false);
            }
        }

        syncPaidUntil();
        $('#is_paid').on('change', function() {
            syncPaidUntil();
        });

        let configAutosaveTimeout;
        let configAutosaveRequest = null;

        function setConfigAutosaveStatus(message, cssClass = 'text-muted') {
            const $status = $('#configAutosaveStatus');
            if (!$status.length) {
                return;
            }

            $status.removeClass('text-muted text-success text-danger').addClass(cssClass).text(message);
        }

        function buildConfigAutosavePayload() {
            const csrfInput = $('#eventForm input[name="<?= csrf_token() ?>"]');

            return {
                service_status: $('#service_status').val() || '',
                visibility: $('#visibility').val() || '',
                access_mode: $('#access_mode').val() || '',
                template_id: $('#template_id').val() || '',
                is_demo: $('#is_demo').is(':checked') ? 1 : 0,
                is_paid: $('#is_paid').is(':checked') ? 1 : 0,
                paid_until: $('#paid_until').is(':disabled') ? '' : ($('#paid_until').val() || ''),
                [csrfInput.attr('name')]: csrfInput.val()
            };
        }

        function saveConfigurationRealtime() {
            if (configAutosaveRequest && typeof configAutosaveRequest.abort === 'function') {
                configAutosaveRequest.abort();
            }

            setConfigAutosaveStatus('Guardando configuración...', 'text-muted');

            configAutosaveRequest = $.ajax({
                url: '<?= base_url('admin/events/update-settings/' . $event['id']) ?>',
                method: 'POST',
                data: buildConfigAutosavePayload(),
                dataType: 'json'
            }).done(function(response) {
                if (!response.success) {
                    setConfigAutosaveStatus(response.message || 'Error al guardar configuración.', 'text-danger');
                    return;
                }

                setConfigAutosaveStatus('Configuración guardada en tiempo real.', 'text-success');
            }).fail(function(xhr, status) {
                if (status === 'abort') {
                    return;
                }
                setConfigAutosaveStatus('No se pudo guardar la configuración.', 'text-danger');
            });
        }

        function triggerConfigAutosave() {
            clearTimeout(configAutosaveTimeout);
            configAutosaveTimeout = setTimeout(saveConfigurationRealtime, 450);
        }

        $('.js-config-autosave').on('change', triggerConfigAutosave);
        $('#paid_until').on('blur', triggerConfigAutosave);

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
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                    } else {
                        let msg = response.message || 'Error al guardar';
                        if (response.errors) {
                            msg = Object.values(response.errors).join('<br>');
                        }
                        Toast.fire({
                            icon: 'error',
                            title: msg
                        });
                    }
                },
                error: function() {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error de conexión'
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });

    $('#geocodeVenue').on('click', async function() {
        const $btn = $(this);
        const originalText = $btn.html();
        const venueName = $('#venue_name').val().trim();
        const venueAddress = $('#venue_address').val().trim();
        const query = [venueName, venueAddress].filter(Boolean).join(', ');

        if (!query) {
            Toast.fire({
                icon: 'warning',
                title: 'Agrega el nombre o la dirección del lugar.'
            });
            return;
        }

        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Buscando...');

        try {
            const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(query)}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();

            if (data && data.length > 0) {
                $('#venue_geo_lat').val(parseFloat(data[0].lat).toFixed(6));
                $('#venue_geo_lng').val(parseFloat(data[0].lon).toFixed(6));
                Toast.fire({
                    icon: 'success',
                    title: 'Coordenadas actualizadas.'
                });
            } else {
                Toast.fire({
                    icon: 'warning',
                    title: 'No se encontraron coordenadas.'
                });
            }
        } catch (error) {
            Toast.fire({
                icon: 'error',
                title: 'No se pudo consultar el mapa.'
            });
        } finally {
            $btn.prop('disabled', false).html(originalText);
        }
    });

    function checkSlugAvailability(slug, excludeId) {
        $.post('<?= base_url('admin/events/check-slug') ?>', {
            slug: slug,
            exclude_id: excludeId
        }).done(function(response) {
            const feedback = $('#slugFeedback');
            if (response.available) {
                feedback.html('<span class="text-success small"><i class="bi bi-check-circle me-1"></i>Disponible</span>');
            } else {
                feedback.html('<span class="text-danger small"><i class="bi bi-x-circle me-1"></i>No disponible</span>');
            }
        });
    }
    <?php if ($highlightTemplateSelection): ?>
    const templateCard = document.getElementById('templateSelectionCard');
    if (templateCard) {
        setTimeout(() => {
            templateCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }
    <?php endif; ?>
</script>
<?= $this->endSection() ?>

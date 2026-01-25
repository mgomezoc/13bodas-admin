<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Confirmaciones RSVP<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">RSVP</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Confirmaciones RSVP</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-2"></i>Exportar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= base_url('admin/events/' . $event['id'] . '/rsvp/export') ?>">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Lista Completa (CSV)
                </a></li>
                <li><a class="dropdown-item" href="<?= base_url('admin/events/' . $event['id'] . '/rsvp/export-meals') ?>">
                    <i class="bi bi-cup-hot me-2"></i>Resumen de Menú
                </a></li>
                <li><a class="dropdown-item" href="<?= base_url('admin/events/' . $event['id'] . '/rsvp/export-songs') ?>">
                    <i class="bi bi-music-note-beamed me-2"></i>Lista de Canciones
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-people"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['total'] ?></div>
                <div class="stat-label">Total Invitados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['accepted'] ?></div>
                <div class="stat-label">Confirmados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger"><i class="bi bi-x-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['declined'] ?></div>
                <div class="stat-label">No Asisten</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-clock"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
</div>

<!-- Progress bar -->
<?php 
$totalResponded = $stats['accepted'] + $stats['declined'];
$responseRate = $stats['total'] > 0 ? round(($totalResponded / $stats['total']) * 100) : 0;
$confirmedRate = $stats['total'] > 0 ? round(($stats['accepted'] / $stats['total']) * 100) : 0;
?>
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Tasa de respuesta</span>
            <strong><?= $responseRate ?>%</strong>
        </div>
        <div class="progress mb-3" style="height: 10px;">
            <div class="progress-bar bg-success" style="width: <?= $confirmedRate ?>%" title="Confirmados"></div>
            <div class="progress-bar bg-danger" style="width: <?= $stats['total'] > 0 ? round(($stats['declined'] / $stats['total']) * 100) : 0 ?>%" title="No asisten"></div>
        </div>
        <div class="d-flex gap-4 small">
            <span><span class="badge bg-success">&nbsp;</span> Confirmados: <?= $stats['accepted'] ?></span>
            <span><span class="badge bg-danger">&nbsp;</span> No asisten: <?= $stats['declined'] ?></span>
            <span><span class="badge bg-warning">&nbsp;</span> Pendientes: <?= $stats['pending'] ?></span>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><i class="bi bi-info-circle me-1"></i>Información</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>"><i class="bi bi-people me-1"></i>Invitados</a></li>
    <li class="nav-item"><button class="nav-link active" type="button"><i class="bi bi-check2-square me-1"></i>RSVPs</button></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/gallery') ?>"><i class="bi bi-images me-1"></i>Galería</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/registry') ?>"><i class="bi bi-gift me-1"></i>Regalos</a></li>
</ul>

<!-- Nav pills para secciones -->
<ul class="nav nav-pills mb-4" id="rsvpTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="list-tab" data-bs-toggle="pill" data-bs-target="#list-pane" type="button">
            <i class="bi bi-list-ul me-1"></i>Lista de Respuestas
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="meals-tab" data-bs-toggle="pill" data-bs-target="#meals-pane" type="button">
            <i class="bi bi-cup-hot me-1"></i>Menú (<?= array_sum(array_column($mealSummary, 'count')) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="songs-tab" data-bs-toggle="pill" data-bs-target="#songs-pane" type="button">
            <i class="bi bi-music-note me-1"></i>Canciones (<?= count($songRequests) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="messages-tab" data-bs-toggle="pill" data-bs-target="#messages-pane" type="button">
            <i class="bi bi-chat-heart me-1"></i>Mensajes (<?= count($messages) ?>)
        </button>
    </li>
    <?php if (!empty($dietaryRestrictions)): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="dietary-tab" data-bs-toggle="pill" data-bs-target="#dietary-pane" type="button">
            <i class="bi bi-exclamation-triangle me-1"></i>Dietas (<?= count($dietaryRestrictions) ?>)
        </button>
    </li>
    <?php endif; ?>
    <?php if (!empty($transportRequests)): ?>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="transport-tab" data-bs-toggle="pill" data-bs-target="#transport-pane" type="button">
            <i class="bi bi-bus-front me-1"></i>Transporte (<?= count($transportRequests) ?>)
        </button>
    </li>
    <?php endif; ?>
</ul>

<div class="tab-content" id="rsvpTabsContent">
    <!-- Lista de respuestas -->
    <div class="tab-pane fade show active" id="list-pane" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <!-- Filtros rápidos -->
                <div class="mb-3">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-filter="">Todos</button>
                        <button type="button" class="btn btn-outline-success" data-filter="accepted">Confirmados</button>
                        <button type="button" class="btn btn-outline-danger" data-filter="declined">No Asisten</button>
                        <button type="button" class="btn btn-outline-warning" data-filter="pending">Pendientes</button>
                    </div>
                </div>
                
                <table 
                    id="rsvpTable"
                    data-toggle="table"
                    data-url="<?= base_url('admin/events/' . $event['id'] . '/rsvp/list') ?>"
                    data-pagination="true"
                    data-page-size="25"
                    data-search="true"
                    data-search-align="left"
                    data-show-refresh="true"
                    data-sort-name="group_name"
                    data-sort-order="asc"
                    data-locale="es-MX"
                    data-response-handler="responseHandler"
                    data-query-params="queryParams"
                    class="table table-hover">
                    <thead>
                        <tr>
                            <th data-field="group_name" data-sortable="true">Grupo</th>
                            <th data-field="first_name" data-formatter="nameFormatter" data-sortable="true">Invitado</th>
                            <th data-field="rsvp_status" data-formatter="rsvpStatusFormatter" data-align="center">Estado</th>
                            <th data-field="meal_name">Menú</th>
                            <th data-field="responded_at" data-formatter="dateTimeFormatter" data-sortable="true">Fecha Respuesta</th>
                            <th data-field="id" data-formatter="actionsFormatter" data-align="right">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Resumen de menú -->
    <div class="tab-pane fade" id="meals-pane" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-cup-hot me-2"></i>Resumen de Opciones de Menú
            </div>
            <div class="card-body">
                <?php if (empty($mealSummary)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-cup" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No hay selecciones de menú registradas</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Opción de Menú</th>
                                    <th class="text-end">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mealSummary as $meal): ?>
                                <tr>
                                    <td><?= esc($meal['name']) ?></td>
                                    <td class="text-end"><strong><?= $meal['count'] ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th>Total</th>
                                    <th class="text-end"><?= array_sum(array_column($mealSummary, 'count')) ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Canciones -->
    <div class="tab-pane fade" id="songs-pane" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-music-note-beamed me-2"></i>Canciones Sugeridas</span>
                <?php if (!empty($songRequests)): ?>
                <a href="<?= base_url('admin/events/' . $event['id'] . '/rsvp/export-songs') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download me-1"></i>Exportar
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($songRequests)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-music-note" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No hay canciones sugeridas</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($songRequests as $song): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-music-note-beamed text-primary me-2"></i>
                                <strong><?= esc($song['song_request']) ?></strong>
                            </div>
                            <small class="text-muted">
                                Sugerida por <?= esc($song['first_name'] . ' ' . $song['last_name']) ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Mensajes -->
    <div class="tab-pane fade" id="messages-pane" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-chat-heart me-2"></i>Mensajes de los Invitados
            </div>
            <div class="card-body">
                <?php if (empty($messages)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-chat" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">No hay mensajes de los invitados</p>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($messages as $msg): ?>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <p class="card-text mb-2">"<?= esc($msg['message_to_couple']) ?>"</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-person me-1"></i>
                                            <?= esc($msg['first_name'] . ' ' . $msg['last_name']) ?>
                                        </small>
                                        <small class="text-muted">
                                            <?= date('d/m/Y', strtotime($msg['responded_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Restricciones dietéticas -->
    <?php if (!empty($dietaryRestrictions)): ?>
    <div class="tab-pane fade" id="dietary-pane" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-exclamation-triangle me-2"></i>Restricciones Dietéticas
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invitado</th>
                                <th>Restricción / Alergia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dietaryRestrictions as $item): ?>
                            <tr>
                                <td><?= esc($item['first_name'] . ' ' . $item['last_name']) ?></td>
                                <td><span class="badge bg-warning text-dark"><?= esc($item['dietary_restrictions']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Transporte -->
    <?php if (!empty($transportRequests)): ?>
    <div class="tab-pane fade" id="transport-pane" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bus-front me-2"></i>Solicitudes de Transporte
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong><?= count($transportRequests) ?></strong> invitados han solicitado transporte.
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Invitado</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transportRequests as $req): ?>
                            <tr>
                                <td><?= esc($req['group_name']) ?></td>
                                <td><?= esc($req['first_name'] . ' ' . $req['last_name']) ?></td>
                                <td><?= esc($req['email']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const eventId = '<?= $event['id'] ?>';
let currentFilter = '';

function queryParams(params) {
    params.rsvp_status = currentFilter;
    return params;
}

function nameFormatter(value, row) {
    let html = `<strong>${row.first_name} ${row.last_name}</strong>`;
    if (row.is_child == 1) {
        html += ' <span class="badge bg-light text-dark">Niño</span>';
    }
    return html;
}

function rsvpStatusFormatter(value) {
    const map = {
        'pending': '<span class="badge bg-warning">Pendiente</span>',
        'accepted': '<span class="badge bg-success">Confirmado</span>',
        'declined': '<span class="badge bg-danger">No Asiste</span>'
    };
    return map[value] || value;
}

function actionsFormatter(value, row) {
    return `
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Cambiar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" onclick="updateStatus('${row.id}', 'accepted')">
                    <i class="bi bi-check-circle text-success me-2"></i>Marcar Confirmado
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="updateStatus('${row.id}', 'declined')">
                    <i class="bi bi-x-circle text-danger me-2"></i>Marcar No Asiste
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="updateStatus('${row.id}', 'pending')">
                    <i class="bi bi-clock text-warning me-2"></i>Marcar Pendiente
                </a></li>
            </ul>
        </div>
    `;
}

function updateStatus(guestId, status) {
    $.post(`${BASE_URL}admin/events/${eventId}/rsvp/update-status/${guestId}`, { status: status })
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                $('#rsvpTable').bootstrapTable('refresh');
                // Recargar página para actualizar stats
                setTimeout(() => location.reload(), 1000);
            } else {
                Toast.fire({ icon: 'error', title: response.message });
            }
        });
}

// Filtros
$(document).on('click', '[data-filter]', function() {
    $('.btn-group [data-filter]').removeClass('active');
    $(this).addClass('active');
    currentFilter = $(this).data('filter');
    $('#rsvpTable').bootstrapTable('refresh');
});
</script>
<?= $this->endSection() ?>

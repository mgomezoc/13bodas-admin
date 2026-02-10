<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Confirmaciones<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Confirmaciones</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('admin/events/partials/_event_navigation', ['active' => 'confirmaciones', 'event_id' => $event['id']]) ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Confirmaciones de Asistencia</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?> • ¿Quién viene a tu evento?</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-download me-2"></i>Exportar
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= base_url('admin/events/' . $event['id'] . '/rsvp/export') ?>">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Lista Completa (CSV)
                </a></li>
            </ul>
        </div>
    </div>
</div>

<?= view('admin/events/partials/_section_help', ['message' => 'Revisa quién confirmó, quién declinó y quién sigue pendiente. También puedes actualizar el estado manualmente cuando sea necesario.']) ?>


<div id="rsvpSection">
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
                <div class="stat-label">Sin Responder</div>
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
            <span class="text-muted">Progreso de respuestas</span>
            <strong><?= $responseRate ?>% han respondido</strong>
        </div>
        <div class="progress mb-3" style="height: 12px;">
            <div class="progress-bar bg-success" style="width: <?= $confirmedRate ?>%" title="Confirmados"></div>
            <div class="progress-bar bg-danger" style="width: <?= $stats['total'] > 0 ? round(($stats['declined'] / $stats['total']) * 100) : 0 ?>%" title="No asisten"></div>
        </div>
        <div class="d-flex gap-4 small flex-wrap">
            <span><span class="badge bg-success">&nbsp;</span> Asistirán: <?= $stats['accepted'] ?></span>
            <span><span class="badge bg-danger">&nbsp;</span> No asisten: <?= $stats['declined'] ?></span>
            <span><span class="badge bg-warning">&nbsp;</span> Sin responder: <?= $stats['pending'] ?></span>
        </div>
    </div>
</div>


<!-- Lista de respuestas -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-list-check me-2"></i>Lista de Respuestas</span>
        <!-- Filtros rápidos -->
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary active" data-filter="">Todos</button>
            <button type="button" class="btn btn-outline-success" data-filter="accepted">Confirmados</button>
            <button type="button" class="btn btn-outline-danger" data-filter="declined">No Asisten</button>
            <button type="button" class="btn btn-outline-warning" data-filter="pending">Pendientes</button>
        </div>
    </div>
    <div class="card-body">
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
                    <th data-field="group_name" data-sortable="true">Grupo/Familia</th>
                    <th data-field="first_name" data-formatter="nameFormatter" data-sortable="true">Invitado</th>
                    <th data-field="rsvp_status" data-formatter="rsvpStatusFormatter" data-align="center">Estado</th>
                    <th data-field="responded_at" data-formatter="dateTimeFormatter" data-sortable="true">Fecha Respuesta</th>
                    <th data-field="id" data-formatter="actionsFormatter" data-align="right">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Mensajes de los invitados -->
<?php if (!empty($messages)): ?>
<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-chat-heart me-2"></i>Mensajes de tus Invitados
        <span class="badge bg-primary ms-2"><?= count($messages) ?></span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach (array_slice($messages, 0, 6) as $msg): ?>
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
    </div>
</div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
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
        'pending': '<span class="badge bg-warning">Sin responder</span>',
        'accepted': '<span class="badge bg-success">Asistirá</span>',
        'declined': '<span class="badge bg-danger">No asiste</span>'
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
                    <i class="bi bi-check-circle text-success me-2"></i>Marcar que Asistirá
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="updateStatus('${row.id}', 'declined')">
                    <i class="bi bi-x-circle text-danger me-2"></i>Marcar que No Asiste
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="updateStatus('${row.id}', 'pending')">
                    <i class="bi bi-clock text-warning me-2"></i>Marcar como Pendiente
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
                refreshModuleSection('#rsvpSection');
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

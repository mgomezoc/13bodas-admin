<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Eventos<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Eventos</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Eventos</h1>
        <p class="page-subtitle">Gestiona las invitaciones digitales de tus clientes</p>
    </div>
    <a href="<?= base_url('admin/events/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Evento
    </a>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="text-muted small">Filtrar por estado:</span>
            </div>
            <div class="col-auto">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-filter="">Todos</button>
                    <button type="button" class="btn btn-outline-warning" data-filter="draft">Borrador</button>
                    <button type="button" class="btn btn-outline-success" data-filter="active">Activo</button>
                    <button type="button" class="btn btn-outline-info" data-filter="suspended">Suspendido</button>
                    <button type="button" class="btn btn-outline-danger" data-filter="archived">Archivado</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table 
            id="eventsTable"
            data-toggle="table"
            data-url="<?= base_url('admin/events/list') ?>"
            data-pagination="true"
            data-page-size="15"
            data-page-list="[15, 30, 50, 100]"
            data-search="true"
            data-search-align="left"
            data-show-refresh="true"
            data-show-columns="true"
            data-sort-name="created_at"
            data-sort-order="desc"
            data-locale="es-MX"
            data-response-handler="responseHandler"
            data-query-params="queryParams"
            class="table table-hover">
            <thead>
                <tr>
                    <th data-field="couple_title" data-sortable="true" data-formatter="eventNameFormatter">Evento</th>
                    <th data-field="client_name" data-sortable="true">Cliente</th>
                    <th data-field="event_date_start" data-formatter="dateFormatter" data-sortable="true">Fecha</th>
                    <th data-field="guest_count" data-align="center">Invitados</th>
                    <th data-field="confirmed_count" data-align="center" data-formatter="confirmedFormatter">Confirmados</th>
                    <th data-field="service_status" data-formatter="serviceStatusFormatter" data-align="center">Estado</th>
                    <th data-field="id" data-formatter="actionsFormatter" data-align="right">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentFilter = '';

// Parámetros de consulta con filtro
function queryParams(params) {
    params.service_status = currentFilter;
    return params;
}

// Formatear nombre del evento con slug
function eventNameFormatter(value, row) {
    return `
        <div>
            <div class="fw-semibold">${value}</div>
            <small class="text-muted">
                <i class="bi bi-link-45deg"></i> /${row.slug}
            </small>
        </div>
    `;
}

// Formatear confirmados con barra de progreso
function confirmedFormatter(value, row) {
    if (row.guest_count === 0) return '-';
    const percentage = Math.round((value / row.guest_count) * 100);
    return `
        <div class="d-flex align-items-center gap-2">
            <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                <div class="progress-bar bg-success" style="width: ${percentage}%"></div>
            </div>
            <small>${value}</small>
        </div>
    `;
}

// Formatear estado del servicio
function serviceStatusFormatter(value, row) {
    const statusMap = {
        'active': { class: 'status-active', label: 'Activo' },
        'suspended': { class: 'status-inactive', label: 'Suspendido' },
        'archived': { class: 'status-inactive', label: 'Archivado' },
        'draft': { class: 'status-draft', label: 'Borrador' }
    };
    const status = statusMap[value] || { class: 'status-draft', label: value };
    return `<span class="status-badge ${status.class}">${status.label}</span>`;
}

// Formatear acciones
function actionsFormatter(value, row) {
    return `
        <div class="action-buttons">
            <a href="${BASE_URL}i/${row.slug}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver invitación">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
            <a href="${BASE_URL}admin/events/view/${row.id}" class="btn btn-sm btn-outline-secondary" title="Detalles">
                <i class="bi bi-eye"></i>
            </a>
            <a href="${BASE_URL}admin/events/edit/${row.id}" class="btn btn-sm btn-outline-primary" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEventSwal('${row.id}')" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

// Filtros de estado
$(document).on('click', '[data-filter]', function() {
    $('.btn-group [data-filter]').removeClass('active');
    $(this).addClass('active');
    currentFilter = $(this).data('filter');
    $('#eventsTable').bootstrapTable('refresh');
});

function deleteEventSwal(eventId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`${BASE_URL}admin/events/delete/${eventId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#eventsTable').bootstrapTable('refresh');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
            });
        }
    });
}
</script>
<?= $this->endSection() ?>

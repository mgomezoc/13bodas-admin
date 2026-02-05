<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Leads<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Leads</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Leads</h1>
        <p class="page-subtitle">Gestiona los leads captados desde el sitio</p>
    </div>
    <a href="<?= base_url('admin/leads/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Lead
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label for="filterStatus" class="form-label small text-muted">Estado</label>
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="filterSource" class="form-label small text-muted">Origen</label>
                <select id="filterSource" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($sourceOptions as $source): ?>
                        <option value="<?= esc($source) ?>"><?= esc($source) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="filterDateFrom" class="form-label small text-muted">Desde</label>
                <input id="filterDateFrom" type="text" class="form-control form-control-sm datepicker" placeholder="YYYY-MM-DD">
            </div>
            <div class="col-12 col-md-3">
                <label for="filterDateTo" class="form-label small text-muted">Hasta</label>
                <input id="filterDateTo" type="text" class="form-control form-control-sm datepicker" placeholder="YYYY-MM-DD">
            </div>
        </div>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFilters">
                <i class="bi bi-x-circle me-1"></i>Limpiar filtros
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table
            id="leadsTable"
            data-toggle="table"
            data-url="<?= base_url('admin/leads/list') ?>"
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
                    <th data-field="full_name" data-sortable="true" data-formatter="leadNameFormatter">Lead</th>
                    <th data-field="phone">Teléfono</th>
                    <th data-field="event_date" data-formatter="dateFormatter" data-sortable="true">Fecha Evento</th>
                    <th data-field="source" data-sortable="true">Origen</th>
                    <th data-field="status" data-formatter="leadStatusFormatter" data-align="center">Estado</th>
                    <th data-field="created_at" data-formatter="dateTimeFormatter" data-sortable="true">Creado</th>
                    <th data-field="id" data-formatter="actionsFormatter" data-align="right">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentStatus = '';
let currentSource = '';
let currentDateFrom = '';
let currentDateTo = '';

function queryParams(params) {
    if (currentStatus !== '') {
        params.status = currentStatus;
    }
    if (currentSource !== '') {
        params.source = currentSource;
    }
    if (currentDateFrom !== '') {
        params.date_from = currentDateFrom;
    }
    if (currentDateTo !== '') {
        params.date_to = currentDateTo;
    }
    return params;
}

function leadNameFormatter(value, row) {
    return `
        <div>
            <div class="fw-semibold">${value || 'Sin nombre'}</div>
            <small class="text-muted">${row.email || 'Sin email'}</small>
        </div>
    `;
}

function leadStatusFormatter(value) {
    const map = {
        new: { label: 'Nuevo', class: 'bg-warning' },
        contacted: { label: 'Contactado', class: 'bg-info' },
        qualified: { label: 'Calificado', class: 'bg-primary' },
        converted: { label: 'Convertido', class: 'bg-success' },
        lost: { label: 'Perdido', class: 'bg-danger' }
    };
    const info = map[value] || { label: value || 'Sin estado', class: 'bg-secondary' };
    return `<span class="badge ${info.class}">${info.label}</span>`;
}

function actionsFormatter(value, row) {
    return `
        <div class="action-buttons">
            <a href="${BASE_URL}admin/leads/edit/${row.id}" class="btn btn-sm btn-outline-primary" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteLead('${row.id}')" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

function refreshLeads() {
    $('#leadsTable').bootstrapTable('refresh');
}

$('#filterStatus').on('change', function() {
    currentStatus = $(this).val();
    refreshLeads();
});

$('#filterSource').on('change', function() {
    currentSource = $(this).val();
    refreshLeads();
});

$('#filterDateFrom').on('change', function() {
    currentDateFrom = $(this).val();
    refreshLeads();
});

$('#filterDateTo').on('change', function() {
    currentDateTo = $(this).val();
    refreshLeads();
});

$('#clearFilters').on('click', function() {
    currentStatus = '';
    currentSource = '';
    currentDateFrom = '';
    currentDateTo = '';
    $('#filterStatus').val('');
    $('#filterSource').val('');
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    refreshLeads();
});

function deleteLead(leadId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
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

            fetch(`${BASE_URL}admin/leads/delete/${leadId}`, {
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
                        refreshLeads();
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

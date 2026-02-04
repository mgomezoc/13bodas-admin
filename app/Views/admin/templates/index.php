<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Templates<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Templates</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Templates</h1>
        <p class="page-subtitle">Gestiona las plantillas de invitaciones</p>
    </div>
    <a href="<?= base_url('admin/templates/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Template
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="text-muted small">Filtros:</span>
            </div>
            <div class="col-auto">
                <div class="btn-group btn-group-sm me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-filter-active="">Todos</button>
                    <button type="button" class="btn btn-outline-success" data-filter-active="1">Activos</button>
                    <button type="button" class="btn btn-outline-danger" data-filter-active="0">Inactivos</button>
                </div>
            </div>
            <div class="col-auto">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-filter-public="">Todos</button>
                    <button type="button" class="btn btn-outline-info" data-filter-public="1">Públicos</button>
                    <button type="button" class="btn btn-outline-warning" data-filter-public="0">Privados</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table 
            id="templatesTable"
            data-toggle="table"
            data-url="<?= base_url('admin/templates/list') ?>"
            data-pagination="true"
            data-page-size="15"
            data-page-list="[15, 30, 50, 100]"
            data-search="true"
            data-search-align="left"
            data-show-refresh="true"
            data-show-columns="true"
            data-sort-name="sort_order"
            data-sort-order="asc"
            data-locale="es-MX"
            data-response-handler="responseHandler"
            data-query-params="queryParams"
            class="table table-hover">
            <thead>
                <tr>
                    <th data-field="id" data-sortable="true" data-width="80">ID</th>
                    <th data-field="thumbnail_url" data-formatter="thumbnailFormatter" data-width="100">Preview</th>
                    <th data-field="name" data-sortable="true" data-formatter="nameFormatter">Nombre</th>
                    <th data-field="sort_order" data-sortable="true" data-align="center" data-width="100">Orden</th>
                    <th data-field="usage_count" data-align="center" data-width="100">Usos</th>
                    <th data-field="is_public" data-formatter="publicFormatter" data-align="center" data-width="100">Público</th>
                    <th data-field="is_active" data-formatter="activeFormatter" data-align="center" data-width="100">Estado</th>
                    <th data-field="id" data-formatter="actionsFormatter" data-align="right" data-width="150">Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentFilterActive = '';
let currentFilterPublic = '';

function queryParams(params) {
    if (currentFilterActive !== '') {
        params.is_active = currentFilterActive;
    }
    if (currentFilterPublic !== '') {
        params.is_public = currentFilterPublic;
    }
    return params;
}

function thumbnailFormatter(value, row) {
    if (value) {
        return `<img src="${value}" alt="${row.name}" class="img-thumbnail" style="max-width: 60px; max-height: 60px; object-fit: cover;">`;
    }
    return '<span class="text-muted small">Sin imagen</span>';
}

function nameFormatter(value, row) {
    return `
        <div>
            <div class="fw-semibold">${value}</div>
            <small class="text-muted">${row.code}</small>
        </div>
    `;
}

function publicFormatter(value, row) {
    const isPublic = parseInt(value);
    if (isPublic === 1) {
        return '<span class="badge bg-info">Público</span>';
    }
    return '<span class="badge bg-warning">Privado</span>';
}

function activeFormatter(value, row) {
    const isActive = parseInt(value);
    if (isActive === 1) {
        return '<span class="status-badge status-active">Activo</span>';
    }
    return '<span class="status-badge status-inactive">Inactivo</span>';
}

function actionsFormatter(value, row) {
    return `
        <div class="action-buttons">
            <a href="${BASE_URL}admin/templates/edit/${row.id}" class="btn btn-sm btn-outline-primary" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(${row.id})" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

$(document).on('click', '[data-filter-active]', function() {
    $('[data-filter-active]').removeClass('active');
    $(this).addClass('active');
    currentFilterActive = $(this).data('filter-active');
    $('#templatesTable').bootstrapTable('refresh');
});

$(document).on('click', '[data-filter-public]', function() {
    $('[data-filter-public]').removeClass('active');
    $(this).addClass('active');
    currentFilterPublic = $(this).data('filter-public');
    $('#templatesTable').bootstrapTable('refresh');
});

function deleteTemplate(templateId) {
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

            fetch(`${BASE_URL}admin/templates/delete/${templateId}`, {
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
                        $('#templatesTable').bootstrapTable('refresh');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'No se puede eliminar',
                        text: data.message
                    });
                }
            })
            .catch(error => {
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

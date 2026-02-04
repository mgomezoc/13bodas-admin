<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Usuarios<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Usuarios</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Usuarios</h1>
        <p class="page-subtitle">Gestiona los usuarios del sistema</p>
    </div>
    <a href="<?= base_url('admin/users/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nuevo Usuario
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-auto">
                <span class="text-muted small">Filtrar por estado:</span>
            </div>
            <div class="col-auto">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-filter="">Todos</button>
                    <button type="button" class="btn btn-outline-success" data-filter="1">Activos</button>
                    <button type="button" class="btn btn-outline-danger" data-filter="0">Inactivos</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table 
            id="usersTable"
            data-toggle="table"
            data-url="<?= base_url('admin/users/list') ?>"
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
                    <th data-field="full_name" data-sortable="true" data-formatter="userNameFormatter">Usuario</th>
                    <th data-field="email" data-sortable="true">Email</th>
                    <th data-field="phone">Teléfono</th>
                    <th data-field="role_names" data-formatter="rolesFormatter">Roles</th>
                    <th data-field="is_active" data-formatter="statusFormatter" data-align="center">Estado</th>
                    <th data-field="last_login_at" data-formatter="dateFormatter" data-sortable="true">Último Acceso</th>
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
let currentFilter = '';

function queryParams(params) {
    if (currentFilter !== '') {
        params.is_active = currentFilter;
    }
    return params;
}

function userNameFormatter(value, row) {
    return `
        <div>
            <div class="fw-semibold">${value || 'Sin nombre'}</div>
            <small class="text-muted">ID: ${row.id.substring(0, 8)}</small>
        </div>
    `;
}

function rolesFormatter(value, row) {
    if (!value) return '<span class="badge bg-secondary">Sin rol</span>';
    const roles = value.split(',');
    return roles.map(role => {
        const badgeClass = role === 'superadmin' ? 'bg-danger' : 
                          role === 'admin' ? 'bg-warning' : 'bg-info';
        return `<span class="badge ${badgeClass}">${role}</span>`;
    }).join(' ');
}

function statusFormatter(value, row) {
    const status = parseInt(value);
    if (status === 1) {
        return '<span class="status-badge status-active">Activo</span>';
    }
    return '<span class="status-badge status-inactive">Inactivo</span>';
}

function actionsFormatter(value, row) {
    const statusBtn = parseInt(row.is_active) === 1 
        ? `<button class="btn btn-sm btn-outline-warning" onclick="toggleStatus('${row.id}')" title="Desactivar">
            <i class="bi bi-toggle-on"></i>
           </button>`
        : `<button class="btn btn-sm btn-outline-success" onclick="toggleStatus('${row.id}')" title="Activar">
            <i class="bi bi-toggle-off"></i>
           </button>`;

    return `
        <div class="action-buttons">
            ${statusBtn}
            <a href="${BASE_URL}admin/users/edit/${row.id}" class="btn btn-sm btn-outline-primary" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteUserSwal('${row.id}')" title="Eliminar">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
}

$(document).on('click', '[data-filter]', function() {
    $('.btn-group [data-filter]').removeClass('active');
    $(this).addClass('active');
    currentFilter = $(this).data('filter');
    $('#usersTable').bootstrapTable('refresh');
});

function toggleStatus(userId) {
    Swal.fire({
        title: '¿Cambiar estado?',
        text: "¿Deseas cambiar el estado de este usuario?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Actualizando...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`${BASE_URL}admin/users/toggle-status/${userId}`, {
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
                        title: '¡Actualizado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#usersTable').bootstrapTable('refresh');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
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

function deleteUserSwal(userId) {
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

            fetch(`${BASE_URL}admin/users/delete/${userId}`, {
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
                        $('#usersTable').bootstrapTable('refresh');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
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

<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Lista de Regalos<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Lista de Regalos</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Lista de Regalos</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?> • Mesa de regalos y fondos</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="bi bi-plus-lg me-2"></i>Agregar Regalo
    </button>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-gift"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $registryStats['total_items'] ?></div>
                <div class="stat-label">Total Regalos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check-circle"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $registryStats['claimed_items'] ?></div>
                <div class="stat-label">Reclamados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-info"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-content">
                <div class="stat-value">$<?= number_format($registryStats['total_value'], 0) ?></div>
                <div class="stat-label">Valor Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $registryStats['total_items'] - $registryStats['claimed_items'] ?></div>
                <div class="stat-label">Disponibles</div>
            </div>
        </div>
    </div>
</div>

<?= view('admin/events/partials/_event_navigation', ['active' => 'regalos', 'event_id' => $event['id']]) ?>

<!-- Lista de Regalos -->
<div id="registrySection">
<?php if (empty($items)): ?>
<div class="card">
    <div class="card-body">
        <div class="empty-state py-5">
            <i class="bi bi-gift empty-state-icon"></i>
            <h4 class="empty-state-title">Sin regalos todavía</h4>
            <p class="empty-state-text">
                Agrega los regalos que la pareja desea recibir.<br>
                También puedes crear "fondos" para viajes, casa, etc.
            </p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-lg me-2"></i>Agregar Primer Regalo
            </button>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row g-3" id="registryGrid">
    <?php foreach ($items as $item): ?>
    <div class="col-md-6 col-lg-4" data-id="<?= $item['id'] ?>">
        <div class="card h-100 <?= $item['is_claimed'] ? 'border-success' : '' ?>">
            <?php if ($item['image_url']): ?>
            <img src="<?= esc($item['image_url']) ?>" class="card-img-top" style="height: 150px; object-fit: cover;" alt="">
            <?php endif; ?>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0"><?= esc($item['name']) ?></h6>
                    <?php if ($item['is_claimed']): ?>
                    <span class="badge bg-success">Reclamado</span>
                    <?php endif; ?>
                </div>
                <?php if ($item['description']): ?>
                <p class="card-text small text-muted"><?= esc($item['description']) ?></p>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center">
                    <?php if ($item['is_fund']): ?>
                    <span class="text-info"><i class="bi bi-piggy-bank me-1"></i>Fondo</span>
                    <?php else: ?>
                    <span class="fw-bold">$<?= number_format($item['price'], 0) ?></span>
                    <?php endif; ?>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="toggleClaimed('<?= $item['id'] ?>')">
                                <i class="bi bi-<?= $item['is_claimed'] ? 'x-circle' : 'check-circle' ?> me-2"></i>
                                <?= $item['is_claimed'] ? 'Marcar Disponible' : 'Marcar Reclamado' ?>
                            </a></li>
                            <?php if ($item['external_url']): ?>
                            <li><a class="dropdown-item" href="<?= esc($item['external_url']) ?>" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-2"></i>Ver en Tienda
                            </a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger delete-item"
                                href="#"
                                data-id="<?= $item['id'] ?>"
                                data-name="<?= esc($item['name']) ?>"
                                data-endpoint="<?= base_url('admin/events/' . $event['id'] . '/registry/delete/' . $item['id']) ?>"
                                data-refresh-target="#registrySection">
                                <i class="bi bi-trash me-2"></i>Eliminar
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- Modal Agregar Regalo -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gift me-2"></i>Agregar Regalo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addItemForm"
                class="modal-ajax-form"
                data-refresh-target="#registrySection"
                action="<?= base_url('admin/events/' . $event['id'] . '/registry/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Regalo <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="Ej: Licuadora Oster">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Descripción opcional"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Precio Aproximado</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="price" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Enlace a Tienda</label>
                            <input type="url" name="external_url" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL de Imagen</label>
                        <input type="url" name="image_url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_fund" class="form-check-input" id="isFund">
                        <label class="form-check-label" for="isFund">
                            Es un fondo (luna de miel, casa, etc.)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
<script>
const eventId = '<?= $event['id'] ?>';

function toggleClaimed(itemId) {
    $.post(`${BASE_URL}admin/events/${eventId}/registry/toggle-claimed/${itemId}`)
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                refreshModuleSection('#registrySection');
            } else {
                Toast.fire({ icon: 'error', title: response.message });
            }
        });
}
</script>
<?= $this->endSection() ?>

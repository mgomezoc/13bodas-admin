<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Lista de Regalos<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><?= esc($event['couple_title']) ?></a></li>
        <li class="breadcrumb-item active">Lista de Regalos</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Lista de Regalos</h1>
        <p class="page-subtitle"><?= esc($event['couple_title']) ?></p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="bi bi-plus-lg me-2"></i>Agregar Artículo
    </button>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/edit/' . $event['id']) ?>"><i class="bi bi-info-circle me-1"></i>Información</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/guests') ?>"><i class="bi bi-people me-1"></i>Invitados</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/rsvp') ?>"><i class="bi bi-check2-square me-1"></i>RSVPs</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/events/' . $event['id'] . '/gallery') ?>"><i class="bi bi-images me-1"></i>Galería</a></li>
    <li class="nav-item"><button class="nav-link active" type="button"><i class="bi bi-gift me-1"></i>Regalos</button></li>
</ul>

<!-- Stats -->
<?php if ($summary): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-info"><i class="bi bi-gift"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $summary['total_items'] ?></div>
                <div class="stat-label">Artículos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="bi bi-check2-all"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $summary['claimed_items'] ?></div>
                <div class="stat-label">Reclamados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary"><i class="bi bi-piggy-bank"></i></div>
            <div class="stat-content">
                <div class="stat-value"><?= $summary['total_funds'] ?></div>
                <div class="stat-label">Fondos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="bi bi-cash-stack"></i></div>
            <div class="stat-content">
                <div class="stat-value">$<?= number_format($summary['funds_collected'] ?? 0, 0) ?></div>
                <div class="stat-label">Recaudado</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Lista de Artículos -->
<div class="row g-4" id="registryItems">
    <?php if (empty($items)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-gift text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">Sin artículos aún</h5>
                    <p class="text-muted">Agrega artículos o fondos monetarios a tu lista de regalos</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-lg me-2"></i>Agregar Primer Artículo
                    </button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <?php if ($item['image_url']): ?>
                        <img src="<?= esc($item['image_url']) ?>" class="card-img-top" alt="<?= esc($item['name']) ?>" style="height: 180px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="bi bi-<?= $item['is_fund'] ? 'piggy-bank' : 'gift' ?> text-muted" style="font-size: 3rem;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= esc($item['name']) ?></h5>
                        <?php if ($item['description']): ?>
                            <p class="card-text small text-muted"><?= esc($item['description']) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($item['is_fund']): ?>
                            <!-- Fondo monetario -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Recaudado</span>
                                    <span>$<?= number_format($item['amount_collected'], 0) ?> / $<?= number_format($item['goal_amount'], 0) ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <?php $percent = $item['goal_amount'] > 0 ? min(100, ($item['amount_collected'] / $item['goal_amount']) * 100) : 0; ?>
                                    <div class="progress-bar bg-success" style="width: <?= $percent ?>%"></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Artículo físico -->
                            <?php if ($item['price']): ?>
                                <p class="card-text"><strong>$<?= number_format($item['price'], 0) ?></strong></p>
                            <?php endif; ?>
                            <?php if ($item['is_claimed']): ?>
                                <span class="badge bg-success"><i class="bi bi-check me-1"></i>Reclamado</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-2">
                            <?php if ($item['external_url']): ?>
                                <a href="<?= esc($item['external_url']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-primary flex-fill" onclick="editItem(<?= $item['id'] ?>, <?= htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8') ?>)">
                                <i class="bi bi-pencil me-1"></i>Editar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?= $item['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal Agregar Artículo -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addItemForm">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Artículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="item_type" id="typeProduct" value="product" checked>
                            <label class="form-check-label" for="typeProduct">Artículo</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="item_type" id="typeFund" value="fund">
                            <label class="form-check-label" for="typeFund">Fondo Monetario</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3 product-field">
                        <label class="form-label">Precio (opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="price" class="form-control" min="0" step="0.01">
                        </div>
                    </div>
                    
                    <div class="mb-3 fund-field" style="display: none;">
                        <label class="form-label">Meta a Recaudar</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="goal_amount" class="form-control" min="0" step="100">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL Externa (tienda, etc.)</label>
                        <input type="url" name="external_url" class="form-control" placeholder="https://...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL de Imagen</label>
                        <input type="url" name="image_url" class="form-control" placeholder="https://...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Artículo -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editItemForm">
                <input type="hidden" name="item_id" id="editItemId">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Artículo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3" id="editPriceField">
                        <label class="form-label">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="price" id="editPrice" class="form-control" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3" id="editGoalField" style="display: none;">
                        <label class="form-label">Meta a Recaudar</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="goal_amount" id="editGoalAmount" class="form-control" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL Externa</label>
                        <input type="url" name="external_url" id="editExternalUrl" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">URL de Imagen</label>
                        <input type="url" name="image_url" id="editImageUrl" class="form-control">
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_visible" id="editIsVisible" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="editIsVisible">Visible en la invitación</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const eventId = '<?= $event['id'] ?>';

// Toggle entre artículo y fondo
$('input[name="item_type"]').on('change', function() {
    if ($(this).val() === 'fund') {
        $('.product-field').hide();
        $('.fund-field').show();
    } else {
        $('.product-field').show();
        $('.fund-field').hide();
    }
});

// Agregar artículo
$('#addItemForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const isFund = $('input[name="item_type"]:checked').val() === 'fund' ? 1 : 0;
    
    $.post(`${BASE_URL}admin/events/${eventId}/registry/store`, formData + `&is_fund=${isFund}`)
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                location.reload();
            } else {
                Toast.fire({ icon: 'error', title: response.message });
            }
        });
});

// Editar artículo
function editItem(itemId, item) {
    $('#editItemId').val(itemId);
    $('#editName').val(item.name);
    $('#editDescription').val(item.description);
    $('#editPrice').val(item.price);
    $('#editGoalAmount').val(item.goal_amount);
    $('#editExternalUrl').val(item.external_url);
    $('#editImageUrl').val(item.image_url);
    $('#editIsVisible').prop('checked', item.is_visible == 1);
    
    if (item.is_fund == 1) {
        $('#editPriceField').hide();
        $('#editGoalField').show();
    } else {
        $('#editPriceField').show();
        $('#editGoalField').hide();
    }
    
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

$('#editItemForm').on('submit', function(e) {
    e.preventDefault();
    
    const itemId = $('#editItemId').val();
    const formData = $(this).serialize();
    
    $.post(`${BASE_URL}admin/events/${eventId}/registry/update/${itemId}`, formData)
        .done(function(response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                location.reload();
            } else {
                Toast.fire({ icon: 'error', title: response.message });
            }
        });
});

// Eliminar artículo
function deleteItem(itemId) {
    Swal.fire({
        title: '¿Eliminar artículo?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`${BASE_URL}admin/events/${eventId}/registry/delete/${itemId}`)
                .done(function(response) {
                    if (response.success) {
                        Toast.fire({ icon: 'success', title: response.message });
                        location.reload();
                    } else {
                        Toast.fire({ icon: 'error', title: response.message });
                    }
                });
        }
    });
}
</script>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Detalle de Cliente<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/clients') ?>">Clientes</a></li>
        <li class="breadcrumb-item active"><?= esc($client['full_name']) ?></li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= esc($client['full_name']) ?></h1>
        <p class="page-subtitle">
            <?= esc($client['email']) ?>
            <?php if (!$client['is_active']): ?>
                <span class="badge bg-danger ms-2">Inactivo</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/clients/edit/' . $client['id']) ?>" class="btn btn-primary">
            <i class="bi bi-pencil me-2"></i>Editar
        </a>
        <a href="<?= base_url('admin/events/create?client_id=' . $client['id']) ?>" class="btn btn-success">
            <i class="bi bi-plus-lg me-2"></i>Crear Evento
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Información del cliente -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-person-circle me-2"></i>Información
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Teléfono</dt>
                    <dd class="col-sm-8"><?= $client['phone'] ? esc($client['phone']) : '<span class="text-muted">-</span>' ?></dd>
                    
                    <dt class="col-sm-4">Empresa</dt>
                    <dd class="col-sm-8"><?= $client['company_name'] ? esc($client['company_name']) : '<span class="text-muted">-</span>' ?></dd>
                    
                    <dt class="col-sm-4">Creado</dt>
                    <dd class="col-sm-8"><?= date('d/m/Y', strtotime($client['created_at'])) ?></dd>
                    
                    <dt class="col-sm-4">Último Acceso</dt>
                    <dd class="col-sm-8">
                        <?= $client['last_login_at'] 
                            ? date('d/m/Y H:i', strtotime($client['last_login_at'])) 
                            : '<span class="text-muted">Nunca</span>' 
                        ?>
                    </dd>
                </dl>
                
                <?php if ($client['notes']): ?>
                    <hr>
                    <h6 class="small text-muted">Notas Internas:</h6>
                    <p class="mb-0 small"><?= nl2br(esc($client['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Eventos del cliente -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-heart me-2"></i>Eventos (<?= count($client['events']) ?>)</span>
                <a href="<?= base_url('admin/events/create?client_id=' . $client['id']) ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($client['events'])): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-calendar-x empty-state-icon"></i>
                        <p class="text-muted mb-3">Este cliente no tiene eventos asignados</p>
                        <a href="<?= base_url('admin/events/create?client_id=' . $client['id']) ?>" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Crear Evento
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($client['events'] as $event): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($event['couple_title']) ?></div>
                                        <small class="text-muted">/<?= esc($event['slug']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($event['event_date_start'])) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match($event['service_status']) {
                                            'pending' => 'status-pending',
                                            'active' => 'status-active',
                                            'completed' => 'status-active',
                                            'cancelled' => 'status-inactive',
                                            default => 'status-draft'
                                        };
                                        $statusLabel = match($event['service_status']) {
                                            'pending' => 'Pendiente',
                                            'active' => 'Activo',
                                            'completed' => 'Completado',
                                            'cancelled' => 'Cancelado',
                                            default => $event['service_status']
                                        };
                                        ?>
                                        <span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="<?= base_url('i/' . $event['slug']) ?>" 
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="Ver invitación">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                            <a href="<?= base_url('admin/events/edit/' . $event['id']) ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

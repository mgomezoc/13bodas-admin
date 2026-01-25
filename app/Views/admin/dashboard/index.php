<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Resumen general de la plataforma</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['total_clients'] ?></div>
                <div class="stat-label">Clientes</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="bi bi-calendar-heart"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['active_events'] ?></div>
                <div class="stat-label">Eventos Activos</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="bi bi-envelope-paper"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['new_leads'] ?></div>
                <div class="stat-label">Leads Nuevos</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $stats['leads_this_month'] ?></div>
                <div class="stat-label">Leads Este Mes</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Próximos Eventos -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event me-2"></i>Próximos Eventos</span>
                <a href="<?= base_url('admin/events') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcoming_events)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-calendar-x empty-state-icon"></i>
                        <p class="text-muted mb-0">No hay eventos próximos</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach ($upcoming_events as $event): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($event['couple_title']) ?></div>
                                        <small class="text-muted"><?= esc($event['client_name']) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?= date('d M Y', strtotime($event['event_date_start'])) ?>
                                        </span>
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

    <!-- Leads Recientes -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-envelope me-2"></i>Leads Recientes</span>
                <a href="<?= base_url('admin/leads') ?>" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_leads)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-envelope-x empty-state-icon"></i>
                        <p class="text-muted mb-0">No hay leads recientes</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach ($recent_leads as $lead): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($lead['full_name']) ?></div>
                                        <small class="text-muted"><?= esc($lead['email']) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <?php
                                        $statusClass = match($lead['status']) {
                                            'new' => 'bg-warning',
                                            'contacted' => 'bg-info',
                                            'qualified' => 'bg-primary',
                                            'converted' => 'bg-success',
                                            'lost' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $statusLabel = match($lead['status']) {
                                            'new' => 'Nuevo',
                                            'contacted' => 'Contactado',
                                            'qualified' => 'Calificado',
                                            'converted' => 'Convertido',
                                            'lost' => 'Perdido',
                                            default => $lead['status']
                                        };
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
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

<!-- Eventos Recientes -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Eventos Creados Recientemente</span>
                <a href="<?= base_url('admin/events/create') ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo Evento
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_events)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-calendar-plus empty-state-icon"></i>
                        <p class="text-muted mb-0">No hay eventos aún</p>
                        <a href="<?= base_url('admin/events/create') ?>" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-lg me-1"></i>Crear primer evento
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Cliente</th>
                                    <th>Fecha del Evento</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_events as $event): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($event['couple_title']) ?></div>
                                        <small class="text-muted">/<?= esc($event['slug']) ?></small>
                                    </td>
                                    <td><?= esc($event['client_name']) ?></td>
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
                                            <a href="<?= base_url('admin/events/view/' . $event['id']) ?>" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="Ver">
                                                <i class="bi bi-eye"></i>
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

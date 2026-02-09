<?php declare(strict_types=1); ?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/admin/css/events.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item active">Historia</li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('admin/events/partials/_event_navigation', ['active' => 'timeline', 'event_id' => $event['id']]) ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= esc($pageTitle) ?></h1>
        <p class="page-subtitle">Gestiona los hitos de la historia del evento</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/events/edit/' . $event['id']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver al Evento
        </a>
        <a href="<?= url_to('admin.timeline.new', $event['id']) ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nuevo Hito
        </a>
    </div>
</div>


<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Año</th>
                        <th>Título</th>
                        <th>Descripción</th>
                        <th>Imagen</th>
                        <th>Orden</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($item['year'] ?? '') ?></td>
                                <td><?= esc($item['title'] ?? '') ?></td>
                                <td class="text-muted">
                                    <?= esc(mb_strimwidth((string) ($item['description'] ?? ''), 0, 120, '...')) ?>
                                </td>
                                <td>
                                    <?php if (!empty($item['image_url'])): ?>
                                        <?php $imageUrl = str_starts_with($item['image_url'], 'http') ? $item['image_url'] : base_url($item['image_url']); ?>
                                        <a href="<?= esc($imageUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                                            <i class="bi bi-image me-1"></i>Ver imagen
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($item['sort_order'] ?? 0)) ?></td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="<?= url_to('admin.timeline.edit', $event['id'], $item['id']) ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger delete-item"
                                            data-id="<?= $item['id'] ?>"
                                            data-name="<?= esc($item['title'] ?? 'este hito') ?>"
                                            data-endpoint="<?= url_to('admin.timeline.delete', $event['id'], $item['id']) ?>"
                                            title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No hay hitos registrados todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/admin/js/events-crud.js') ?>"></script>
<?= $this->endSection() ?>

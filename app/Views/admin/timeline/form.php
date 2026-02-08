<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?><?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/events') ?>">Eventos</a></li>
        <li class="breadcrumb-item"><a href="<?= url_to('admin.timeline.index', $event['id']) ?>">Historia</a></li>
        <li class="breadcrumb-item active"><?= esc($item ? 'Editar' : 'Nuevo') ?></li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= esc($pageTitle) ?></h1>
        <p class="page-subtitle">
            <?= esc($item ? 'Actualiza el hito de la historia.' : 'Agrega un nuevo hito a la historia del evento.') ?>
        </p>
    </div>
    <a href="<?= url_to('admin.timeline.index', $event['id']) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= $item
                    ? url_to('admin.timeline.update', $event['id'], $item['id'])
                    : url_to('admin.timeline.create', $event['id']) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="year" class="form-label">Año <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>"
                            id="year"
                            name="year"
                            value="<?= esc(old('year', $item['year'] ?? '')) ?>"
                            required>
                        <?php if (isset($errors['year'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['year']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Título <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                            id="title"
                            name="title"
                            value="<?= esc(old('title', $item['title'] ?? '')) ?>"
                            required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['title']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea
                            class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                            id="description"
                            name="description"
                            rows="5"><?= esc(old('description', $item['description'] ?? '')) ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['description']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="image_url" class="form-label">URL de imagen</label>
                        <input
                            type="url"
                            class="form-control <?= isset($errors['image_url']) ? 'is-invalid' : '' ?>"
                            id="image_url"
                            name="image_url"
                            value="<?= esc(old('image_url', $item['image_url'] ?? '')) ?>">
                        <?php if (isset($errors['image_url'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['image_url']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Orden</label>
                        <input
                            type="number"
                            class="form-control <?= isset($errors['sort_order']) ? 'is-invalid' : '' ?>"
                            id="sort_order"
                            name="sort_order"
                            value="<?= esc(old('sort_order', (string) ($item['sort_order'] ?? 0))) ?>">
                        <?php if (isset($errors['sort_order'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['sort_order']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i><?= esc($item ? 'Actualizar' : 'Crear') ?> Hito
                        </button>
                        <a href="<?= url_to('admin.timeline.index', $event['id']) ?>" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Evento</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <small class="text-muted">Título:</small><br>
                        <span class="small"><?= esc($event['couple_title'] ?? '') ?></span>
                    </li>
                    <li class="mb-2">
                        <small class="text-muted">Slug:</small><br>
                        <span class="small"><?= esc($event['slug'] ?? '') ?></span>
                    </li>
                    <li>
                        <small class="text-muted">ID:</small><br>
                        <code class="small"><?= esc($event['id'] ?? '') ?></code>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

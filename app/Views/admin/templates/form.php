<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= $pageTitle ?><?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('admin/templates') ?>">Templates</a></li>
        <li class="breadcrumb-item active"><?= $template ? 'Editar' : 'Nuevo' ?></li>
    </ol>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= $pageTitle ?></h1>
        <p class="page-subtitle"><?= $template ? 'Actualiza la información del template' : 'Crea un nuevo template de invitación' ?></p>
    </div>
    <a href="<?= base_url('admin/templates') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= base_url('admin/templates/save' . ($template ? '/' . $template['id'] : '')) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="code" class="form-label">Código <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control <?= isset($errors['code']) ? 'is-invalid' : '' ?>" 
                            id="code" 
                            name="code" 
                            value="<?= old('code', $template['code'] ?? '') ?>"
                            required>
                        <?php if (isset($errors['code'])): ?>
                            <div class="invalid-feedback"><?= $errors['code'] ?></div>
                        <?php else: ?>
                            <small class="form-text text-muted">Identificador único (ej: classic-gold, modern-floral)</small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                            id="name" 
                            name="name" 
                            value="<?= old('name', $template['name'] ?? '') ?>"
                            required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?= $errors['name'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea 
                            class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                            id="description" 
                            name="description" 
                            rows="3"><?= old('description', $template['description'] ?? '') ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback"><?= $errors['description'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="preview_url" class="form-label">URL Preview</label>
                            <input 
                                type="url" 
                                class="form-control <?= isset($errors['preview_url']) ? 'is-invalid' : '' ?>" 
                                id="preview_url" 
                                name="preview_url" 
                                value="<?= old('preview_url', $template['preview_url'] ?? '') ?>">
                            <?php if (isset($errors['preview_url'])): ?>
                                <div class="invalid-feedback"><?= $errors['preview_url'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="thumbnail_url" class="form-label">URL Thumbnail</label>
                            <input 
                                type="url" 
                                class="form-control <?= isset($errors['thumbnail_url']) ? 'is-invalid' : '' ?>" 
                                id="thumbnail_url" 
                                name="thumbnail_url" 
                                value="<?= old('thumbnail_url', $template['thumbnail_url'] ?? '') ?>">
                            <?php if (isset($errors['thumbnail_url'])): ?>
                                <div class="invalid-feedback"><?= $errors['thumbnail_url'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="schema_json" class="form-label">Schema JSON</label>
                        <textarea 
                            class="form-control font-monospace <?= isset($errors['schema_json']) ? 'is-invalid' : '' ?>" 
                            id="schema_json" 
                            name="schema_json" 
                            rows="10"
                            style="font-size: 13px;"><?= old('schema_json', $template['schema_json'] ?? '') ?></textarea>
                        <?php if (isset($errors['schema_json'])): ?>
                            <div class="invalid-feedback"><?= $errors['schema_json'] ?></div>
                        <?php else: ?>
                            <small class="form-text text-muted">Debe ser un formato JSON válido</small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="meta_json" class="form-label">Meta JSON</label>
                        <textarea 
                            class="form-control font-monospace <?= isset($errors['meta_json']) ? 'is-invalid' : '' ?>" 
                            id="meta_json" 
                            name="meta_json" 
                            rows="10"
                            style="font-size: 13px;"><?= old('meta_json', $template['meta_json'] ?? '') ?></textarea>
                        <?php if (isset($errors['meta_json'])): ?>
                            <div class="invalid-feedback"><?= $errors['meta_json'] ?></div>
                        <?php else: ?>
                            <small class="form-text text-muted">Debe ser un formato JSON válido</small>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sort_order" class="form-label">Orden</label>
                            <input 
                                type="number" 
                                class="form-control <?= isset($errors['sort_order']) ? 'is-invalid' : '' ?>" 
                                id="sort_order" 
                                name="sort_order" 
                                value="<?= old('sort_order', $template['sort_order'] ?? 0) ?>"
                                min="0">
                            <?php if (isset($errors['sort_order'])): ?>
                                <div class="invalid-feedback"><?= $errors['sort_order'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Público</label>
                            <div class="form-check form-switch">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    role="switch" 
                                    id="is_public_checkbox"
                                    <?= old('is_public', $template['is_public'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_public_checkbox">Visible públicamente</label>
                            </div>
                            <input type="hidden" name="is_public" id="is_public_hidden" value="<?= old('is_public', $template['is_public'] ?? 1) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    role="switch" 
                                    id="is_active_checkbox"
                                    <?= old('is_active', $template['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active_checkbox">Template activo</label>
                            </div>
                            <input type="hidden" name="is_active" id="is_active_hidden" value="<?= old('is_active', $template['is_active'] ?? 1) ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i><?= $template ? 'Actualizar' : 'Crear' ?> Template
                        </button>
                        <a href="<?= base_url('admin/templates') ?>" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($template): ?>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Información del Template</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <small class="text-muted">ID:</small><br>
                        <code class="small"><?= esc($template['id']) ?></code>
                    </li>
                    <li class="mb-2">
                        <small class="text-muted">Creado:</small><br>
                        <span class="small"><?= date('d/m/Y H:i', strtotime($template['created_at'])) ?></span>
                    </li>
                    <li class="mb-2">
                        <small class="text-muted">Actualizado:</small><br>
                        <span class="small"><?= date('d/m/Y H:i', strtotime($template['updated_at'])) ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <?php if (!empty($template['thumbnail_url'])): ?>
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">Preview</h6>
                <img src="<?= esc($template['thumbnail_url']) ?>" alt="<?= esc($template['name']) ?>" class="img-fluid rounded">
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('is_public_checkbox').addEventListener('change', function() {
    document.getElementById('is_public_hidden').value = this.checked ? '1' : '0';
});

document.getElementById('is_active_checkbox').addEventListener('change', function() {
    document.getElementById('is_active_hidden').value = this.checked ? '1' : '0';
});
</script>
<?= $this->endSection() ?>

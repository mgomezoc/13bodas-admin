<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Proyectos publicados<?= $this->endSection() ?>
<?= $this->section('description') ?>Proyectos y ejemplos reales de invitaciones digitales creadas en 13Bodas.<?= $this->endSection() ?>

<?= $this->section('header') ?><?= $this->include('partials/header') ?><?= $this->endSection() ?>
<?= $this->section('footer') ?><?= $this->include('partials/footer') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="projects section-padding" style="padding-top:140px;">
    <div class="container">
        <header class="section-header">
            <span class="section-tag">Portafolio</span>
            <h1 class="section-title">Todos los proyectos</h1>
        </header>
        <div class="projects-grid">
            <?php if (!empty($projects ?? [])): ?>
            <?php foreach (($projects ?? []) as $project): ?>
                <article class="project-card">
                    <a href="<?= site_url(route_to('projects.show', $project['slug'])) ?>" class="project-image-link">
                        <img src="<?= esc(base_url($project['cover_image'])) ?>" alt="<?= esc($project['cover_alt']) ?>" loading="lazy">
                    </a>
                    <div class="project-card-body">
                        <h2><?= esc($project['title']) ?></h2>
                        <a href="<?= site_url(route_to('projects.show', $project['slug'])) ?>" class="btn btn-ghost">Ver detalle</a>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php else: ?>
                <p class="section-description">No hay proyectos activos por el momento.</p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

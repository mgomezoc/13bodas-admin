<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($project['title']) ?><?= $this->endSection() ?>
<?= $this->section('description') ?>Detalle del proyecto <?= esc($project['title']) ?> y su galería de imágenes.<?= $this->endSection() ?>

<?= $this->section('header') ?><?= $this->include('partials/header') ?><?= $this->endSection() ?>
<?= $this->section('footer') ?><?= $this->include('partials/footer') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="projects section-padding" style="padding-top:140px;">
    <div class="container">
        <header class="section-header">
            <span class="section-tag">Detalle de proyecto</span>
            <h1 class="section-title"><?= esc($project['title']) ?></h1>
            <p class="section-description"><?= esc((string) ($project['venue_name'] ?: 'Proyecto publicado en 13Bodas')) ?></p>
        </header>

        <div class="projects-grid">
            <?php foreach (($project['gallery'] ?? []) as $image): ?>
                <figure class="project-card">
                    <img src="<?= esc(base_url($image['url'])) ?>" alt="<?= esc($image['alt_text'] ?: $project['title']) ?>" loading="lazy">
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

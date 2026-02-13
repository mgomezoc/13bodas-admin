<section id="proyectos" class="projects section-padding">
    <div class="container">
        <header class="section-header" data-aos="fade-up">
            <span class="section-tag">Proyectos reales</span>
            <h2 class="section-title">Explora invitaciones <span class="gradient-text">publicadas</span></h2>
            <p class="section-description">Cada proyecto carga la imagen correcta desde su galería.</p>
        </header>

        <div class="projects-grid">
            <?php if (!empty($featuredProjects ?? [])): ?>
            <?php foreach (($featuredProjects ?? []) as $project): ?>
                <article class="project-card" data-aos="fade-up">
                    <a href="<?= site_url(route_to('projects.show', $project['slug'])) ?>" class="project-image-link" aria-label="Ver proyecto <?= esc($project['title']) ?>">
                        <img src="<?= esc(base_url($project['cover_image'])) ?>" alt="<?= esc($project['cover_alt']) ?>" loading="lazy">
                    </a>
                    <div class="project-card-body">
                        <h3><?= esc($project['title']) ?></h3>
                        <p><?= esc((string) ($project['venue_name'] ?: 'Evento digital 13Bodas')) ?></p>
                        <a href="<?= site_url(route_to('projects.show', $project['slug'])) ?>" class="btn btn-ghost">Ver detalle</a>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php else: ?>
                <p class="section-description">Próximamente agregaremos más proyectos publicados.</p>
            <?php endif; ?>
        </div>

        <div class="projects-cta" data-aos="fade-up">
            <a href="<?= site_url(route_to('projects.index')) ?>" class="btn btn-secondary">Ver todos los proyectos</a>
        </div>
    </div>
</section>

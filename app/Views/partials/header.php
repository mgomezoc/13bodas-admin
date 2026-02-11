<header class="header" id="header">
    <nav class="nav container" aria-label="Navegación principal">
        <a href="<?= site_url(route_to('home')) ?>#inicio" class="nav-logo" aria-label="13Bodas - Inicio">
            <img
                src="<?= esc(base_url('img/13bodas-logo-blanco-transparente.png')) ?>"
                alt="13Bodas - Plataforma de invitaciones digitales"
                width="140"
                height="40"
                fetchpriority="high"
            >
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false">
            <span class="hamburger"></span>
        </button>

        <div class="nav-menu" id="navMenu">
            <ul class="nav-list">
                <li><a href="<?= site_url(route_to('home')) ?>#servicios" class="nav-link">Plataforma</a></li>
                <li><a href="<?= site_url(route_to('home')) ?>#magiccam" class="nav-link">MagicCam</a></li>
                <li><a href="<?= site_url(route_to('home')) ?>#paquetes" class="nav-link">Planes</a></li>
                <li><a href="<?= site_url(route_to('home')) ?>#faq" class="nav-link">FAQ</a></li>
                <li><a href="<?= site_url(route_to('register.index')) ?>" class="nav-cta">Crear cuenta gratis</a></li>
            </ul>
        </div>
    </nav>
</header>

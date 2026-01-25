<header class="header" id="header">
    <nav class="nav container" aria-label="Navegación principal">
        <a href="<?= base_url('#inicio') ?>" class="nav-logo" aria-label="13Bodas - Inicio">
            <img
                src="<?= base_url('img/13bodas-logo-blanco-transparente.png') ?>"
                alt="13Bodas Logo"
                width="140"
                height="40"
            >
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false">
            <span class="hamburger"></span>
        </button>

        <div class="nav-menu" id="navMenu">
            <ul class="nav-list">
                <li><a href="<?= base_url('#servicios') ?>" class="nav-link">Servicios</a></li>
                <li><a href="<?= base_url('#magiccam') ?>" class="nav-link">MagicCam</a></li>
                <li><a href="<?= base_url('#paquetes') ?>" class="nav-link">Paquetes</a></li>
                <li><a href="<?= base_url('#proceso') ?>" class="nav-link">Proceso</a></li>
                <li><a href="<?= base_url('#contacto') ?>" class="nav-cta">Cotizar Proyecto</a></li>
            </ul>
        </div>
    </nav>
</header>

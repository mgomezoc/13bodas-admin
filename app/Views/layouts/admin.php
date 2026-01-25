<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    
    <title><?= $this->renderSection('title') ?> | Admin 13Bodas</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('img/favicon.svg') ?>">
    <meta name="theme-color" content="#0a0510">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Admin Styles -->
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>

<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?= base_url('admin/dashboard') ?>" class="sidebar-logo">
                    <img src="<?= base_url('img/13bodas-logo-blanco-transparente.png') ?>" alt="13Bodas" width="120">
                </a>
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                    </svg>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="<?= base_url('admin/dashboard') ?>" class="nav-link <?= uri_string() === 'admin/dashboard' ? 'active' : '' ?>">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 1L1 8v11h6v-6h6v6h6V8L10 1z"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('admin/leads') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/leads') ? 'active' : '' ?>">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 10c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            <span>Leads</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('admin/proyectos') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/proyectos') ? 'active' : '' ?>">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2 4h4v4H2V4zm6 0h4v4H8V4zm6 0h4v4h-4V4zM2 10h4v4H2v-4zm6 0h4v4H8v-4zm6 0h4v4h-4v-4z"/>
                            </svg>
                            <span>Proyectos</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('admin/configuracion') ?>" class="nav-link <?= uri_string() === 'admin/configuracion' ? 'active' : '' ?>">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M17.14 10.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L1.71 6.87a.49.49 0 00.12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM10 13c-1.65 0-3-1.35-3-3s1.35-3 3-3 3 1.35 3 3-1.35 3-3 3z"/>
                            </svg>
                            <span>Configuración</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="<?= base_url('/') ?>" class="sidebar-link" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                        <path d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                        <path d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                    </svg>
                    Ver sitio
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Abrir menú">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                        </svg>
                    </button>
                    <h1 class="page-title"><?= $this->renderSection('page_title') ?></h1>
                </div>
                
                <div class="topbar-right">
                    <div class="user-dropdown">
                        <button class="user-btn" id="userDropdownBtn">
                            <span class="user-avatar">
                                <?= substr(session()->get('user_name') ?? 'A', 0, 1) ?>
                            </span>
                            <span class="user-name"><?= session()->get('user_name') ?? 'Admin' ?></span>
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <path d="M4 6l4 4 4-4H4z"/>
                            </svg>
                        </button>
                        <div class="dropdown-menu" id="userDropdownMenu">
                            <a href="<?= base_url('admin/perfil') ?>" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M8 8a3 3 0 100-6 3 3 0 000 6zm-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3z"/>
                                </svg>
                                Mi Perfil
                            </a>
                            <a href="<?= base_url('auth/logout') ?>" class="dropdown-item text-danger">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                    <path d="M10 12.5a.5.5 0 01-.5.5h-8a.5.5 0 01-.5-.5v-9a.5.5 0 01.5-.5h8a.5.5 0 01.5.5v2a.5.5 0 001 0v-2A1.5 1.5 0 009.5 2h-8A1.5 1.5 0 000 3.5v9A1.5 1.5 0 001.5 14h8a1.5 1.5 0 001.5-1.5v-2a.5.5 0 00-1 0v2z"/>
                                    <path d="M15.854 8.354a.5.5 0 000-.708l-3-3a.5.5 0 00-.708.708L14.293 7.5H5.5a.5.5 0 000 1h8.793l-2.147 2.146a.5.5 0 00.708.708l3-3z"/>
                                </svg>
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="admin-content">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-error">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <!-- Admin Scripts -->
    <script src="<?= base_url('js/admin.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>

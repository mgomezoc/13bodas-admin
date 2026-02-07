<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">

    <title><?= $this->renderSection('title') ?> | 13Bodas Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= base_url('img/favicon.svg') ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Flatpickr CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css" rel="stylesheet">

    <!-- Bootstrap Table CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.1/dist/bootstrap-table.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Admin Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">

    <?= $this->renderSection('styles') ?>
</head>

<body class="admin-body">
    <?php
    $session = session();
    $userRoles = $session->get('user_roles') ?? [];
    $isAdmin = in_array('superadmin', $userRoles) || in_array('admin', $userRoles);
    $isStaff = in_array('staff', $userRoles);
    $isClient = in_array('client', $userRoles);
    ?>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?= base_url('admin/dashboard') ?>" class="sidebar-logo">
                    <img src="<?= base_url('img/13bodas-logo-blanco-transparente.png') ?>" alt="13Bodas" height="35">
                </a>
                <button class="btn-close-sidebar d-lg-none" id="closeSidebar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <ul class="nav-menu">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="<?= base_url('admin/dashboard') ?>" class="nav-link <?= uri_string() === 'admin/dashboard' || uri_string() === 'admin' ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <?php if ($isAdmin || $isStaff): ?>
                        <!-- Clientes -->
                        <li class="nav-item">
                            <a href="<?= base_url('admin/clients') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/clients') ? 'active' : '' ?>">
                                <i class="bi bi-people"></i>
                                <span>Clientes</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Eventos -->
                    <li class="nav-item">
                        <a href="<?= base_url('admin/events') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/events') ? 'active' : '' ?>">
                            <i class="bi bi-calendar-heart"></i>
                            <span><?= $isClient ? 'Mi Evento' : 'Eventos' ?></span>
                        </a>
                    </li>

                    <?php if ($isAdmin || $isStaff): ?>
                        <!-- Leads -->
                        <li class="nav-item">
                            <a href="<?= base_url('admin/leads') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/leads') ? 'active' : '' ?>">
                                <i class="bi bi-envelope-paper"></i>
                                <span>Leads</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <!-- Templates -->
                        <li class="nav-item">
                            <a href="<?= base_url('admin/templates') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/templates') ? 'active' : '' ?>">
                                <i class="bi bi-palette"></i>
                                <span>Templates</span>
                            </a>
                        </li>

                        <!-- Usuarios -->
                        <li class="nav-item">
                            <a href="<?= base_url('admin/users') ?>" class="nav-link <?= str_starts_with(uri_string(), 'admin/users') ? 'active' : '' ?>">
                                <i class="bi bi-person-gear"></i>
                                <span>Usuarios</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a href="<?= base_url('/') ?>" target="_blank" class="sidebar-link">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Ver sitio</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="btn-toggle-sidebar" id="toggleSidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <nav aria-label="breadcrumb" class="d-none d-md-block">
                        <?= $this->renderSection('breadcrumb') ?>
                    </nav>
                </div>

                <div class="topbar-right">
                    <div class="dropdown">
                        <button class="btn btn-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="user-avatar">
                                <?= strtoupper(substr($session->get('user_name') ?? 'U', 0, 1)) ?>
                            </span>
                            <span class="user-name d-none d-md-inline"><?= esc($session->get('user_name')) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    <?= esc($session->get('user_email')) ?>
                                </span>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= base_url('admin/profile') ?>">
                                    <i class="bi bi-person me-2"></i>Mi Perfil
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= base_url('admin/logout') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="admin-content">
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <!-- Overlay for mobile sidebar -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery Validate -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/localization/messages_es.min.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>

    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <!-- Bootstrap Table -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.1/dist/locale/bootstrap-table-es-MX.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Configuración global
        const BASE_URL = '<?= base_url() ?>';
        const CSRF_TOKEN = '<?= csrf_hash() ?>';

        // Configuración de SweetAlert2
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        // Mostrar mensajes flash
        <?php if (session()->getFlashdata('success')): ?>
            Toast.fire({
                icon: 'success',
                title: '<?= esc(session()->getFlashdata('success')) ?>'
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            Toast.fire({
                icon: 'error',
                title: '<?= esc(session()->getFlashdata('error')) ?>'
            });
        <?php endif; ?>

        <?php if (session()->getFlashdata('warning')): ?>
            Toast.fire({
                icon: 'warning',
                title: '<?= esc(session()->getFlashdata('warning')) ?>'
            });
        <?php endif; ?>
    </script>

    <!-- Admin JS -->
    <script src="<?= base_url('js/admin.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>

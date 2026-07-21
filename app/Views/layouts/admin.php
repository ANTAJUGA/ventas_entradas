<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Panel administrativo del sistema de venta de entradas">
    <title><?= esc($titulo) ?> | TicketFlow</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>
    <?php $usuario = session('usuario') ?? $usuario; $rolSlug = $usuario['rol_slug'] ?? ''; ?>
    <div class="admin-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="<?= base_url('admin') ?>" aria-label="Ir al panel"><span class="brand-mark">T</span><span>TicketFlow</span></a>
            <nav class="sidebar-nav" aria-label="Navegación principal">
                <p class="nav-label">Gestión</p>
                <?php if (in_array($rolSlug, ['administrador', 'organizador'], true)): ?>
                    <a class="nav-link <?= $paginaActiva === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('admin') ?>"><span>◦</span> Resumen</a>
                    <a class="nav-link <?= $paginaActiva === 'eventos' ? 'active' : '' ?>" href="<?= base_url('admin/eventos') ?>"><span>◫</span> Eventos</a>
                    <a class="nav-link <?= $paginaActiva === 'funciones' ? 'active' : '' ?>" href="<?= base_url('admin/funciones') ?>"><span>◷</span> Funciones</a>
                    <a class="nav-link <?= $paginaActiva === 'tipos_entrada' ? 'active' : '' ?>" href="<?= base_url('admin/tipos-entrada') ?>"><span>◇</span> Tipos de entrada</a>
                    <a class="nav-link <?= $paginaActiva === 'descuentos' ? 'active' : '' ?>" href="<?= base_url('admin/descuentos') ?>"><span>%</span> Descuentos</a>
                <?php endif ?>
                <?php if (in_array($rolSlug, ['administrador', 'vendedor'], true)): ?>
                    <a class="nav-link <?= $paginaActiva === 'punto_venta' ? 'active' : '' ?>" href="<?= base_url('admin/ventas/nueva') ?>"><span>$</span> Punto de venta</a>
                    <a class="nav-link <?= $paginaActiva === 'ventas' ? 'active' : '' ?>" href="<?= base_url('admin/ventas') ?>"><span>≡</span> Historial de ventas</a>
                <?php endif ?>
                <?php if (in_array($rolSlug, ['administrador', 'control-acceso'], true)): ?>
                    <a class="nav-link <?= $paginaActiva === 'accesos' ? 'active' : '' ?>" href="<?= base_url('admin/accesos') ?>"><span>✓</span> Control de acceso</a>
                <?php endif ?>
                <?php if ($rolSlug === 'administrador'): ?>
                    <p class="nav-label">Administración</p>
                    <a class="nav-link <?= $paginaActiva === 'usuarios' ? 'active' : '' ?>" href="<?= base_url('admin/usuarios') ?>"><span>♙</span> Usuarios y roles</a>
                    <a class="nav-link disabled" href="#" aria-disabled="true"><span>⚙</span> Configuración</a>
                <?php endif ?>
            </nav>
            <div class="sidebar-footer">
                <span class="avatar"><?= esc(strtoupper(substr($usuario['nombre'], 0, 1))) ?></span>
                <div><strong><?= esc($usuario['nombre']) ?></strong><small><?= esc($usuario['rol']) ?></small></div>
                <form class="logout-form" action="<?= base_url('logout') ?>" method="post"><?= csrf_field() ?><button type="submit" title="Cerrar sesión" aria-label="Cerrar sesión">↪</button></form>
            </div>
        </aside>
        <div class="page">
            <header class="topbar">
                <button class="menu-button" id="menuButton" type="button" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">☰</button>
                <div class="topbar-title"><span>Administración</span><strong><?= esc($titulo) ?></strong></div>
                <div class="topbar-actions">
                    <?php if (in_array($rolSlug, ['administrador', 'organizador'], true)): ?><a class="primary-button" href="<?= base_url('admin/eventos/nuevo') ?>">+ Nuevo evento</a><?php endif ?>
                    <?php if ($rolSlug === 'vendedor'): ?><a class="primary-button" href="<?= base_url('admin/ventas/nueva') ?>">+ Nueva venta</a><?php endif ?>
                </div>
            </header>
            <main class="content"><?= $this->renderSection('contenido') ?></main>
        </div>
    </div>
    <script>
        const menuButton = document.getElementById('menuButton');
        const sidebar = document.getElementById('sidebar');
        menuButton?.addEventListener('click', () => {
            const isOpen = sidebar.classList.toggle('open');
            menuButton.setAttribute('aria-expanded', String(isOpen));
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo) ?> | TicketFlow</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/public.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/public.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/catalog.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/catalog.css') ?>">
</head>

<body>
    <header class="public-header"><a class="public-brand" href="<?= base_url('/') ?>"><b>T</b> TicketFlow</a>
        <nav><a href="<?= base_url('/') ?>">Eventos</a><?php $currentUser = session('usuario'); ?><?php if (session('isLoggedIn') && ($currentUser['rol_slug'] ?? '') === 'cliente'): ?><a href="<?= base_url('mi-cuenta/entradas') ?>">Mis entradas</a><span>Hola, <?= esc($currentUser['nombre']) ?></span>
            <form action="<?= base_url('salir') ?>" method="post"><?= csrf_field() ?><button type="submit">Salir</button></form><?php else: ?><a href="<?= base_url('ingresar') ?>">Ingresar</a><a class="nav-primary" href="<?= base_url('registro') ?>">Crear cuenta</a><?php endif ?>
        </nav>
    </header>
    <main><?= $this->renderSection('contenido') ?></main>
    <footer class="public-footer">TicketFlow · Sistema de venta de entradas</footer>
</body>

</html>
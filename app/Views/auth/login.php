<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Acceso administrativo de TicketFlow">
    <title><?= esc($titulo) ?> | TicketFlow</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card">
            <a class="brand" href="<?= base_url('/') ?>"><span>T</span> TicketFlow</a>
            <div class="auth-heading">
                <p>Panel administrativo</p>
                <h1>Bienvenido de nuevo</h1>
                <span>Ingresa tus credenciales para continuar.</span>
            </div>

            <?php if (session()->has('error')): ?><div class="alert error"><?= esc(session('error')) ?></div><?php endif ?>
            <?php if (session()->has('success')): ?><div class="alert success"><?= esc(session('success')) ?></div><?php endif ?>
            <?php $errors = session('errors') ?? []; ?>
            <?php if ($errors !== []): ?><div class="alert error"><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul></div><?php endif ?>

            <form action="<?= base_url('login') ?>" method="post">
                <?= csrf_field() ?>
                <label><span>Correo electrónico</span><input type="email" name="email" maxlength="190" autocomplete="username" required value="<?= esc(old('email')) ?>" placeholder="admin@ventas-entradas.local"></label>
                <label><span>Contraseña</span><input type="password" name="password" maxlength="255" autocomplete="current-password" required placeholder="Tu contraseña"></label>
                <button type="submit">Iniciar sesión</button>
            </form>

            <p class="auth-note">El acceso está reservado para el equipo de administración.</p>
        </section>
    </main>
</body>
</html>

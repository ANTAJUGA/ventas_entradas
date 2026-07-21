<?= $this->extend('layouts/public') ?><?= $this->section('contenido') ?><section class="customer-auth">
    <div class="customer-card">
        <p>Cuenta de cliente</p>
        <h1>Ingresa para comprar</h1><span>Accede a tus compras y entradas.</span><?php if (session()->has('error')): ?><div class="public-alert"><?= esc(session('error')) ?></div><?php endif ?><form action="<?= base_url('ingresar') ?>" method="post"><?= csrf_field() ?><label>Correo<input type="email" name="email" required autocomplete="username" value="<?= esc(old('email')) ?>"></label><label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label><button type="submit">Ingresar</button></form><small>¿No tienes cuenta? <a href="<?= base_url('registro') ?>">Regístrate aquí</a></small><a class="staff-link" href="<?= base_url('login') ?>">Acceso para personal administrativo</a>
    </div>
</section><?= $this->endSection() ?>
<?= $this->extend('layouts/admin') ?>
<?= $this->section('contenido') ?>
<section class="page-heading"><div><p class="eyebrow">Administración</p><h1>Usuarios y roles</h1><p class="muted">Controla las cuentas y permisos de acceso al sistema.</p></div><a class="primary-button" href="<?= base_url('admin/usuarios/nuevo') ?>">+ Crear usuario</a></section>
<?php foreach (['success', 'error', 'info'] as $type): ?><?php if (session()->has($type)): ?><div class="alert <?= esc($type) ?>"><?= esc(session($type)) ?></div><?php endif ?><?php endforeach ?>
<section class="panel"><div class="panel-heading"><div><h2>Usuarios registrados</h2><p><?= count($usuarios) ?> cuenta(s) encontrada(s)</p></div></div>
<?php if ($usuarios === []): ?><div class="empty-state"><span>♙</span><h2>No existen usuarios</h2><p>Crea la primera cuenta del equipo.</p></div><?php else: ?>
<div class="table-wrap"><table><thead><tr><th>Usuario</th><th>Rol</th><th>Teléfono</th><th>Último acceso</th><th>Estado</th><th class="actions-column">Acciones</th></tr></thead><tbody>
<?php foreach ($usuarios as $item): ?><tr>
    <td><strong><?= esc($item['nombres'] . ' ' . $item['apellidos']) ?><?= (int) $item['id'] === $usuarioActualId ? ' (tú)' : '' ?></strong><small><?= esc($item['email']) ?></small></td>
    <td><?= esc($item['rol_nombre']) ?></td><td><?= esc($item['telefono'] ?: '—') ?></td><td><?= $item['ultimo_acceso'] ? esc(date('d/m/Y H:i', strtotime($item['ultimo_acceso']))) : 'Nunca' ?></td>
    <td><span class="tag <?= esc($item['estado']) ?>"><?= esc(ucfirst($item['estado'])) ?></span></td>
    <td><div class="row-actions"><a class="secondary-button" href="<?= base_url('admin/usuarios/' . $item['id'] . '/editar') ?>">Editar</a><a class="secondary-button" href="<?= base_url('admin/usuarios/' . $item['id'] . '/password') ?>">Contraseña</a>
    <?php if ((int) $item['id'] !== $usuarioActualId): ?><form action="<?= base_url('admin/usuarios/' . $item['id'] . '/bloqueo') ?>" method="post"><?= csrf_field() ?><button class="text-button" type="submit"><?= $item['estado'] === 'bloqueado' ? 'Desbloquear' : 'Bloquear' ?></button></form><form action="<?= base_url('admin/usuarios/' . $item['id'] . '/eliminar') ?>" method="post" onsubmit="return confirm('¿Eliminar este usuario?');"><?= csrf_field() ?><button class="text-button danger" type="submit">Eliminar</button></form><?php endif ?>
    </div></td>
</tr><?php endforeach ?></tbody></table></div><?php endif ?>
</section>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>
<?= $this->section('contenido') ?>
<section class="page-heading">
    <div><p class="eyebrow">Promociones</p><h1>Descuentos</h1><p class="muted">Administra códigos promocionales globales o asociados a un evento.</p></div>
    <a class="primary-button" href="<?= base_url('admin/descuentos/nuevo') ?>">+ Crear descuento</a>
</section>
<?php foreach (['success', 'error', 'info'] as $messageType): ?>
    <?php if (session()->has($messageType)): ?><div class="alert <?= esc($messageType) ?>"><?= esc(session($messageType)) ?></div><?php endif ?>
<?php endforeach ?>
<section class="panel">
    <div class="panel-heading"><div><h2>Códigos promocionales</h2><p><?= count($descuentos) ?> registro(s) encontrados</p></div></div>
    <?php if ($descuentos === []): ?>
        <div class="empty-state"><span aria-hidden="true">%</span><h2>No existen descuentos</h2><p>Crea una promoción para comenzar.</p><a class="primary-button" href="<?= base_url('admin/descuentos/nuevo') ?>">Crear descuento</a></div>
    <?php else: ?>
        <div class="table-wrap"><table><thead><tr><th>Código</th><th>Aplicación</th><th>Beneficio</th><th>Vigencia</th><th>Usos</th><th>Estado</th><th class="actions-column">Acciones</th></tr></thead><tbody>
        <?php foreach ($descuentos as $descuento): ?><tr>
            <td><strong><?= esc($descuento['codigo']) ?></strong><small><?= esc($descuento['nombre']) ?></small></td>
            <td><?= esc($descuento['evento_nombre'] ?: 'Todos los eventos') ?></td>
            <td><strong><?= $descuento['tipo'] === 'porcentaje' ? esc(number_format((float) $descuento['valor'], 2)) . '%' : '$' . esc(number_format((float) $descuento['valor'], 2)) ?></strong><small>Compra mínima: $<?= esc(number_format((float) $descuento['compra_minima'], 2)) ?></small></td>
            <td><strong><?= esc(date('d/m/Y', strtotime($descuento['fecha_inicio']))) ?> – <?= esc(date('d/m/Y', strtotime($descuento['fecha_fin']))) ?></strong></td>
            <td><?= esc((string) $descuento['usos_actuales']) ?> / <?= $descuento['limite_usos'] === null ? '∞' : esc((string) $descuento['limite_usos']) ?></td>
            <td><span class="tag <?= $descuento['activo'] ? 'publicado' : 'finalizado' ?>"><?= $descuento['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
            <td><div class="row-actions"><a class="secondary-button" href="<?= base_url('admin/descuentos/' . $descuento['id'] . '/editar') ?>">Editar</a><form action="<?= base_url('admin/descuentos/' . $descuento['id'] . '/eliminar') ?>" method="post" onsubmit="return confirm('¿Eliminar este descuento?');"><?= csrf_field() ?><button class="text-button danger" type="submit">Eliminar</button></form></div></td>
        </tr><?php endforeach ?>
        </tbody></table></div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>

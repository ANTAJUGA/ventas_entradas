<?= $this->extend('layouts/admin') ?>
<?= $this->section('contenido') ?>
<section class="page-heading">
    <div><p class="eyebrow">Precios y localidades</p><h1>Tipos de entrada</h1><p class="muted">Administra cupos, precios y límites de compra para cada función.</p></div>
    <a class="secondary-button" href="<?= base_url('admin/funciones') ?>">Seleccionar función</a>
</section>
<?php foreach (['success', 'error', 'info'] as $messageType): ?>
    <?php if (session()->has($messageType)): ?><div class="alert <?= esc($messageType) ?>"><?= esc(session($messageType)) ?></div><?php endif ?>
<?php endforeach ?>
<section class="panel">
    <div class="panel-heading"><div><h2>Entradas configuradas</h2><p><?= count($tipos) ?> registro(s) encontrados</p></div></div>
    <?php if ($tipos === []): ?>
        <div class="empty-state"><span aria-hidden="true">◇</span><h2>No existen tipos de entrada</h2><p>Selecciona una función y agrega opciones como General o VIP.</p><a class="primary-button" href="<?= base_url('admin/funciones') ?>">Ir a funciones</a></div>
    <?php else: ?>
        <div class="table-wrap"><table><thead><tr><th>Tipo</th><th>Evento / función</th><th>Precio</th><th>Cupo</th><th>Límite</th><th>Estado</th><th class="actions-column">Acciones</th></tr></thead><tbody>
        <?php foreach ($tipos as $tipo): ?><tr>
            <td><strong><?= esc($tipo['nombre']) ?></strong><small><?= esc($tipo['descripcion'] ?: 'Sin descripción') ?></small></td>
            <td><strong><?= esc($tipo['evento_nombre']) ?></strong><small><?= esc($tipo['funcion_nombre'] ?: date('d/m/Y H:i', strtotime($tipo['fecha_inicio']))) ?></small></td>
            <td>$<?= esc(number_format((float) $tipo['precio'], 2)) ?></td><td><?= esc((string) $tipo['cupo']) ?> / <?= esc((string) $tipo['aforo_total']) ?></td><td><?= esc((string) $tipo['limite_por_compra']) ?> por compra</td>
            <td><span class="tag <?= $tipo['activo'] ? 'publicado' : 'finalizado' ?>"><?= $tipo['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
            <td><div class="row-actions"><a class="secondary-button" href="<?= base_url('admin/tipos-entrada/' . $tipo['id'] . '/editar') ?>">Editar</a><form action="<?= base_url('admin/tipos-entrada/' . $tipo['id'] . '/eliminar') ?>" method="post" onsubmit="return confirm('¿Eliminar este tipo de entrada?');"><?= csrf_field() ?><button class="text-button danger" type="submit">Eliminar</button></form></div></td>
        </tr><?php endforeach ?>
        </tbody></table></div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('contenido') ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Programación</p>
        <h1>Funciones</h1>
        <p class="muted">Administra fechas, recintos, periodos de venta y aforo.</p>
    </div>
    <a class="secondary-button" href="<?= base_url('admin/eventos') ?>">Seleccionar evento</a>
</section>

<?php foreach (['success', 'error', 'info'] as $messageType): ?>
    <?php if (session()->has($messageType)): ?>
        <div class="alert <?= esc($messageType) ?>"><?= esc(session($messageType)) ?></div>
    <?php endif ?>
<?php endforeach ?>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Funciones programadas</h2>
            <p><?= count($funciones) ?> registro(s) encontrados</p>
        </div>
    </div>

    <?php if ($funciones === []): ?>
        <div class="empty-state">
            <span aria-hidden="true">◷</span>
            <h2>No existen funciones</h2>
            <p>Entra al listado de eventos y selecciona “Nueva función”.</p>
            <a class="primary-button" href="<?= base_url('admin/eventos') ?>">Ir a eventos</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Evento / función</th>
                        <th>Fecha</th>
                        <th>Ubicación</th>
                        <th>Aforo</th>
                        <th>Estado</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($funciones as $funcion): ?>
                        <tr>
                            <td>
                                <strong><?= esc($funcion['evento_nombre']) ?></strong>
                                <small><?= esc($funcion['nombre'] ?: 'Función general') ?></small>
                            </td>
                            <td>
                                <strong><?= esc(date('d/m/Y', strtotime($funcion['fecha_inicio']))) ?></strong>
                                <small><?= esc(date('H:i', strtotime($funcion['fecha_inicio']))) ?></small>
                            </td>
                            <td>
                                <strong><?= esc($funcion['recinto']) ?></strong>
                                <small><?= esc($funcion['ciudad']) ?></small>
                            </td>
                            <td><?= esc((string) $funcion['aforo_total']) ?></td>
                            <td><span class="tag <?= esc($funcion['estado']) ?>"><?= esc(ucfirst(str_replace('_', ' ', $funcion['estado']))) ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="secondary-button" href="<?= base_url('admin/funciones/' . $funcion['id'] . '/editar') ?>">Editar</a>
                                    <a class="secondary-button" href="<?= base_url('admin/funciones/' . $funcion['id'] . '/tipos-entrada/nuevo') ?>">Nueva entrada</a>
                                    <form action="<?= base_url('admin/funciones/' . $funcion['id'] . '/eliminar') ?>" method="post" onsubmit="return confirm('¿Eliminar esta función?');">
                                        <?= csrf_field() ?>
                                        <button class="text-button danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>

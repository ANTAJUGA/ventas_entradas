<?= $this->extend('layouts/admin') ?>

<?= $this->section('contenido') ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Catálogo artístico</p>
        <h1>Artistas</h1>
        <p class="muted">Administra artistas de forma independiente. Su asociación con eventos se realizará en una etapa futura.</p>
    </div>
    <a class="primary-button" href="<?= base_url('admin/artistas/nuevo') ?>">+ Crear artista</a>
</section>

<?php foreach (['success', 'error', 'info'] as $messageType): ?>
    <?php if (session()->has($messageType)): ?>
        <div class="alert <?= esc($messageType) ?>"><?= esc(session($messageType)) ?></div>
    <?php endif ?>
<?php endforeach ?>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Artistas registrados</h2>
            <p><?= count($artistas) ?> registro(s) encontrados</p>
        </div>
    </div>

    <?php if ($artistas === []): ?>
        <div class="empty-state">
            <span aria-hidden="true">★</span>
            <h2>No existen artistas</h2>
            <p>Registra el primer artista del catálogo.</p>
            <a class="primary-button" href="<?= base_url('admin/artistas/nuevo') ?>">Crear artista</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Artista</th>
                        <th>Tipo</th>
                        <th>Género</th>
                        <th>País</th>
                        <th>Estado</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($artistas as $artista): ?>
                        <tr>
                            <td>
                                <strong><?= esc($artista['nombre_artistico']) ?></strong>
                                <small><?= esc($artista['nombre_real'] ?: 'Nombre real no registrado') ?></small>
                                <?php if ($artista['descripcion_resumen'] !== ''): ?>
                                    <small><?= esc($artista['descripcion_resumen']) ?></small>
                                <?php endif ?>
                            </td>
                            <td><?= esc($artista['tipo_etiqueta']) ?></td>
                            <td><?= esc($artista['genero'] ?: 'No registrado') ?></td>
                            <td><?= esc($artista['pais'] ?: 'No registrado') ?></td>
                            <td>
                                <span class="tag <?= $artista['estado'] === 'activo' ? 'publicado' : 'finalizado' ?>">
                                    <?= $artista['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="secondary-button" href="<?= base_url('admin/artistas/' . $artista['id'] . '/editar') ?>">Editar</a>
                                    <form action="<?= base_url('admin/artistas/' . $artista['id'] . '/eliminar') ?>" method="post" onsubmit="return confirm('¿Eliminar este artista?');">
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

<?= $this->extend('layouts/admin') ?>

<?= $this->section('contenido') ?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Gestión de cartelera</p>
        <h1>Eventos</h1>
        <p class="muted">Crea, edita y publica los eventos disponibles.</p>
    </div>
    <a class="primary-button" href="<?= base_url('admin/eventos/nuevo') ?>">+ Crear evento</a>
</section>

<?php foreach (['success', 'error', 'info'] as $messageType): ?>
    <?php if (session()->has($messageType)): ?>
        <div class="alert <?= esc($messageType) ?>"><?= esc(session($messageType)) ?></div>
    <?php endif ?>
<?php endforeach ?>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Todos los eventos</h2>
            <p><?= count($eventos) ?> registro(s) encontrados</p>
        </div>
    </div>

    <?php if ($eventos === []): ?>
        <div class="empty-state">
            <span aria-hidden="true">◫</span>
            <h2>Aún no existen eventos</h2>
            <p>Crea el primero para comenzar a configurar funciones y entradas.</p>
            <a class="primary-button" href="<?= base_url('admin/eventos/nuevo') ?>">Crear primer evento</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Organizador</th>
                        <th>Funciones</th>
                        <th>Estado</th>
                        <th class="actions-column">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td>
                                <strong><?= esc($evento['nombre']) ?></strong>
                                <small><?= esc($evento['categoria'] ?: 'Sin categoría') ?></small>
                            </td>
                            <td><?= esc($evento['organizador_nombre']) ?></td>
                            <td><?= esc((string) $evento['total_funciones']) ?></td>
                            <td><span class="tag <?= esc($evento['estado']) ?>"><?= esc(ucfirst($evento['estado'])) ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <a class="secondary-button" href="<?= base_url('admin/eventos/' . $evento['id'] . '/editar') ?>">Editar</a>
                                    <a class="secondary-button" href="<?= base_url('admin/eventos/' . $evento['id'] . '/funciones/nueva') ?>">Nueva función</a>
                                    <?php if ($evento['estado'] !== 'publicado'): ?>
                                        <form action="<?= base_url('admin/eventos/' . $evento['id'] . '/publicar') ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button class="text-button" type="submit">Publicar</button>
                                        </form>
                                    <?php endif ?>
                                    <form action="<?= base_url('admin/eventos/' . $evento['id'] . '/eliminar') ?>" method="post" onsubmit="return confirm('¿Eliminar este evento?');">
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

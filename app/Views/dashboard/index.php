<?= $this->extend('layouts/admin') ?>

<?= $this->section('contenido') ?>
<section class="welcome-row">
    <div>
        <p class="eyebrow"><?= esc($fechaActual) ?></p>
        <h1>Hola, <?= esc($usuario['nombre']) ?></h1>
        <p class="muted">Aquí tienes un resumen actualizado de tus eventos.</p>
    </div>
    <span class="status-badge"><i></i> Datos actualizados</span>
</section>

<section class="summary-grid" aria-label="Indicadores principales">
    <?php foreach ($resumen as $item): ?>
        <article class="summary-card <?= esc($item['tono']) ?>">
            <div class="summary-card-head">
                <span><?= esc($item['etiqueta']) ?></span>
                <span class="metric-icon" aria-hidden="true">◆</span>
            </div>
            <strong><?= esc((string) $item['valor']) ?></strong>
            <small><?= esc($item['detalle']) ?></small>
        </article>
    <?php endforeach ?>
</section>

<section class="dashboard-grid">
    <article class="panel events-panel">
        <div class="panel-heading">
            <div><h2>Próximas funciones</h2><p>Ocupación basada en entradas pagadas</p></div>
            <a href="<?= base_url('admin/funciones') ?>">Ver todas</a>
        </div>

        <?php if ($eventos === []): ?>
            <div class="empty-state compact"><span aria-hidden="true">◷</span><h2>No hay funciones próximas</h2><p>Crea un evento y programa su primera función.</p><a class="primary-button" href="<?= base_url('admin/eventos') ?>">Ir a eventos</a></div>
        <?php else: ?>
            <div class="table-wrap"><table><thead><tr><th>Evento</th><th>Ocupación</th><th>Estado</th></tr></thead><tbody>
                <?php foreach ($eventos as $evento): ?>
                    <?php $porcentaje = $evento['aforo'] > 0 ? min(100, (int) round(($evento['vendidas'] / $evento['aforo']) * 100)) : 0; ?>
                    <tr>
                        <td><strong><?= esc($evento['nombre']) ?></strong><small><?= esc($evento['funcion']) ?> · <?= esc($evento['fecha']) ?></small></td>
                        <td><div class="capacity-label"><span><?= esc((string) $evento['vendidas']) ?>/<?= esc((string) $evento['aforo']) ?></span><b><?= $porcentaje ?>%</b></div><div class="progress"><span style="width: <?= $porcentaje ?>%"></span></div></td>
                        <td><span class="tag <?= $evento['estado'] === 'Borrador' ? 'draft' : '' ?>"><?= esc($evento['estado']) ?></span></td>
                    </tr>
                <?php endforeach ?>
            </tbody></table></div>
        <?php endif ?>
    </article>

    <aside class="panel quick-panel">
        <div class="panel-heading"><div><h2>Acciones rápidas</h2><p>Accesos de administración</p></div></div>
        <a href="<?= base_url('admin/eventos/nuevo') ?>"><span>＋</span><div><strong>Crear evento</strong><small>Configura fechas y funciones</small></div><b>›</b></a>
        <a href="<?= base_url('admin/tipos-entrada') ?>"><span>◇</span><div><strong>Tipos de entrada</strong><small>General, VIP y cortesías</small></div><b>›</b></a>
        <a href="<?= base_url('admin/descuentos/nuevo') ?>"><span>%</span><div><strong>Nuevo descuento</strong><small>Promociones y condiciones</small></div><b>›</b></a>
        <a href="#"><span>♙</span><div><strong>Gestionar usuarios</strong><small>Próximo módulo</small></div><b>›</b></a>
    </aside>
</section>
<?= $this->endSection() ?>

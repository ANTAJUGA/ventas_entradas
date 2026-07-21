<?= $this->extend('layouts/public') ?><?= $this->section('contenido') ?>
<section class="event-hero">
    <div><a href="<?= base_url('/') ?>">← Volver a eventos</a>
        <p><?= esc($evento['categoria'] ?: 'Evento') ?></p>
        <h1><?= esc($evento['nombre']) ?></h1><span><?= esc($evento['descripcion'] ?: 'Selecciona una función para continuar.') ?></span>
    </div>
</section>
<section class="public-container narrow">
    <div class="section-title">
        <div>
            <p>Agenda</p>
            <h2>Funciones y entradas</h2>
        </div>
    </div>
    <?php if ($evento['funciones'] === []): ?><div class="public-empty">
            <h2>No hay funciones disponibles</h2>
        </div><?php else: ?><?php foreach ($evento['funciones'] as $funcion): ?><article class="function-card">
            <header>
                <div><small><?= esc(date('d/m/Y', strtotime($funcion['fecha_inicio']))) ?></small>
                    <h2><?= esc($funcion['nombre'] ?: 'Función general') ?></h2>
                    <p><?= esc(date('H:i', strtotime($funcion['fecha_inicio']))) ?> · <?= esc($funcion['recinto']) ?>, <?= esc($funcion['ciudad']) ?></p>
                </div><span>Aforo <?= esc((string) $funcion['aforo_total']) ?></span>
            </header>
            <div class="ticket-list"><?php foreach ($funcion['tipos'] as $tipo): ?><div>
                        <div><strong><?= esc($tipo['nombre']) ?></strong><small><?= esc($tipo['descripcion'] ?: 'Entrada para esta función') ?></small></div><b>$<?= esc(number_format((float) $tipo['precio'], 2)) ?></b><span><?= esc((string) $tipo['disponibles']) ?> disponibles</span><?php if (! $funcion['venta_disponible']): ?><em>Venta cerrada</em><?php elseif ($tipo['disponibles'] > 0): ?><a href="<?= base_url('comprar/' . $tipo['id']) ?>">Seleccionar</a><?php else: ?><em>Agotado</em><?php endif ?>
                    </div><?php endforeach ?></div>
        </article><?php endforeach ?><?php endif ?>
</section>
<?= $this->endSection() ?>

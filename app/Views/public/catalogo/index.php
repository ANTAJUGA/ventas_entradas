<?= $this->extend('layouts/public') ?><?= $this->section('contenido') ?>
<section class="hero">
    <p>Experiencias para recordar</p>
    <h1>Encuentra tu próximo evento</h1><span>Consulta funciones, precios y disponibilidad en tiempo real.</span>
</section>
<section class="public-container">
    <div class="section-title">
        <div>
            <p>Cartelera</p>
            <h2>Eventos disponibles</h2>
        </div><span><?= count($eventos) ?> evento(s)</span>
    </div>
    <?php if ($eventos === []): ?><div class="public-empty"><b>Próximamente</b>
            <h2>No hay eventos publicados</h2>
            <p>Vuelve pronto para conocer nuestra cartelera.</p>
        </div><?php else: ?><div class="event-grid"><?php foreach ($eventos as $evento): ?><article class="event-card">
                    <div class="event-cover"><?php if ($evento['imagen_url']): ?><img src="<?= esc($evento['imagen_url']) ?>" alt="Portada de <?= esc($evento['nombre']) ?>" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false"><span class="event-cover-fallback" hidden><?= esc(strtoupper(substr($evento['nombre'], 0, 1))) ?></span><?php else: ?><span><?= esc(strtoupper(substr($evento['nombre'], 0, 1))) ?></span><?php endif ?></div>
                    <div class="event-body"><small><?= esc($evento['categoria'] ?: 'Evento') ?></small>
                        <h2><?= esc($evento['nombre']) ?></h2>
                        <p><?= esc(mb_strimwidth($evento['descripcion'] ?: 'Consulta las funciones disponibles.', 0, 115, '…')) ?></p>
                        <div class="event-meta"><span>Desde <strong>$<?= esc(number_format((float) $evento['precio_desde'], 2)) ?></strong></span><span><?= esc(date('d/m/Y', strtotime($evento['proxima_funcion']))) ?></span></div><a href="<?= base_url('eventos/' . $evento['slug']) ?>">Ver evento</a>
                    </div>
                </article><?php endforeach ?></div><?php endif ?>
</section><?= $this->endSection() ?>

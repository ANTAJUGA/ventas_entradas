<?= $this->extend('layouts/admin') ?>

<?= $this->section('contenido') ?>
<section class="page-heading">
    <div>
        <a class="back-link" href="<?= base_url('admin/funciones') ?>">← Volver a funciones</a>
        <h1><?= esc($titulo) ?></h1>
        <p class="muted">Programa una fecha para <?= esc($evento['nombre']) ?>.</p>
    </div>
</section>

<?php $errors = session('errors') ?? []; ?>
<?php if ($errors !== []): ?>
    <div class="alert error">
        <strong>Revisa la información ingresada:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
<?php endif ?>

<form class="panel event-form" action="<?= esc($accion) ?>" method="post">
    <?= csrf_field() ?>

    <div class="form-section">
        <div class="form-section-title">
            <h2>Programación</h2>
            <p>La función representa una fecha, lugar y aforo concretos del evento.</p>
        </div>

        <div class="form-grid">
            <label class="field full">
                <span>Evento *</span>
                <select name="evento_id" required>
                    <?php foreach ($eventos as $eventoOption): ?>
                        <?php $selectedEvento = (string) old('evento_id', $funcion['evento_id'] ?? $evento['id']); ?>
                        <option value="<?= esc((string) $eventoOption['id']) ?>" <?= $selectedEvento === (string) $eventoOption['id'] ? 'selected' : '' ?>>
                            <?= esc($eventoOption['nombre']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </label>

            <label class="field full">
                <span>Nombre identificador</span>
                <input type="text" name="nombre" maxlength="120" value="<?= esc(old('nombre', $funcion['nombre'] ?? '')) ?>" placeholder="Ej. Función nocturna">
            </label>

            <label class="field">
                <span>Inicio de la función *</span>
                <input type="datetime-local" name="fecha_inicio" required value="<?= esc(old('fecha_inicio', isset($funcion['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($funcion['fecha_inicio'])) : '')) ?>">
            </label>

            <label class="field">
                <span>Finalización</span>
                <input type="datetime-local" name="fecha_fin" value="<?= esc(old('fecha_fin', ! empty($funcion['fecha_fin']) ? date('Y-m-d\TH:i', strtotime($funcion['fecha_fin'])) : '')) ?>">
            </label>

            <label class="field">
                <span>Inicio de ventas</span>
                <input type="datetime-local" name="venta_inicio" value="<?= esc(old('venta_inicio', ! empty($funcion['venta_inicio']) ? date('Y-m-d\TH:i', strtotime($funcion['venta_inicio'])) : '')) ?>">
            </label>

            <label class="field">
                <span>Fin de ventas</span>
                <input type="datetime-local" name="venta_fin" value="<?= esc(old('venta_fin', ! empty($funcion['venta_fin']) ? date('Y-m-d\TH:i', strtotime($funcion['venta_fin'])) : '')) ?>">
            </label>

            <label class="field">
                <span>Recinto *</span>
                <input type="text" name="recinto" maxlength="150" required value="<?= esc(old('recinto', $funcion['recinto'] ?? '')) ?>" placeholder="Ej. Teatro principal">
            </label>

            <label class="field">
                <span>Ciudad *</span>
                <input type="text" name="ciudad" maxlength="100" required value="<?= esc(old('ciudad', $funcion['ciudad'] ?? '')) ?>" placeholder="Ej. Guayaquil">
            </label>

            <label class="field full">
                <span>Dirección</span>
                <input type="text" name="direccion" maxlength="255" value="<?= esc(old('direccion', $funcion['direccion'] ?? '')) ?>" placeholder="Dirección del recinto">
            </label>

            <label class="field">
                <span>Aforo total *</span>
                <input type="number" name="aforo_total" min="1" required value="<?= esc(old('aforo_total', $funcion['aforo_total'] ?? '')) ?>" placeholder="500">
            </label>
        </div>
    </div>

    <footer class="form-actions">
        <a class="secondary-button" href="<?= base_url('admin/funciones') ?>">Cancelar</a>
        <button class="primary-button borderless" type="submit">Guardar función</button>
    </footer>
</form>
<?= $this->endSection() ?>

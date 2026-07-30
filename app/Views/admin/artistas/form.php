<?= $this->extend('layouts/admin') ?>

<?= $this->section('contenido') ?>
<section class="page-heading">
    <div>
        <a class="back-link" href="<?= base_url('admin/artistas') ?>">← Volver a artistas</a>
        <h1><?= esc($titulo) ?></h1>
        <p class="muted">Completa la información general del artista. Este registro todavía no se relaciona con eventos ni funciones.</p>
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
            <h2>Información artística</h2>
            <p>Datos administrativos del artista o agrupación.</p>
        </div>

        <div class="form-grid">
            <label class="field">
                <span>Nombre artístico *</span>
                <input type="text" name="nombre_artistico" maxlength="150" required value="<?= esc(old('nombre_artistico', $artista['nombre_artistico'] ?? '')) ?>" placeholder="Ej. Son del Pacífico">
            </label>

            <label class="field">
                <span>Nombre real</span>
                <input type="text" name="nombre_real" maxlength="180" value="<?= esc(old('nombre_real', $artista['nombre_real'] ?? '')) ?>" placeholder="Nombre personal o razón de la agrupación">
            </label>

            <label class="field">
                <span>Tipo *</span>
                <?php $tipoSeleccionado = (string) old('tipo', $artista['tipo'] ?? 'solista'); ?>
                <select name="tipo" required>
                    <option value="solista" <?= $tipoSeleccionado === 'solista' ? 'selected' : '' ?>>Solista</option>
                    <option value="banda" <?= $tipoSeleccionado === 'banda' ? 'selected' : '' ?>>Banda</option>
                    <option value="duo" <?= $tipoSeleccionado === 'duo' ? 'selected' : '' ?>>Dúo</option>
                    <option value="orquesta" <?= $tipoSeleccionado === 'orquesta' ? 'selected' : '' ?>>Orquesta</option>
                    <option value="otro" <?= $tipoSeleccionado === 'otro' ? 'selected' : '' ?>>Otro</option>
                </select>
            </label>

            <label class="field">
                <span>Género</span>
                <input type="text" name="genero" maxlength="80" value="<?= esc(old('genero', $artista['genero'] ?? '')) ?>" placeholder="Ej. Rock, electrónica o teatro">
            </label>

            <label class="field">
                <span>País</span>
                <input type="text" name="pais" maxlength="100" value="<?= esc(old('pais', $artista['pais'] ?? '')) ?>" placeholder="Ej. Ecuador">
            </label>

            <label class="field">
                <span>Estado *</span>
                <?php $estadoSeleccionado = (string) old('estado', $artista['estado'] ?? 'activo'); ?>
                <select name="estado" required>
                    <option value="activo" <?= $estadoSeleccionado === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $estadoSeleccionado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </label>

            <label class="field full">
                <span>Descripción</span>
                <textarea name="descripcion" rows="5" placeholder="Reseña breve del artista."><?= esc(old('descripcion', $artista['descripcion'] ?? '')) ?></textarea>
            </label>

            <label class="field full">
                <span>Ruta o URL de imagen</span>
                <input type="text" name="imagen" maxlength="255" value="<?= esc(old('imagen', $artista['imagen'] ?? '')) ?>" placeholder="uploads/artistas/imagen.jpg">
            </label>
        </div>
    </div>

    <footer class="form-actions">
        <a class="secondary-button" href="<?= base_url('admin/artistas') ?>">Cancelar</a>
        <button class="primary-button borderless" type="submit">Guardar artista</button>
    </footer>
</form>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>

<?= $this->section('contenido') ?>
<section class="page-heading">
    <div>
        <a class="back-link" href="<?= base_url('admin/eventos') ?>">← Volver a eventos</a>
        <h1><?= esc($titulo) ?></h1>
        <p class="muted">Completa la información general. Las funciones y entradas se configuran después.</p>
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

<?php if ($organizadores === []): ?>
    <div class="alert error">Debes registrar un usuario Administrador u Organizador antes de crear eventos.</div>
<?php endif ?>

<form class="panel event-form" action="<?= esc($accion) ?>" method="post">
    <?= csrf_field() ?>

    <div class="form-section">
        <div class="form-section-title">
            <h2>Información principal</h2>
            <p>Datos visibles en la cartelera del evento.</p>
        </div>

        <div class="form-grid">
            <label class="field full">
                <span>Nombre del evento *</span>
                <input type="text" name="nombre" maxlength="150" required value="<?= esc(old('nombre', $evento['nombre'] ?? '')) ?>" placeholder="Ej. Festival de Verano">
            </label>

            <label class="field">
                <span>Categoría</span>
                <input type="text" name="categoria" maxlength="80" value="<?= esc(old('categoria', $evento['categoria'] ?? '')) ?>" placeholder="Ej. Concierto">
            </label>

            <label class="field">
                <span>Organizador *</span>
                <select name="organizador_id" required <?= $organizadores === [] ? 'disabled' : '' ?>>
                    <option value="">Selecciona un organizador</option>
                    <?php foreach ($organizadores as $organizador): ?>
                        <?php $selectedId = (string) old('organizador_id', $evento['organizador_id'] ?? ''); ?>
                        <option value="<?= esc((string) $organizador['id']) ?>" <?= $selectedId === (string) $organizador['id'] ? 'selected' : '' ?>>
                            <?= esc($organizador['nombres'] . ' ' . $organizador['apellidos'] . ' · ' . $organizador['rol_nombre']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </label>

            <label class="field full">
                <span>Descripción</span>
                <textarea name="descripcion" rows="5" placeholder="Describe el evento para los asistentes."><?= esc(old('descripcion', $evento['descripcion'] ?? '')) ?></textarea>
            </label>

            <label class="field full">
                <span>Ruta o URL de imagen</span>
                <input type="text" name="imagen" maxlength="255" value="<?= esc(old('imagen', $evento['imagen'] ?? '')) ?>" placeholder="uploads/eventos/imagen.jpg">
            </label>
        </div>
    </div>

    <footer class="form-actions">
        <a class="secondary-button" href="<?= base_url('admin/eventos') ?>">Cancelar</a>
        <button class="primary-button borderless" type="submit" <?= $organizadores === [] ? 'disabled' : '' ?>>Guardar evento</button>
    </footer>
</form>
<?= $this->endSection() ?>

<?= $this->extend('layouts/admin') ?>
<?= $this->section('contenido') ?>
<section class="page-heading"><div><a class="back-link" href="<?= base_url('admin/tipos-entrada') ?>">← Volver a tipos de entrada</a><h1><?= esc($titulo) ?></h1><p class="muted"><?= esc($funcion['evento_nombre']) ?> · <?= esc($funcion['nombre'] ?: date('d/m/Y H:i', strtotime($funcion['fecha_inicio']))) ?></p></div></section>
<?php $errors = session('errors') ?? []; ?>
<?php if ($errors !== []): ?><div class="alert error"><strong>Revisa la información ingresada:</strong><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
<div class="capacity-summary"><div><span>Aforo de la función</span><strong><?= esc((string) $funcion['aforo_total']) ?></strong></div><div><span>Cupo ya asignado</span><strong><?= esc((string) $cupoAsignado) ?></strong></div><div><span>Disponible</span><strong><?= esc((string) max(0, (int) $funcion['aforo_total'] - $cupoAsignado)) ?></strong></div></div>
<form class="panel event-form" action="<?= esc($accion) ?>" method="post"><?= csrf_field() ?>
    <div class="form-section"><div class="form-section-title"><h2>Configuración comercial</h2><p>La suma de los cupos no puede superar el aforo de la función.</p></div><div class="form-grid">
        <label class="field full"><span>Función *</span><select name="funcion_id" required><?php foreach ($funciones as $option): ?><?php $selected = (string) old('funcion_id', $tipoEntrada['funcion_id'] ?? $funcion['id']); ?><option value="<?= esc((string) $option['id']) ?>" <?= $selected === (string) $option['id'] ? 'selected' : '' ?>><?= esc($option['evento_nombre'] . ' · ' . ($option['nombre'] ?: date('d/m/Y H:i', strtotime($option['fecha_inicio'])))) ?></option><?php endforeach ?></select></label>
        <label class="field"><span>Nombre *</span><input type="text" name="nombre" maxlength="80" required value="<?= esc(old('nombre', $tipoEntrada['nombre'] ?? '')) ?>" placeholder="Ej. General"></label>
        <label class="field"><span>Precio *</span><input type="number" name="precio" min="0" step="0.01" required value="<?= esc(old('precio', $tipoEntrada['precio'] ?? '')) ?>" placeholder="25.00"></label>
        <label class="field"><span>Cupo *</span><input type="number" name="cupo" min="0" required value="<?= esc(old('cupo', $tipoEntrada['cupo'] ?? '')) ?>" placeholder="100"></label>
        <label class="field"><span>Límite por compra *</span><input type="number" name="limite_por_compra" min="1" required value="<?= esc(old('limite_por_compra', $tipoEntrada['limite_por_compra'] ?? 10)) ?>"></label>
        <label class="field full"><span>Descripción</span><textarea name="descripcion" rows="4" maxlength="255" placeholder="Beneficios o ubicación de esta entrada."><?= esc(old('descripcion', $tipoEntrada['descripcion'] ?? '')) ?></textarea></label>
        <label class="check-field full"><input type="checkbox" name="activo" value="1" <?= (string) old('activo', $tipoEntrada['activo'] ?? 1) === '1' ? 'checked' : '' ?>><span>Disponible para la venta</span></label>
    </div></div>
    <footer class="form-actions"><a class="secondary-button" href="<?= base_url('admin/tipos-entrada') ?>">Cancelar</a><button class="primary-button borderless" type="submit">Guardar tipo de entrada</button></footer>
</form>
<?= $this->endSection() ?>

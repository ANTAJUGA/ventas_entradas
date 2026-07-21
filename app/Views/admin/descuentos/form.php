<?= $this->extend('layouts/admin') ?>
<?= $this->section('contenido') ?>
<section class="page-heading"><div><a class="back-link" href="<?= base_url('admin/descuentos') ?>">← Volver a descuentos</a><h1><?= esc($titulo) ?></h1><p class="muted">Configura las condiciones y vigencia de la promoción.</p></div></section>
<?php $errors = session('errors') ?? []; ?>
<?php if ($errors !== []): ?><div class="alert error"><strong>Revisa la información ingresada:</strong><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach ?></ul></div><?php endif ?>
<form class="panel event-form" action="<?= esc($accion) ?>" method="post"><?= csrf_field() ?>
    <div class="form-section"><div class="form-section-title"><h2>Información promocional</h2><p>Deja el evento vacío para que el código pueda utilizarse en cualquier evento.</p></div><div class="form-grid">
        <label class="field"><span>Código *</span><input class="uppercase-input" type="text" name="codigo" maxlength="50" required value="<?= esc(old('codigo', $descuento['codigo'] ?? '')) ?>" placeholder="VERANO20"></label>
        <label class="field"><span>Nombre *</span><input type="text" name="nombre" maxlength="100" required value="<?= esc(old('nombre', $descuento['nombre'] ?? '')) ?>" placeholder="Promoción de verano"></label>
        <label class="field full"><span>Evento</span><select name="evento_id"><option value="">Todos los eventos</option><?php foreach ($eventos as $evento): ?><?php $selected = (string) old('evento_id', $descuento['evento_id'] ?? ''); ?><option value="<?= esc((string) $evento['id']) ?>" <?= $selected === (string) $evento['id'] ? 'selected' : '' ?>><?= esc($evento['nombre']) ?></option><?php endforeach ?></select></label>
        <label class="field"><span>Tipo *</span><select name="tipo" required><?php $selectedTipo = old('tipo', $descuento['tipo'] ?? 'porcentaje'); ?><option value="porcentaje" <?= $selectedTipo === 'porcentaje' ? 'selected' : '' ?>>Porcentaje</option><option value="fijo" <?= $selectedTipo === 'fijo' ? 'selected' : '' ?>>Valor fijo</option></select></label>
        <label class="field"><span>Valor *</span><input type="number" name="valor" min="0.01" step="0.01" required value="<?= esc(old('valor', $descuento['valor'] ?? '')) ?>" placeholder="20.00"></label>
        <label class="field"><span>Compra mínima</span><input type="number" name="compra_minima" min="0" step="0.01" value="<?= esc(old('compra_minima', $descuento['compra_minima'] ?? 0)) ?>"></label>
        <label class="field"><span>Límite de usos</span><input type="number" name="limite_usos" min="1" value="<?= esc(old('limite_usos', $descuento['limite_usos'] ?? '')) ?>" placeholder="Sin límite"></label>
        <label class="field"><span>Inicio de vigencia *</span><input type="datetime-local" name="fecha_inicio" required value="<?= esc(old('fecha_inicio', ! empty($descuento['fecha_inicio']) ? date('Y-m-d\TH:i', strtotime($descuento['fecha_inicio'])) : '')) ?>"></label>
        <label class="field"><span>Fin de vigencia *</span><input type="datetime-local" name="fecha_fin" required value="<?= esc(old('fecha_fin', ! empty($descuento['fecha_fin']) ? date('Y-m-d\TH:i', strtotime($descuento['fecha_fin'])) : '')) ?>"></label>
        <label class="field full"><span>Descripción</span><textarea name="descripcion" rows="4" maxlength="255" placeholder="Condiciones de la promoción."><?= esc(old('descripcion', $descuento['descripcion'] ?? '')) ?></textarea></label>
        <label class="check-field full"><input type="checkbox" name="activo" value="1" <?= (string) old('activo', $descuento['activo'] ?? 1) === '1' ? 'checked' : '' ?>><span>Descuento activo</span></label>
    </div></div>
    <footer class="form-actions"><a class="secondary-button" href="<?= base_url('admin/descuentos') ?>">Cancelar</a><button class="primary-button borderless" type="submit">Guardar descuento</button></footer>
</form>
<?= $this->endSection() ?>

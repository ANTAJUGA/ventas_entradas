<?= $this->extend('layouts/admin') ?>
<?= $this->section('contenido') ?>
<section class="page-heading"><div><p class="eyebrow">Venta presencial</p><h1>Punto de venta</h1><p class="muted">Identifica al cliente por correo y emite sus entradas.</p></div><a class="secondary-button" href="<?= base_url('admin/ventas') ?>">Ver historial</a></section>
<?php foreach (['success', 'error'] as $type): ?><?php if (session()->has($type)): ?><div class="alert <?= esc($type) ?>"><?= esc(session($type)) ?></div><?php endif ?><?php endforeach ?>
<form class="panel event-form" action="<?= base_url('admin/ventas') ?>" method="post"><?= csrf_field() ?>
    <div class="form-section"><div class="form-section-title"><h2>Cliente</h2><p>Para un cliente existente solamente necesitas escribir su correo. Sus datos se recuperan automáticamente.</p></div><div class="form-grid">
        <label class="field full"><span>Correo del cliente *</span><input id="clienteEmail" type="email" name="cliente_email" list="clientesList" maxlength="190" required autocomplete="off" value="<?= esc(old('cliente_email')) ?>" placeholder="cliente@correo.com"><datalist id="clientesList"><?php foreach ($clientes as $c): ?><option value="<?= esc($c['email']) ?>"><?= esc($c['nombres'] . ' ' . $c['apellidos']) ?></option><?php endforeach ?></datalist><small id="clienteEstado" class="field-help">Escribe o selecciona el correo.</small></label>
        <div id="clienteNuevo" class="form-grid nested-grid full" hidden>
            <p class="new-client-note full">Correo nuevo: completa únicamente nombre y apellido para crear la cuenta.</p>
            <label class="field"><span>Nombres *</span><input id="clienteNombres" type="text" name="cliente_nombres" maxlength="100" value="<?= esc(old('cliente_nombres')) ?>"></label>
            <label class="field"><span>Apellidos *</span><input id="clienteApellidos" type="text" name="cliente_apellidos" maxlength="100" value="<?= esc(old('cliente_apellidos')) ?>"></label>
        </div>
    </div></div>
    <div class="form-section"><div class="form-section-title"><h2>Entradas y pago</h2><p>Selecciona la entrada, cantidad y forma de pago. El cupo se comprueba antes de emitir.</p></div><div class="form-grid">
        <label class="field full"><span>Evento, función y tipo de entrada *</span><select name="tipo_entrada_id" required><option value="">Selecciona una opción</option><?php foreach ($opciones as $o): ?><option value="<?= esc((string) $o['id']) ?>" <?= (string) old('tipo_entrada_id') === (string) $o['id'] ? 'selected' : '' ?>><?= esc($o['evento_nombre'] . ' · ' . date('d/m/Y H:i', strtotime($o['fecha_inicio'])) . ' · ' . $o['tipo_nombre'] . ' · $' . number_format((float) $o['precio'], 2)) ?></option><?php endforeach ?></select></label>
        <label class="field"><span>Cantidad *</span><input type="number" name="cantidad" min="1" max="20" required value="<?= esc(old('cantidad', '1')) ?>"></label>
        <label class="field"><span>Método de pago *</span><select name="metodo_pago" required><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia</option><option value="tarjeta-simulada">Tarjeta simulada</option></select></label>
        <label class="field full"><span>Código promocional <small>(opcional)</small></span><input class="uppercase-input" name="codigo_descuento" maxlength="50" value="<?= esc(old('codigo_descuento')) ?>" placeholder="Ej. VERANO20"><small class="field-help">Aquí va un código de descuento, no el código ENT- de una entrada.</small></label>
    </div></div>
    <div class="form-actions"><a class="secondary-button" href="<?= base_url('admin/ventas') ?>">Cancelar</a><button class="primary-button borderless" type="submit">Vender y emitir</button></div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script>
const email = document.getElementById('clienteEmail');
const newFields = document.getElementById('clienteNuevo');
const status = document.getElementById('clienteEstado');
const knownClients = new Map(<?= json_encode(array_column($clientes, null, 'email'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>);
function updateClient() {
    const value = email.value.trim().toLowerCase();
    const client = knownClients.get(value);
    const isEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    if (client) {
        newFields.hidden = true;
        status.textContent = `Cliente encontrado: ${client.nombres} ${client.apellidos}`;
        status.className = 'field-help found';
    } else if (isEmail) {
        newFields.hidden = false;
        status.textContent = 'Cliente nuevo';
        status.className = 'field-help new';
    } else {
        newFields.hidden = true;
        status.textContent = 'Escribe o selecciona un correo válido.';
        status.className = 'field-help';
    }
}
email.addEventListener('input', updateClient);
updateClient();
</script><?= $this->endSection() ?>

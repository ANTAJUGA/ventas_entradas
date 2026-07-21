<?= $this->extend('layouts/admin') ?><?= $this->section('contenido') ?>
<section class="page-heading"><div><p class="eyebrow">Ingreso al evento</p><h1>Control de acceso</h1><p class="muted">Escanea o escribe el código. Una entrada solamente puede aceptarse una vez.</p></div></section>
<?php foreach (['success','error','info'] as $type): ?><?php if (session()->has($type)): ?><div class="alert <?= esc($type) ?>"><?= esc(session($type)) ?></div><?php endif ?><?php endforeach ?>
<div class="capacity-summary"><div><span>Aceptados hoy</span><strong><?= esc((string) $resumen['aceptados']) ?></strong></div><div><span>Rechazados hoy</span><strong><?= esc((string) $resumen['rechazados']) ?></strong></div><div><span>Estado del lector</span><strong id="scannerStatus">Manual</strong></div></div>
<section class="access-grid">
    <div class="panel access-reader"><div class="panel-heading"><div><h2>Consultar entrada</h2><p>Admite código escrito o lectura QR.</p></div></div><div class="access-body">
        <form action="<?= base_url('admin/accesos') ?>" method="get" class="access-search"><label class="field"><span>Código de entrada</span><input id="codigoInput" class="uppercase-input" name="codigo" required autofocus value="<?= esc($codigo) ?>" placeholder="ENT-XXXXXXXXXXXX"></label><button class="primary-button borderless" type="submit">Buscar</button><button class="secondary-button" id="scanButton" type="button">Escanear QR</button></form>
        <video id="scannerVideo" class="scanner-video" playsinline hidden></video>
        <?php if ($codigo !== '' && $entrada === null && ! session()->has('error')): ?><div class="access-result rejected"><strong>Código no encontrado</strong><p>No existe una entrada asociada con ese código.</p></div><?php endif ?>
        <?php if ($entrada !== null): ?><div class="access-result <?= $entrada['estado'] === 'vigente' ? 'valid' : 'rejected' ?>"><div><span class="tag <?= esc($entrada['estado']) ?>"><?= esc(ucfirst($entrada['estado'])) ?></span><h2><?= esc($entrada['evento_nombre']) ?></h2><p><?= esc($entrada['tipo_nombre']) ?> · <?= esc($entrada['cliente_nombre']) ?></p><p><?= esc(date('d/m/Y H:i', strtotime($entrada['fecha_inicio']))) ?> · <?= esc($entrada['recinto'] . ', ' . $entrada['ciudad']) ?></p><?php if ($entrada['usada_at']): ?><p><b>Utilizada:</b> <?= esc(date('d/m/Y H:i', strtotime($entrada['usada_at']))) ?></p><?php endif ?></div><form action="<?= base_url('admin/accesos/validar') ?>" method="post"><?= csrf_field() ?><input type="hidden" name="codigo" value="<?= esc($entrada['codigo']) ?>"><label class="field"><span>Punto de acceso</span><input name="punto_acceso" maxlength="100" value="Acceso principal"></label><button class="primary-button borderless" type="submit"><?= $entrada['estado'] === 'vigente' ? 'Validar ingreso' : 'Registrar intento' ?></button></form></div><?php endif ?>
    </div></div>
    <div class="panel"><div class="panel-heading"><div><h2>Reglas rápidas</h2><p>Respuesta del sistema</p></div></div><div class="access-rules"><p><b>Vigente</b><span>Autorizar y marcar como usada.</span></p><p><b>Usada</b><span>Rechazar y mostrar la primera validación.</span></p><p><b>Anulada</b><span>Rechazar el ingreso.</span></p></div></div>
</section>
<section class="panel access-history"><div class="panel-heading"><div><h2>Historial reciente</h2><p>Últimos <?= count($historial) ?> intentos</p></div></div><?php if ($historial === []): ?><div class="empty-state compact"><p>Todavía no se han validado entradas.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Entrada</th><th>Evento</th><th>Resultado</th><th>Operador</th><th>Motivo</th></tr></thead><tbody><?php foreach ($historial as $h): ?><tr><td><?= esc(date('d/m/Y H:i:s', strtotime($h['registrado_at']))) ?></td><td><strong><?= esc($h['codigo']) ?></strong><small><?= esc($h['punto_acceso'] ?: '—') ?></small></td><td><?= esc($h['evento_nombre']) ?></td><td><span class="tag <?= $h['resultado'] === 'aceptado' ? 'activo' : 'bloqueado' ?>"><?= esc(ucfirst($h['resultado'])) ?></span></td><td><?= esc(trim(($h['nombres'] ?? '') . ' ' . ($h['apellidos'] ?? '')) ?: 'Sistema') ?></td><td><?= esc($h['motivo']) ?></td></tr><?php endforeach ?></tbody></table></div><?php endif ?></section>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?><script>
const scanButton = document.getElementById('scanButton');
const video = document.getElementById('scannerVideo');
const status = document.getElementById('scannerStatus');
let stream;
scanButton.addEventListener('click', async () => {
    if (!('BarcodeDetector' in window)) { alert('Este navegador no admite lector QR. Usa Chrome/Edge actualizado o escribe el código.'); return; }
    try {
        stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:'environment'}});
        video.srcObject = stream; video.hidden = false; await video.play(); status.textContent = 'Escaneando';
        const detector = new BarcodeDetector({formats:['qr_code']});
        const read = async () => {
            if (!stream) return;
            const codes = await detector.detect(video);
            if (codes[0]?.rawValue) {
                document.getElementById('codigoInput').value = codes[0].rawValue.trim().toUpperCase();
                stream.getTracks().forEach(t => t.stop()); stream = null; video.hidden = true; status.textContent = 'Leído';
                document.getElementById('codigoInput').form.submit(); return;
            }
            requestAnimationFrame(read);
        }; read();
    } catch (error) { status.textContent = 'Sin cámara'; alert('No se pudo abrir la cámara. Verifica el permiso del navegador.'); }
});
</script><?= $this->endSection() ?>

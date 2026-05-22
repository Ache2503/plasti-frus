<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-envelope"></i> Mensajes</h1>
    <button class="btn btn-sm btn-success" onclick="abrirRedactar()"><i class="bi bi-pencil-square"></i> Redactar</button>
</div>

<ul class="nav nav-tabs mb-3" id="mensajesTab">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#inbox">Recibidos <?= $no_leidos > 0 ? '<span class="badge bg-danger">'.$no_leidos.'</span>' : '' ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#sent">Enviados</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="inbox">
        <div class="list-group">
            <?php foreach ($inbox as $m): ?>
            <div class="list-group-item list-group-item-action <?= !$m['leido'] ? 'fw-bold' : '' ?>" style="cursor:pointer;" onclick="verMensaje(<?= $m['id_mensaje'] ?>, '<?= safe_string($m['remitente_nombre'] ?? $m['remitente_usuario']) ?>', '<?= safe_string($m['asunto']) ?>', '<?= safe_string(str_replace("'", "\'", $m['mensaje'])) ?>')">
                <div class="d-flex justify-content-between">
                    <small class="text-muted"><?= safe_string($m['remitente_nombre'] ?? $m['remitente_usuario']) ?></small>
                    <small class="text-muted"><?= format_date($m['created_at']) ?></small>
                </div>
                <div><?= safe_string($m['asunto']) ?></div>
                <small class="text-muted"><?= safe_string(truncate($m['mensaje'], 100)) ?></small>
            </div>
            <?php endforeach; ?>
            <?php if (empty($inbox)): ?>
            <div class="list-group-item text-center text-muted">Sin mensajes</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="tab-pane fade" id="sent">
        <div class="list-group">
            <?php foreach ($sent as $m): ?>
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <small class="text-muted">Para: <?= safe_string($m['destinatario_nombre'] ?? $m['destinatario_usuario']) ?></small>
                    <small class="text-muted"><?= format_date($m['created_at']) ?></small>
                </div>
                <div><?= safe_string($m['asunto']) ?></div>
                <small class="text-muted"><?= safe_string(truncate($m['mensaje'], 100)) ?></small>
            </div>
            <?php endforeach; ?>
            <?php if (empty($sent)): ?>
            <div class="list-group-item text-center text-muted">Sin mensajes enviados</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Redactar -->
<div class="modal fade" id="redactarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('mensajes/enviar') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Redactar Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-2">
                        <label class="form-label">Destinatario</label>
                        <select name="para_user_id" class="form-select form-select-sm" required>
                            <option value="">Seleccionar...</option>
                            <?php
                            $db = \App\Core\Database::getInstance();
                            $usuarios = $db->fetchAll("SELECT u.id_usuario, u.nombre_usuario, CONCAT(e.nombre, ' ', e.apellido_paterno) as nombre_completo FROM usuarios u LEFT JOIN empleados e ON u.id_empleado = e.id_empleado WHERE u.id_rol IN (1,3,4) AND u.activo = 1 ORDER BY e.nombre");
                            ?>
                            <?php foreach ($usuarios as $u): ?>
                            <option value="<?= $u['id_usuario'] ?>"><?= safe_string($u['nombre_completo'] ?? $u['nombre_usuario']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Asunto</label>
                        <input type="text" name="asunto" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mensaje</label>
                        <textarea name="mensaje" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-send"></i> Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Mensaje -->
<div class="modal fade" id="verMensajeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="msgAsunto"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <small class="text-muted" id="msgRemitente"></small>
                <hr>
                <p id="msgContenido"></p>
            </div>
        </div>
    </div>
</div>

<script>
function abrirRedactar() { new bootstrap.Modal(document.getElementById('redactarModal')).show(); }

function verMensaje(id, remitente, asunto, mensaje) {
    document.getElementById('msgAsunto').textContent = asunto;
    document.getElementById('msgRemitente').textContent = 'De: ' + remitente;
    document.getElementById('msgContenido').textContent = mensaje;
    const formData = new FormData();
    formData.append('csrf_token', '<?= csrf_token() ?>');
    fetch('<?= url('mensajes/leer') ?>/' + id, { method: 'POST', body: formData });
    new bootstrap.Modal(document.getElementById('verMensajeModal')).show();
}
</script>

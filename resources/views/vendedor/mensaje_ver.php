<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-envelope-open"></i> Mensaje</h1>
    <a href="<?= url('mensajes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-dark text-white">
        <strong><?= safe_string($mensaje['asunto']) ?></strong>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-1">De: <?= safe_string($mensaje['remitente_nombre'] ?? 'Desconocido') ?></p>
        <p class="text-muted small"><?= format_date($mensaje['created_at']) ?></p>
        <hr>
        <p><?= nl2br(safe_string($mensaje['mensaje'])) ?></p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-success text-white"><i class="bi bi-reply"></i> Responder</div>
    <div class="card-body">
        <form method="POST" action="<?= url('mensajes') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="para_user_id" value="<?= $mensaje['de_user_id'] ?>">
            <input type="hidden" name="asunto" value="Re: <?= safe_string($mensaje['asunto']) ?>">
            <div class="mb-2">
                <label class="form-label">Mensaje</label>
                <textarea name="mensaje" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-send"></i> Enviar respuesta</button>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-ticket"></i> <?= safe_string($ticket['titulo']) ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= url('tickets') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        <?php if ($ticket['estatus'] !== 'cerrado'): ?>
        <form method="POST" action="<?= url('tickets/cerrar/' . $ticket['id_ticket']) ?>" class="d-inline" onsubmit="return confirm('¿Cerrar este ticket?')">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> Cerrar Ticket</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <strong>Estatus:</strong>
        <?php if ($ticket['estatus'] === 'abierto'): ?>
        <span class="badge bg-success">Abierto</span>
        <?php elseif ($ticket['estatus'] === 'respondido'): ?>
        <span class="badge bg-info">Respondido</span>
        <?php else: ?>
        <span class="badge bg-secondary">Cerrado</span>
        <?php endif; ?>
    </div>
    <div class="col-md-3">
        <strong>Prioridad:</strong>
        <?php if ($ticket['prioridad'] === 'urgente'): ?>
        <span class="badge bg-danger">Urgente</span>
        <?php elseif ($ticket['prioridad'] === 'alta'): ?>
        <span class="badge bg-warning text-dark">Alta</span>
        <?php elseif ($ticket['prioridad'] === 'media'): ?>
        <span class="badge bg-info">Media</span>
        <?php else: ?>
        <span class="badge bg-secondary">Baja</span>
        <?php endif; ?>
    </div>
    <div class="col-md-3">
        <strong>Creado:</strong> <?= format_datetime($ticket['created_at']) ?>
    </div>
    <div class="col-md-3">
        <strong>Cliente:</strong> <?= safe_string($ticket['cliente_razon'] ?? 'N/A') ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white"><i class="bi bi-chat-dots"></i> Conversación</div>
    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
        <div class="d-flex mb-4">
            <div class="flex-shrink-0 me-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-person"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="bg-light p-3 rounded">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Tú <small class="text-muted">(cliente)</small></strong>
                        <small class="text-muted"><?= format_datetime($ticket['created_at']) ?></small>
                    </div>
                    <p class="mb-0"><?= nl2br(safe_string($ticket['descripcion'])) ?></p>
                </div>
            </div>
        </div>

        <?php foreach ($ticket['respuestas'] as $r): ?>
        <div class="d-flex mb-4 <?= $r['es_cliente'] ? '' : 'flex-row-reverse' ?>">
            <div class="flex-shrink-0 me-3 <?= $r['es_cliente'] ? '' : 'ms-3 me-0' ?>">
                <div class="rounded-circle <?= $r['es_cliente'] ? 'bg-secondary' : 'bg-success' ?> text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-<?= $r['es_cliente'] ? 'person' : 'headset' ?>"></i>
                </div>
            </div>
            <div class="flex-grow-1" style="max-width: 75%;">
                <div class="bg-<?= $r['es_cliente'] ? 'light' : 'success' ?> bg-opacity-10 p-3 rounded">
                    <div class="d-flex justify-content-between mb-2">
                        <strong><?= $r['es_cliente'] ? 'Tú' : safe_string($r['nombre_usuario'] ?? 'Soporte') ?></strong>
                        <small class="text-muted"><?= format_datetime($r['created_at']) ?></small>
                    </div>
                    <p class="mb-0"><?= nl2br(safe_string($r['mensaje'])) ?></p>
                    <?php if (!empty($r['archivo'])): ?>
                    <a href="<?= asset($r['archivo']) ?>" class="btn btn-sm btn-outline-primary mt-2" target="_blank">
                        <i class="bi bi-paperclip"></i> Ver archivo
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($ticket['estatus'] !== 'cerrado'): ?>
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white"><i class="bi bi-reply"></i> Responder</div>
    <div class="card-body">
        <form method="POST" action="<?= url('tickets/responder/' . $ticket['id_ticket']) ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="mb-3">
                <textarea name="mensaje" class="form-control" rows="4" placeholder="Escribe tu respuesta..." required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Adjuntar archivo (opcional)</label>
                <input type="file" name="archivo" class="form-control form-control-sm">
                <small class="text-muted">PDF, JPG, PNG, DOC (max 10MB)</small>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-dark"><i class="bi bi-send"></i> Enviar Respuesta</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

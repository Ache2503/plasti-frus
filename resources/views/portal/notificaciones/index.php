<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-bell"></i> Notificaciones</h1>
    <div class="d-flex gap-2">
        <?php if ($no_leidas > 0): ?>
        <form method="POST" action="<?= url('notificaciones-cliente/marcar-leidas') ?>" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-check-all"></i> Marcar todas como leídas</button>
        </form>
        <?php endif; ?>
        <span class="badge bg-<?= $no_leidas > 0 ? 'danger' : 'secondary' ?> fs-6">
            <?= $no_leidas ?> no leídas
        </span>
    </div>
</div>

<?php if (empty($notificaciones)): ?>
<div class="text-center py-5">
    <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted">No hay notificaciones</h4>
    <p class="text-muted">Recibirás notificaciones cuando haya novedades en tus pedidos o tickets.</p>
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php foreach ($notificaciones as $n): ?>
            <div class="list-group-item list-group-item-action d-flex gap-3 py-3 <?= !$n['leida'] ? 'fw-semibold bg-light' : '' ?>">
                <div class="flex-shrink-0">
                    <?php if ($n['tipo'] === 'pedido'): ?>
                    <i class="bi bi-box-seam fs-4 text-primary"></i>
                    <?php elseif ($n['tipo'] === 'ticket'): ?>
                    <i class="bi bi-headset fs-4 text-success"></i>
                    <?php elseif ($n['tipo'] === 'factura'): ?>
                    <i class="bi bi-file-text fs-4 text-warning"></i>
                    <?php elseif ($n['tipo'] === 'warning'): ?>
                    <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                    <?php else: ?>
                    <i class="bi bi-info-circle fs-4 text-info"></i>
                    <?php endif; ?>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 <?= !$n['leida'] ? '' : 'text-muted' ?>"><?= safe_string($n['titulo']) ?></h6>
                        <small class="text-muted"><?= format_datetime($n['created_at']) ?></small>
                    </div>
                    <?php if (!empty($n['mensaje'])): ?>
                    <p class="mb-0 small text-muted <?= !$n['leida'] ? '' : '' ?>"><?= safe_string($n['mensaje']) ?></p>
                    <?php endif; ?>
                </div>
                <?php if (!$n['leida']): ?>
                <div class="flex-shrink-0">
                    <span class="badge bg-danger rounded-pill">Nueva</span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

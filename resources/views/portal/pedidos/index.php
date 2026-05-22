<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-box-seam"></i> Mis Pedidos</h1>
    <div class="d-flex gap-2">
        <span class="badge bg-secondary fs-6"><?= safe_string($total_compras['total'] ?? 0) ?> compras</span>
        <span class="badge bg-success fs-6"><?= format_money($total_compras['monto'] ?? 0) ?> invertido</span>
    </div>
</div>

<?php if (empty($mis_pedidos)): ?>
<div class="text-center py-5">
    <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted">Aún no tienes pedidos</h4>
    <p class="text-muted">Explora nuestro catálogo y realiza tu primer pedido.</p>
    <a href="<?= url('catalogo') ?>" class="btn btn-dark"><i class="bi bi-shop"></i> Ir a la Tienda</a>
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Folio</th>
                        <th>Productos</th>
                        <th class="text-end">Total</th>
                        <th>Fecha</th>
                        <th>Estatus</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mis_pedidos as $p): ?>
                    <tr>
                        <td class="fw-bold">#<?= $p['id_pedido'] ?></td>
                        <td><span class="font-monospace small"><?= safe_string($p['folio']) ?></span></td>
                        <td>
                            <span class="badge bg-secondary me-1"><?= $p['total_productos'] ?> prod.</span>
                            <small class="text-muted d-block"><?= safe_string(truncate($p['productos_resumen'] ?? '', 60)) ?></small>
                        </td>
                        <td class="text-end fw-semibold"><?= format_money($p['total']) ?></td>
                        <td><?= format_date($p['created_at']) ?></td>
                        <td>
                            <?php if ($p['estatus'] === 'pendiente'): ?>
                            <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php elseif ($p['estatus'] === 'procesando'): ?>
                            <span class="badge bg-info">Procesando</span>
                            <?php elseif ($p['estatus'] === 'completado'): ?>
                            <span class="badge bg-success">Completado</span>
                            <?php elseif ($p['estatus'] === 'cancelado'): ?>
                            <span class="badge bg-secondary">Cancelado</span>
                            <?php else: ?>
                            <span class="badge bg-secondary"><?= safe_string($p['estatus']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= url('mis-pedidos/' . $p['id_pedido']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                            <?php if (!empty($historial_pedidos[$p['id_pedido']] ?? [])): ?>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#historialModal<?= $p['id_pedido'] ?>">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($mis_pedidos as $p):
    $historial = $historial_pedidos[$p['id_pedido']] ?? [];
    if (empty($historial)) continue;
?>
<div class="modal fade" id="historialModal<?= $p['id_pedido'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); color: #fff;">
                <h6 class="modal-title"><i class="bi bi-clock-history"></i> Pedido #<?= $p['id_pedido'] ?></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background: #f8f9fa;">
                <div class="timeline">
                    <?php foreach ($historial as $h): ?>
                    <div class="d-flex gap-3 mb-3">
                        <div class="d-flex flex-column align-items-center" style="width: 20px;">
                            <div class="rounded-circle" style="width: 12px; height: 12px; background: var(--accent);"></div>
                            <div style="width: 2px; flex: 1; background: #dee2e6;"></div>
                        </div>
                        <div>
                            <span class="badge bg-<?= $h['estatus'] === 'completado' ? 'success' : ($h['estatus'] === 'cancelado' ? 'secondary' : 'warning') ?> bg-opacity-10 text-<?= $h['estatus'] === 'completado' ? 'success' : ($h['estatus'] === 'cancelado' ? 'secondary' : 'warning') ?> mb-1">
                                <?= safe_string(ucfirst($h['estatus'])) ?>
                            </span>
                            <p class="small mb-0"><?= safe_string($h['comentario'] ?? '') ?></p>
                            <small class="text-muted"><?= format_datetime($h['created_at']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-box-seam"></i> Pedido #<?= $pedido['id_pedido'] ?></h1>
    <a href="<?= url('mis-pedidos') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-muted"><i class="bi bi-info-circle"></i> Información del Pedido</h6>
                <p class="mb-1"><strong>Folio:</strong> <?= safe_string($pedido['folio']) ?></p>
                <p class="mb-1"><strong>Fecha:</strong> <?= format_datetime($pedido['created_at']) ?></p>
                <p class="mb-0">
                    <strong>Estatus:</strong>
                    <?php if ($pedido['estatus'] === 'pendiente'): ?>
                    <span class="badge bg-warning text-dark">Pendiente</span>
                    <?php elseif ($pedido['estatus'] === 'procesando'): ?>
                    <span class="badge bg-info">Procesando</span>
                    <?php elseif ($pedido['estatus'] === 'completado'): ?>
                    <span class="badge bg-success">Completado</span>
                    <?php elseif ($pedido['estatus'] === 'cancelado'): ?>
                    <span class="badge bg-secondary">Cancelado</span>
                    <?php else: ?>
                    <span class="badge bg-secondary"><?= safe_string($pedido['estatus']) ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-muted"><i class="bi bi-credit-card"></i> Totales</h6>
                <p class="mb-1"><strong>Subtotal:</strong> <?= format_money($pedido['subtotal'] ?? 0) ?></p>
                <p class="mb-1"><strong>IVA:</strong> <?= format_money($pedido['iva'] ?? 0) ?></p>
                <p class="mb-0 fs-5 fw-bold">Total: <?= format_money($pedido['total']) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title text-muted"><i class="bi bi-geo-alt"></i> Datos de Entrega</h6>
                <p class="mb-1"><?= safe_string($pedido['razon_social'] ?? 'N/A') ?></p>
                <p class="mb-1">RFC: <?= safe_string($pedido['rfc'] ?? 'N/A') ?></p>
                <p class="mb-0"><?= safe_string($pedido['domicilio'] ?? '') ?>, <?= safe_string($pedido['ciudad'] ?? '') ?>, <?= safe_string($pedido['estado'] ?? '') ?></p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($historial)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white"><i class="bi bi-clock-history"></i> Historial del Pedido</div>
    <div class="card-body">
        <div class="timeline">
            <?php foreach ($historial as $h): ?>
            <div class="d-flex gap-3 mb-3">
                <div class="d-flex flex-column align-items-center" style="width: 24px;">
                    <div class="rounded-circle p-1" style="width: 14px; height: 14px; background: <?= $h['estatus'] === 'completado' ? '#198754' : ($h['estatus'] === 'cancelado' ? '#6c757d' : '#ffc107') ?>;"></div>
                    <div style="width: 2px; flex: 1; background: #dee2e6;"></div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <strong><?= safe_string(ucfirst($h['estatus'])) ?></strong>
                        <small class="text-muted"><?= format_datetime($h['created_at']) ?></small>
                    </div>
                    <p class="small mb-0 text-muted"><?= safe_string($h['comentario'] ?? 'Sin comentarios') ?></p>
                    <?php if (!empty($h['usuario'])): ?>
                    <small class="text-muted">por <?= safe_string($h['usuario']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cart-check"></i> Productos</span>
        <span class="badge bg-light text-dark"><?= count($ventas) ?> artículos</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Código</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">P. Unitario</th>
                        <th class="text-end">Subtotal</th>
                        <th>Ticket</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><strong><?= safe_string($v['producto_nombre'] ?? 'N/A') ?></strong></td>
                        <td><span class="font-monospace small"><?= safe_string($v['producto_codigo'] ?? '') ?></span></td>
                        <td class="text-center"><?= $v['cantidad_vendida'] ?></td>
                        <td class="text-end"><?= format_money($v['precio_unitario']) ?></td>
                        <td class="text-end fw-semibold"><?= format_money($v['cantidad_vendida'] * $v['precio_unitario']) ?></td>
                        <td>
                            <?php if (!empty($v['folio_unico'])): ?>
                            <div class="d-flex gap-1">
                                <a href="<?= url('tickets/' . $v['folio_unico']) ?>" class="btn btn-sm btn-outline-info" target="_blank" title="Ver ticket"><i class="bi bi-receipt"></i></a>
                                <a href="<?= url('factura/pdf/' . $v['folio_unico']) ?>" class="btn btn-sm btn-outline-dark" target="_blank" title="Descargar PDF"><i class="bi bi-download"></i></a>
                            </div>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <td colspan="4" class="text-end fw-bold">Total</td>
                        <td class="text-end fw-bold fs-5"><?= format_money($pedido['total']) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-receipt"></i> Detalle de Compra #<?= $venta['id_venta'] ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= url('mis-compras') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Mis Compras</a>
        <span class="badge bg-secondary fs-6"><?= safe_string($rol_nombre) ?></span>
    </div>
</div>

<div class="row g-4">
    <!-- Columna principal: info de compra + producto -->
    <div class="col-md-7">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-info-circle"></i> Informaci&oacute;n de la Compra</span>
                <span class="badge bg-<?= $venta['estatus'] === 'completado' ? 'success' : 'warning' ?> fs-6">
                    <?= $venta['estatus'] === 'completado' ? 'Completado' : 'Pendiente' ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm borderless mb-0">
                            <tr><td class="text-muted ps-0">Folio Venta</td><td class="fw-semibold">#<?= $venta['id_venta'] ?></td></tr>
                            <tr><td class="text-muted ps-0">Fecha</td><td class="fw-semibold"><?= format_datetime($venta['fecha_venta']) ?></td></tr>
                            <tr><td class="text-muted ps-0">Moneda</td><td class="fw-semibold"><?= safe_string($venta['moneda'] ?? 'MXN') ?></td></tr>
                            <tr><td class="text-muted ps-0">Estatus</td>
                                <td>
                                    <span class="badge bg-<?= $venta['estatus'] === 'completado' ? 'success' : 'warning' ?>">
                                        <?= $venta['estatus'] === 'completado' ? 'Completado' : 'Pendiente' ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm borderless mb-0">
                            <tr><td class="text-muted ps-0">Cliente</td><td class="fw-semibold"><?= safe_string($venta['razon_social'] ?? 'N/A') ?></td></tr>
                            <tr><td class="text-muted ps-0">RFC</td><td class="fw-semibold"><?= safe_string($venta['rfc'] ?? 'N/A') ?></td></tr>
                            <tr><td class="text-muted ps-0">Ubicaci&oacute;n</td><td class="fw-semibold"><?= safe_string(($venta['ciudad'] ?? '') . ', ' . ($venta['estado'] ?? '')) ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-box-seam"></i> Producto
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-light flex-shrink-0" style="width: 80px; height: 80px;">
                        <i class="bi bi-box-seam" style="font-size: 2.5rem; color: #667eea;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-1"><?= safe_string($venta['producto_nombre'] ?? 'N/A') ?></h4>
                        <?php if (!empty($venta['producto_codigo'])): ?>
                        <p class="text-muted mb-2">C&oacute;digo: <?= safe_string($venta['producto_codigo']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($venta['descripcion_comercial'])): ?>
                        <p class="mb-2"><?= nl2br(safe_string($venta['descripcion_comercial'])) ?></p>
                        <?php endif; ?>
                        <div class="d-flex gap-3 flex-wrap">
                            <?php if (!empty($venta['familia'])): ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary"><?= safe_string($venta['familia']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($venta['linea'])): ?>
                            <span class="badge bg-info bg-opacity-10 text-info"><?= safe_string($venta['linea']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($venta['color'])): ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= safe_string($venta['color']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($venta['peso_unitario_grs'])): ?>
                            <span class="badge bg-light text-dark"><?= $venta['peso_unitario_grs'] ?> gr</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna derecha: total + ticket + acciones -->
    <div class="col-md-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-receipt"></i> Resumen
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Producto</span>
                    <span class="fw-semibold"><?= safe_string($venta['producto_nombre'] ?? 'N/A') ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Cantidad</span>
                    <span class="fw-semibold"><?= $venta['cantidad_vendida'] ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Precio Unitario</span>
                    <span class="fw-semibold"><?= format_money($venta['precio_unitario']) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between py-2">
                    <span class="h5 mb-0">Total</span>
                    <span class="h5 mb-0 fw-bold text-primary"><?= format_money($venta['cantidad_vendida'] * $venta['precio_unitario']) ?></span>
                </div>
            </div>
        </div>

        <?php if ($ticket): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-ticket"></i> Ticket Electr&oacute;nico
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <small class="text-muted d-block">Folio &uacute;nico</small>
                        <strong class="fs-5" style="letter-spacing: 1px;"><?= safe_string($ticket['folio_unico']) ?></strong>
                    </div>
                    <span class="badge bg-<?= $ticket['estatus'] === 'emitido' ? 'success' : 'secondary' ?> fs-6">
                        <?= $ticket['estatus'] === 'emitido' ? 'Emitido' : 'Cancelado' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted small">Emisi&oacute;n:</span>
                    <span class="small"><?= format_datetime($ticket['fecha_emision']) ?></span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('tickets/' . $ticket['folio_unico']) ?>" class="btn btn-outline-info btn-sm flex-fill" target="_blank">
                        <i class="bi bi-eye"></i> Ver Ticket
                    </a>
                    <a href="<?= url('tickets/' . $ticket['folio_unico'] . '/pdf') ?>" class="btn btn-dark btn-sm flex-fill" target="_blank">
                        <i class="bi bi-download"></i> Descargar PDF
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-file-text"></i> Facturaci&oacute;n
            </div>
            <div class="card-body">
                <?php if ($tiene_solicitud): ?>
                <div class="alert alert-success mb-0 py-2 small">
                    <i class="bi bi-check-circle"></i> Ya solicitaste la factura para esta compra.
                </div>
                <?php elseif ($venta['estatus'] === 'completado'): ?>
                <p class="small text-muted mb-3">Solicita tu factura electr&oacute;nica para esta compra.</p>
                <form method="POST" action="<?= url('facturas/request/' . $venta['id_venta']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-receipt"></i> Solicitar Factura</button>
                </form>
                <?php else: ?>
                <p class="small text-muted mb-0">
                    <i class="bi bi-info-circle"></i> La factura estar&aacute; disponible cuando la venta est&eacute; completada.
                </p>
                <?php endif; ?>
                <hr>
                <a href="<?= url('factura/solicitar/' . ($ticket['folio_unico'] ?? '')) ?>" class="btn btn-sm btn-outline-secondary w-0 <?= empty($ticket['folio_unico']) ? 'disabled' : '' ?>">
                    <i class="bi bi-box-arrow-up-right"></i> Facturaci&oacute;n p&uacute;blica
                </a>
            </div>
        </div>
    </div>
</div>
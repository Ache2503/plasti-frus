<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clock-history"></i> Mis Compras</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('catalogo') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-shop"></i> Tienda</a>
        <a href="<?= url('mis-compras') ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-box-seam"></i> Mis Pedidos</a>
        <span class="badge bg-secondary fs-6"><?= safe_string($rol_nombre) ?></span>
    </div>
</div>

<?php if (!$cliente): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-circle"></i> Tu cuenta de cliente no est&aacute; vinculada a un registro de cliente. Contacta al administrador.
</div>
<?php return; endif; ?>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= $total_compras['total'] ?? 0 ?></div>
                <div class="stat-label"><i class="bi bi-cart-check"></i> Total Compras</div>
            </div>
            <i class="bi bi-cart-check stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($total_compras['monto'] ?? 0) ?></div>
                <div class="stat-label"><i class="bi bi-cash-coin"></i> Total Invertido</div>
            </div>
            <i class="bi bi-cash-coin stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card card-info">
            <div class="stat-content">
                <div class="stat-number"><?= safe_string($cliente['razon_social'] ?? 'N/A') ?></div>
                <div class="stat-label"><i class="bi bi-building"></i> Cliente</div>
            </div>
            <i class="bi bi-building stat-icon"></i>
        </div>
    </div>
</div>

<!-- Historial de Compras -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-receipt"></i> Historial de Compras</span>
        <span class="badge bg-light text-dark"><?= count($mis_ventas) ?> registro(s)</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($mis_ventas)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
            <p class="mt-2 text-muted">A&uacute;n no has realizado compras.</p>
            <a href="<?= url('catalogo') ?>" class="btn btn-dark"><i class="bi bi-shop"></i> Ir a la Tienda</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="comprasTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">P. Unit.</th>
                        <th class="text-end">Total</th>
                        <th>Fecha</th>
                        <th>Estatus</th>
                        <th class="text-center">Ticket</th>
                        <th class="text-center">Factura</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mis_ventas as $v): 
                        $totalVenta = $v['cantidad_vendida'] * $v['precio_unitario'];
                    ?>
                    <tr role="button" class="compra-row" onclick="window.location='<?= url('mis-compras/' . $v['id_venta']) ?>'" style="cursor: pointer;">
                        <td><?= $v['id_venta'] ?></td>
                        <td>
                            <strong><?= safe_string($v['producto_nombre'] ?? 'N/A') ?></strong>
                            <?php if (!empty($v['producto_codigo'])): ?>
                            <br><small class="text-muted"><?= safe_string($v['producto_codigo']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $v['cantidad_vendida'] ?></td>
                        <td class="text-end"><?= format_money($v['precio_unitario']) ?></td>
                        <td class="text-end"><strong><?= format_money($totalVenta) ?></strong></td>
                        <td><?= format_date($v['fecha_venta']) ?></td>
                        <td>
                            <span class="badge bg-<?= $v['estatus'] === 'completado' ? 'success' : 'warning' ?>">
                                <?= $v['estatus'] === 'completado' ? 'Completado' : 'Pendiente' ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if (!empty($v['folio_unico'])): ?>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?= url('tickets/' . $v['folio_unico']) ?>" class="btn btn-sm btn-outline-info" target="_blank" title="Ver Ticket">
                                    <i class="bi bi-receipt"></i>
                                </a>
                                <a href="<?= url('tickets/' . $v['folio_unico'] . '/pdf') ?>" class="btn btn-sm btn-outline-dark" target="_blank" title="Descargar PDF">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                            <?php else: ?>
                            <span class="text-muted small">&mdash;</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if (in_array($v['id_venta'], $ids_con_solicitud)): ?>
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle"></i> Facturado
                            </span>
                            <?php elseif ($v['estatus'] === 'completado'): ?>
                            <form method="POST" action="<?= url('facturas/request/' . $v['id_venta']) ?>" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-text"></i> Factura
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted small">&mdash;</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Solicitudes de Factura -->
<?php if (!empty($solicitudes)): ?>
<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <span><i class="bi bi-file-text"></i> Solicitudes de Factura</span>
        <span class="badge bg-dark text-white"><?= count($solicitudes) ?> solicitude(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="text-end">Monto</th>
                        <th>Fecha Venta</th>
                        <th>Solicitada</th>
                        <th>Estatus</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes as $s): ?>
                    <tr>
                        <td><?= $s['id_solicitud'] ?></td>
                        <td><?= safe_string($s['producto_nombre'] ?? 'N/A') ?></td>
                        <td class="text-end"><?= format_money(($s['cantidad_vendida'] ?? 0) * ($s['precio_unitario'] ?? 0)) ?></td>
                        <td><?= format_date($s['fecha_venta']) ?></td>
                        <td><?= format_datetime($s['fecha_solicitud']) ?></td>
                        <td>
                            <span class="badge bg-<?= $s['estatus'] === 'procesada' ? 'success' : 'warning' ?>">
                                <?= $s['estatus'] === 'procesada' ? 'Procesada' : 'Pendiente' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($s['estatus'] === 'pendiente'): ?>
                            <form method="POST" action="<?= url('facturas/cancelar/' . $s['id_solicitud']) ?>" onsubmit="return confirm('&iquest;Cancelar solicitud?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.compra-row:hover { background: rgba(102, 126, 234, 0.08) !important; }
.compra-row td:first-child { font-weight: 700; color: var(--accent); }
</style>
<script>
$(document).ready(function() {
    if ($('#comprasTable').length) {
        $('#comprasTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'desc']]
        });
    }
});
</script>
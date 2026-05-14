<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person-circle"></i> Mi Panel</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('cartera') ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-wallet2"></i> Mi Cartera</a>
        <a href="<?= url('factura') ?>" class="btn btn-sm btn-outline-info" target="_blank"><i class="bi bi-receipt"></i> Facturación</a>
        <span class="badge bg-secondary fs-6"><?= safe_string($rol_nombre) ?></span>
    </div>
</div>

<?php if ($cliente): ?>

<?php if (empty($cliente['id_vendedor'])): ?>
<div class="alert alert-info d-flex align-items-center gap-2">
    <i class="bi bi-info-circle"></i>
    <span>Aún no tienes un vendedor asignado. <a href="#vendedorSection" class="fw-semibold">Selecciona uno aquí</a>.</span>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="<?= url('mis-compras') ?>" class="text-decoration-none">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= $total_compras['total'] ?? 0 ?></div>
                <div class="stat-label"><i class="bi bi-cart-check"></i> Compras</div>
            </div>
            <i class="bi bi-cart-check stat-icon"></i>
        </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($total_compras['monto'] ?? 0) ?></div>
                <div class="stat-label"><i class="bi bi-cash-coin"></i> Total Invertido</div>
            </div>
            <i class="bi bi-cash-coin stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-warning">
            <div class="stat-content">
                <div class="stat-number"><?= count($solicitudes) ?></div>
                <div class="stat-label"><i class="bi bi-file-text"></i> Facturas Solicitadas</div>
            </div>
            <i class="bi bi-file-text stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-info">
            <div class="stat-content">
                <div class="stat-label"><i class="bi bi-building"></i> Cliente</div>
                <div class="stat-number" style="font-size: .85rem;"><?= safe_string($cliente['razon_social'] ?? 'N/A') ?></div>
            </div>
            <i class="bi bi-building stat-icon"></i>
        </div>
    </div>
</div>

<!-- Sección: Mis Pedidos (agrupados) -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-box-seam"></i> Mis Pedidos</span>
        <a href="<?= url('mis-compras') ?>" class="btn btn-sm btn-light">Ver todos</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($mis_pedidos)): ?>
        <div class="text-center py-4">
            <i class="bi bi-box-seam text-muted" style="font-size: 2.5rem;"></i>
            <p class="mt-2 text-muted">Aún no has realizado pedidos. <a href="<?= url('catalogo') ?>">Explora la tienda</a>.</p>
        </div>
        <?php else: ?>
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
                            <small class="text-muted d-block"><?= safe_string(truncate($p['productos_resumen'] ?? '', 40)) ?></small>
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
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#historialModal<?= $p['id_pedido'] ?>" title="Ver historial">
                                <i class="bi bi-clock-history"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modales de historial por pedido -->
<?php foreach ($mis_pedidos as $p):
    $historial = $historial_pedidos[$p['id_pedido']] ?? [];
?>
<div class="modal fade" id="historialModal<?= $p['id_pedido'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); color: #fff;">
                <h6 class="modal-title"><i class="bi bi-clock-history"></i> Pedido #<?= $p['id_pedido'] ?> — <?= safe_string($p['folio']) ?></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="background: #f8f9fa;">
                <?php if (empty($historial)): ?>
                <p class="text-muted small mb-0">Sin historial disponible.</p>
                <?php else: ?>
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
                <?php endif; ?>
                <hr>
                <p class="small text-muted mb-0"><strong>Total:</strong> <?= format_money($p['total']) ?></p>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Sección: Mis compras (detalle individual) -->
<div class="card shadow-sm mb-4" id="mis-compras">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history"></i> &Uacute;ltimas Compras</span>
        <a href="<?= url('mis-compras') ?>" class="btn btn-sm btn-light">Ver todas</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>#</th><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Total</th><th>Fecha</th><th>Estatus</th><th>Factura</th></tr></thead>
                <tbody>
                    <?php foreach ($mis_ventas as $v): ?>
                    <tr>
                        <td><?= $v['id_venta'] ?></td>
                        <td><?= safe_string($v['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= $v['cantidad_vendida'] ?></td>
                        <td><?= format_money($v['precio_unitario']) ?></td>
                        <td><?= format_money($v['cantidad_vendida'] * $v['precio_unitario']) ?></td>
                        <td><?= format_date($v['fecha_venta']) ?></td>
                        <td><span class="badge bg-<?= $v['estatus'] === 'completado' ? 'success' : 'warning' ?>"><?= safe_string($v['estatus']) ?></span></td>
                        <td>
                            <?php if (!empty($v['folio_unico'])): ?>
                            <div class="d-flex gap-1">
                                <a href="<?= url('tickets/' . $v['folio_unico']) ?>" class="btn btn-sm btn-outline-info" target="_blank" title="Ver ticket"><i class="bi bi-receipt"></i></a>
                                <a href="<?= url('tickets/' . $v['folio_unico'] . '/pdf') ?>" class="btn btn-sm btn-outline-dark" target="_blank" title="Descargar PDF"><i class="bi bi-download"></i></a>
                            </div>
                            <?php endif; ?>
                            <?php if (in_array($v['id_venta'], $ids_con_solicitud)): ?>
                            <span class="text-success small"><i class="bi bi-check-circle"></i> Facturado</span>
                            <?php elseif ($v['estatus'] === 'completado'): ?>
                            <form method="POST" action="<?= url('facturas/request/' . $v['id_venta']) ?>" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-text"></i> Factura</button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($mis_ventas)): ?>
                    <tr><td colspan="9" class="text-center text-muted">Aún no has realizado compras</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Sección: Solicitudes de factura -->
<?php if (!empty($solicitudes)): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning text-dark"><i class="bi bi-file-text"></i> Mis Solicitudes de Factura</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>#</th><th>Producto</th><th>Monto</th><th>Fecha Venta</th><th>Solicitada</th><th>Estatus</th><th></th></tr></thead>
                <tbody>
                    <?php foreach ($solicitudes as $s): ?>
                    <tr>
                        <td><?= $s['id_solicitud'] ?></td>
                        <td><?= safe_string($s['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= format_money(($s['cantidad_vendida'] ?? 0) * ($s['precio_unitario'] ?? 0)) ?></td>
                        <td><?= format_date($s['fecha_venta']) ?></td>
                        <td><?= format_datetime($s['fecha_solicitud']) ?></td>
                        <td><span class="badge bg-<?= $s['estatus'] === 'procesada' ? 'success' : 'warning' ?>"><?= safe_string($s['estatus']) ?></span></td>
                        <td>
                            <?php if ($s['estatus'] === 'pendiente'): ?>
                            <form method="POST" action="<?= url('facturas/cancelar/' . $s['id_solicitud']) ?>" style="display:inline" onsubmit="return confirm('¿Cancelar solicitud?')">
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

<!-- Sección: Seleccionar vendedor -->
<div class="card shadow-sm mb-4" id="vendedorSection">
    <div class="card-header bg-info text-white"><i class="bi bi-person-badge"></i> Mi Vendedor Asignado</div>
    <div class="card-body">
        <?php if ($vendedor_asignado): ?>
        <p class="mb-3">
            <strong>Vendedor actual:</strong>
            <?= safe_string($vendedor_asignado['nombre'] . ' ' . $vendedor_asignado['apellido_paterno']) ?>
            (<em><?= safe_string($vendedor_asignado['nombre_usuario']) ?></em>)
        </p>
        <?php else: ?>
        <p class="mb-3 text-muted">No tienes un vendedor asignado aún.</p>
        <?php endif; ?>

        <?php if (!empty($vendedores)): ?>
        <form method="POST" action="<?= url('cliente/asignar-vendedor') ?>" class="row g-2 align-items-end">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="col-auto">
                <label class="form-label small fw-semibold mb-0">Seleccionar vendedor</label>
                <select name="id_vendedor" class="form-select">
                    <option value="">— Elegir —</option>
                    <?php foreach ($vendedores as $v): ?>
                    <option value="<?= $v['id_usuario'] ?>" <?= ($cliente['id_vendedor'] ?? '') == $v['id_usuario'] ? 'selected' : '' ?>>
                        <?= safe_string(($v['nombre'] ?? $v['nombre_usuario']) . ' ' . ($v['apellido_paterno'] ?? '')) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-dark"><i class="bi bi-check-lg"></i> Asignar</button>
            </div>
        </form>
        <?php else: ?>
        <p class="text-muted small mb-0">No hay vendedores disponibles actualmente.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Sección: Datos de la cuenta -->
<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white"><i class="bi bi-person-lines-fill"></i> Datos de mi Cuenta</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Razón Social:</strong><br> <?= safe_string($cliente['razon_social'] ?? 'N/A') ?></p>
                <p><strong>RFC:</strong><br> <?= safe_string($cliente['rfc'] ?? 'N/A') ?></p>
                <p><strong>Teléfono:</strong><br> <?= safe_string($cliente['telefono'] ?? 'N/A') ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Correo:</strong><br> <?= safe_string($cliente['correo'] ?? 'N/A') ?></p>
                <p><strong>Ubicación:</strong><br> <?= safe_string(($cliente['ciudad'] ?? '') . ', ' . ($cliente['estado'] ?? '')) ?></p>
                <?php if (!empty($cliente['domicilio'])): ?>
                <p><strong>Domicilio:</strong><br> <?= safe_string($cliente['domicilio']) ?><?= !empty($cliente['codigo_postal']) ? ', CP ' . safe_string($cliente['codigo_postal']) : '' ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-2">
            <a href="<?= url('profile') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-pencil"></i> Editar perfil</a>
        </div>
    </div>
</div>

<?php else: ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-circle"></i> Tu cuenta de cliente no está vinculada a un registro de cliente. Contacta al administrador.
</div>
<?php endif; ?>

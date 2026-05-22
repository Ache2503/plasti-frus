<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cash-coin"></i> Ventas</h1>
    <a href="<?= url('ventas/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Venta</a>
</div>

<?php if (empty($ventas)): ?>
<div class="alert alert-info text-center py-4">
    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
    No hay registros disponibles.
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead>
                    <tr><th>ID</th><th>Cliente</th><th>Producto</th><th>Cantidad</th><th>Precio Unit.</th><th>Total</th><th>Fecha</th><th>Vendedor</th><th>Estatus</th><th>Ticket</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($ventas as $v): ?>
                    <tr>
                        <td><?= $v['id_venta'] ?></td>
                        <td><?= safe_string($v['cliente'] ?? 'N/A') ?></td>
                        <td><?= safe_string($v['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= $v['cantidad_vendida'] ?></td>
                        <td><?= format_money($v['precio_unitario'], $v['moneda'] ?? 'MXN') ?></td>
                        <td><?= format_money($v['cantidad_vendida'] * $v['precio_unitario'], $v['moneda'] ?? 'MXN') ?></td>
                        <td><?= format_date($v['fecha_venta']) ?></td>
                        <td><?= safe_string($v['vendedor_nombre'] ?? '—') ?></td>
                        <td><span class="badge bg-<?= $v['estatus'] === 'completado' ? 'success' : ($v['estatus'] === 'pendiente' ? 'warning' : 'secondary') ?>"><?= safe_string($v['estatus']) ?></span></td>
                        <td>
                            <?php if (!empty($v['folio_unico'])): ?>
                            <a href="<?= url('tickets/' . $v['folio_unico']) ?>" class="btn btn-sm btn-outline-info" target="_blank"><i class="bi bi-receipt"></i></a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!es_contador()): ?>
                            <a href="<?= url('ventas/edit/' . $v['id_venta']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= url('ventas/delete/' . $v['id_venta']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar venta?')"><i class="bi bi-trash"></i></button>
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
<?php require __DIR__ . '/../partials/pagination.php'; ?>
<?php endif; ?>

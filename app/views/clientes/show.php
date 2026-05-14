<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person-badge"></i> <?= safe_string($cliente['razon_social']) ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= url('clientes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        <a href="<?= url('ventas/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-cash-coin"></i> Nueva Venta</a>
    </div>
</div>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-info-circle"></i> Datos del Cliente</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">RFC</dt>
                    <dd class="col-sm-7"><?= safe_string($cliente['rfc'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Ciudad</dt>
                    <dd class="col-sm-7"><?= safe_string($cliente['ciudad'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Estado</dt>
                    <dd class="col-sm-7"><?= safe_string($cliente['estado'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Teléfono</dt>
                    <dd class="col-sm-7"><?= safe_string($cliente['telefono'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Correo</dt>
                    <dd class="col-sm-7"><?= safe_string($cliente['correo'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Sector</dt>
                    <dd class="col-sm-7"><?= safe_string($cliente['sector'] ?? '—') ?></dd>

                    <dt class="col-sm-5">Vendedor</dt>
                    <dd class="col-sm-7"><?= safe_string($cliente['vendedor_nombre'] ?? '—') ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-clock-history"></i> Últimas Ventas</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>#</th><th>Producto</th><th>Cant.</th><th>Total</th><th>Fecha</th><th>Ticket</th></tr></thead>
                        <tbody>
                            <?php foreach ($ventas as $v): ?>
                            <tr>
                                <td><?= $v['id_venta'] ?></td>
                                <td><?= safe_string($v['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= $v['cantidad_vendida'] ?></td>
                                <td><?= format_money($v['cantidad_vendida'] * $v['precio_unitario']) ?></td>
                                <td><?= format_date($v['fecha_venta']) ?></td>
                                <td>
                                    <?php if (!empty($v['folio_unico'])): ?>
                                    <a href="<?= url('tickets/' . $v['folio_unico']) ?>" class="btn btn-sm btn-outline-info" target="_blank"><i class="bi bi-receipt"></i></a>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ventas)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Sin ventas registradas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-graph-up"></i> Dashboard Comercial</h1>
    <div class="d-flex align-items-center gap-2">
        <?php if ($notificaciones_no_leidas > 0): ?>
        <span class="badge bg-danger rounded-pill"><i class="bi bi-bell-fill"></i> <?= $notificaciones_no_leidas ?></span>
        <?php endif; ?>
        <span class="badge bg-info"><?= safe_string($rol_nombre) ?></span>
    </div>
</div>

<?php if (!empty($notificaciones)): ?>
<div class="alert mb-3 py-1 px-2
    <?= $notificaciones_no_leidas > 0 ? 'alert-info' : 'alert-light' ?>"
    role="alert" style="border-left: 4px solid var(--bs-info);">
    <div class="d-flex align-items-center gap-1 flex-wrap" style="font-size:0.85rem;">
        <i class="bi bi-bell <?= $notificaciones_no_leidas > 0 ? 'text-info' : 'text-muted' ?>"></i>
        <span class="fw-bold small">Notificaciones:</span>
        <?php foreach ($notificaciones as $n): ?>
        <span class="badge bg-<?= $n['leida'] ? 'secondary' : 'info' ?> text-dark px-1 py-0" style="font-size:0.75rem;" title="<?= safe_string($n['mensaje']) ?>">
            <?= safe_string($n['titulo']) ?>
        </span>
        <?php endforeach; ?>
        <?php if ($notificaciones_no_leidas > 0): ?>
        <form method="POST" action="<?= url('notificaciones/marcar-leidas') ?>" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" style="font-size:0.75rem;"><i class="bi bi-check-all"></i></button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3 mb-0">
        <div class="stat-card card-primary px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $total_clientes ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-people"></i> Mis Clientes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-0">
        <div class="stat-card card-success px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $total_ventas_mes['total'] ?? 0 ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-cash-coin"></i> Ventas del Mes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-0">
        <div class="stat-card card-warning px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= format_money($comisiones_resumen['pendiente'] ?? 0) ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-hourglass-split"></i> Comisiones Pend.</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-0">
        <div class="stat-card card-info px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $nuevos_clientes_mes ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-person-plus"></i> Nuevos Clientes</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-cash-coin"></i> Facturación del Mes</small></div>
            <div class="card-body text-center py-2">
                <span style="font-size:1.4rem; font-weight: 700; color: var(--bs-success);"><?= format_money($total_ventas_mes['monto'] ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white py-1 px-2"><small><i class="bi bi-coin"></i> Comisiones</small></div>
            <div class="card-body text-center py-2">
                <div class="row g-0">
                    <div class="col-6">
                        <small class="text-muted">Pendiente</small>
                        <div style="font-size:1rem; font-weight:600; color:var(--bs-warning);"><?= format_money($comisiones_resumen['pendiente'] ?? 0) ?></div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Pagado</small>
                        <div style="font-size:1rem; font-weight:600; color:var(--bs-success);"><?= format_money($comisiones_resumen['pagado'] ?? 0) ?></div>
                    </div>
                </div>
                <a href="<?= url('mis-comisiones') ?>" class="btn btn-sm btn-outline-dark mt-1 py-0 px-2"><small>Ver Detalle</small></a>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-dark py-1 px-2"><small><i class="bi bi-bar-chart-line"></i> Evolución Mensual</small></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.8rem;">
                        <thead><tr><th>Mes</th><th>Ventas</th><th>Monto</th></tr></thead>
                        <tbody>
                            <?php $maxVentas = max(array_column($ventas_mensuales, 'monto') ?: [1]); ?>
                            <?php foreach ($ventas_mensuales as $vm): ?>
                            <tr>
                                <td><?= format_date($vm['mes'] . '-01', 'M-Y') ?></td>
                                <td><?= $vm['total_ventas'] ?></td>
                                <td><?= format_money($vm['monto']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ventas_mensuales)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-2">
    <div class="col-12 mb-0">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-clock-history"></i> Ventas Recientes</small></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Producto</th><th>Cliente</th><th class="text-center">Cant.</th><th class="text-end">Total</th><th class="text-end">Fecha</th></tr></thead>
                    <tbody>
                        <?php foreach ($ventas_recientes as $v): ?>
                        <tr>
                            <td><?= safe_string($v['producto_nombre'] ?? 'N/A') ?></td>
                            <td><?= safe_string($v['cliente'] ?? 'N/A') ?></td>
                            <td class="text-center"><?= $v['cantidad_vendida'] ?></td>
                            <td class="text-end text-nowrap"><?= format_money($v['cantidad_vendida'] * $v['precio_unitario']) ?></td>
                            <td class="text-end text-nowrap"><?= format_date($v['fecha_venta']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ventas_recientes)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Sin ventas registradas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-2">
    <div class="col-12 col-sm-6 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-warning text-dark py-1 px-2"><small><i class="bi bi-trophy"></i> Top Clientes</small></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>#</th><th>Cliente</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        <?php $i = 1; foreach ($top_clientes as $tc): ?>
                        <tr>
                            <td class="text-muted"><?= $i++ ?></td>
                            <td><?= safe_string($tc['razon_social'] ?? 'N/A') ?></td>
                            <td class="text-end text-nowrap"><?= format_money($tc['total_gastado'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($top_clientes)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-upc-scan"></i> Top Productos</small></div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>#</th><th>Producto</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        <?php $i = 1; foreach ($top_productos as $tp): ?>
                        <tr>
                            <td class="text-muted"><?= $i++ ?></td>
                            <td><?= safe_string($tp['nombre'] ?? 'N/A') ?></td>
                            <td class="text-end text-nowrap"><?= format_money($tp['total_generado'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($top_productos)): ?>
                        <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-1">
            <a href="<?= url('mis-clientes') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-people"></i> Mis Clientes</a>
            <a href="<?= url('ventas') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-cash-coin"></i> Mis Ventas</a>
            <a href="<?= url('mis-comisiones') ?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-cash-stack"></i> Mis Comisiones</a>
            <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i> Buscar Clientes</a>
        </div>
    </div>
</div>

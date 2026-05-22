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
<div class="alert mb-3 py-1 px-2 <?= $notificaciones_no_leidas > 0 ? 'alert-info' : 'alert-light' ?>" role="alert" style="border-left: 4px solid var(--bs-info);">
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

<?php if (!empty($alertas)): ?>
<div class="mb-3">
    <?php foreach ($alertas as $alerta): ?>
    <div class="alert alert-<?= $alerta['tipo'] ?> py-1 px-2 mb-1" style="font-size:0.85rem; border-left: 4px solid var(--bs-<?= $alerta['tipo'] ?>);">
        <i class="bi <?= $alerta['icono'] ?>"></i> <?= safe_string($alerta['mensaje']) ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-2 mb-0">
        <div class="stat-card card-primary px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.1rem;"><?= $total_clientes ?></div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-people"></i> Mis Clientes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-0">
        <div class="stat-card card-success px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.1rem;"><?= $total_ventas_mes['total'] ?? 0 ?></div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-cash-coin"></i> Ventas del Mes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-0">
        <div class="stat-card card-warning px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.1rem;"><?= format_money($comisiones_resumen['pendiente'] ?? 0) ?></div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-hourglass-split"></i> Comisiones Pend.</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-0">
        <div class="stat-card card-info px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.1rem;"><?= $nuevos_clientes_mes ?></div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-person-plus"></i> Nuevos Clientes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-0">
        <div class="stat-card card-secondary px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.1rem;"><?= $tasa_conversion['tasa'] ?>%</div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-graph-up"></i> Conversión</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2 mb-0">
        <div class="stat-card card-dark px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.1rem;"><?= format_money($ticket_promedio) ?></div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-receipt"></i> Ticket Prom.</div>
            </div>
        </div>
    </div>
</div>

<?php if ($meta_mes > 0): ?>
<div class="row g-2 mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="fw-bold"><i class="bi bi-bullseye"></i> Meta Mensual: <?= format_money($meta_mes) ?></small>
                    <small class="<?= $avance_meta >= 100 ? 'text-success' : ($avance_meta >= 50 ? 'text-warning' : 'text-danger') ?>">
                        <?= $avance_meta ?>% completado
                    </small>
                </div>
                <div class="progress" style="height: 12px;">
                    <div class="progress-bar <?= $avance_meta >= 100 ? 'bg-success' : ($avance_meta >= 50 ? 'bg-warning' : 'bg-danger') ?>" role="progressbar" style="width: <?= min($avance_meta, 100) ?>%" aria-valuenow="<?= $avance_meta ?>" aria-valuemin="0" aria-valuemax="100">
                        <?= $avance_meta >= 10 ? $avance_meta . '%' : '' ?>
                    </div>
                </div>
                <small class="text-muted"><?= format_money($total_ventas_mes['monto'] ?? 0) ?> de <?= format_money($meta_mes) ?></small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-cash-coin"></i> Facturación del Mes</small></div>
            <div class="card-body text-center py-2">
                <span style="font-size:1.4rem; font-weight: 700; color: var(--bs-success);"><?= format_money($total_ventas_mes['monto'] ?? 0) ?></span>
                <div><small class="text-muted">Pipeline activo: <?= format_money($total_pipeline) ?></small></div>
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

<?php if (!empty($proximas_actividades)): ?>
<div class="row g-2 mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white py-1 px-2">
                <small><i class="bi bi-calendar-check"></i> Próximas Actividades</small>
                <?php if ($actividades_pendientes > 5): ?>
                <span class="badge bg-light text-dark ms-1">+<?= $actividades_pendientes - 5 ?> más</span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" style="font-size:0.8rem;">
                    <thead><tr><th>Tipo</th><th>Título</th><th>Fecha</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php foreach ($proximas_actividades as $act): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= safe_string($act['tipo']) ?></span></td>
                            <td><?= safe_string($act['titulo']) ?></td>
                            <td><?= format_date($act['fecha_hora'], 'd/m/Y H:i') ?></td>
                            <td><span class="badge bg-<?= $act['estado'] === 'pendiente' ? 'warning' : 'success' ?>"><?= safe_string($act['estado']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($pipeline_resumen)): ?>
<div class="row g-2 mb-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-funnel"></i> Pipeline por Etapa</small></div>
            <div class="card-body p-2">
                <div class="row g-1">
                    <?php $etapasLabels = ['prospeccion' => 'Prospección', 'contactado' => 'Contactado', 'propuesta' => 'Propuesta', 'negociacion' => 'Negociación', 'cerrado_ganado' => 'Ganado', 'cerrado_perdido' => 'Perdido']; ?>
                    <?php $etapasColores = ['prospeccion' => 'secondary', 'contactado' => 'info', 'propuesta' => 'primary', 'negociacion' => 'warning', 'cerrado_ganado' => 'success', 'cerrado_perdido' => 'danger']; ?>
                    <?php foreach ($pipeline_resumen as $pr): ?>
                    <div class="col-4 col-md-2 mb-1">
                        <div class="card card-<?= $etapasColores[$pr['etapa']] ?? 'secondary' ?> text-white text-center py-1" style="font-size:0.75rem;">
                            <div class="fw-bold"><?= $pr['total'] ?></div>
                            <small><?= safe_string($etapasLabels[$pr['etapa']] ?? $pr['etapa']) ?></small>
                            <small><?= format_money($pr['valor_total']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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
            <a href="<?= url('pipeline') ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-funnel"></i> Pipeline</a>
            <a href="<?= url('mis-clientes') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-people"></i> Mis Clientes</a>
            <a href="<?= url('ventas') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-cash-coin"></i> Mis Ventas</a>
            <a href="<?= url('mis-comisiones') ?>" class="btn btn-outline-warning btn-sm"><i class="bi bi-cash-stack"></i> Mis Comisiones</a>
            <a href="<?= url('agenda') ?>" class="btn btn-outline-info btn-sm"><i class="bi bi-calendar"></i> Mi Agenda</a>
            <a href="<?= url('clientes') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search"></i> Buscar Clientes</a>
            <a href="<?= url('reportes-vendedor') ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cash-stack"></i> Mis Comisiones</h1>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="stat-card card-warning">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($resumen['pendiente']) ?></div>
                <div class="stat-label"><i class="bi bi-hourglass-split"></i> Pendiente</div>
            </div>
            <i class="bi bi-hourglass-split stat-icon"></i>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($resumen['pagado']) ?></div>
                <div class="stat-label"><i class="bi bi-check-circle"></i> Pagado</div>
            </div>
            <i class="bi bi-check-circle stat-icon"></i>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white"><i class="bi bi-list"></i> Historial de Comisiones</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>#</th><th>Venta</th><th>Cliente</th><th>% Comisión</th><th>Monto</th><th>Estatus</th><th>Fecha Cálculo</th></tr></thead>
                <tbody>
                    <?php foreach ($comisiones as $c): ?>
                    <tr>
                        <td><?= $c['id_comision'] ?></td>
                        <td><?= safe_string($c['venta_folio'] ?? '#'.$c['id_venta']) ?></td>
                        <td><?= safe_string($c['cliente'] ?? 'N/A') ?></td>
                        <td><?= $c['porcentaje_comision'] ?>%</td>
                        <td><?= format_money($c['monto_comision']) ?></td>
                        <td>
                            <span class="badge bg-<?= $c['estatus'] === 'pagada' ? 'success' : ($c['estatus'] === 'pendiente' ? 'warning' : 'secondary') ?>">
                                <?= safe_string($c['estatus']) ?>
                            </span>
                        </td>
                        <td><?= format_date($c['fecha_calculo']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($comisiones)): ?>
                    <tr><td colspan="7" class="text-center text-muted">Sin comisiones registradas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-calculator"></i> Dashboard Contabilidad</h1>
    <span class="badge bg-info text-dark fs-6"><?= safe_string($rol_nombre) ?></span>
</div>

<?php if (!empty($alertas)): ?>
<div class="mb-3">
    <?php foreach ($alertas as $alerta): ?>
    <div class="alert alert-<?= safe_string($alerta['tipo']) ?> d-flex align-items-center gap-2 py-2 mb-2">
        <i class="bi <?= safe_string($alerta['icono']) ?>"></i>
        <span><?= $alerta['mensaje'] ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="<?= url('contabilidad/plan-cuentas') ?>" class="text-decoration-none">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= $total_cuentas ?></div>
                <div class="stat-label"><i class="bi bi-book"></i> Cuentas</div>
            </div>
            <i class="bi bi-book stat-icon"></i>
        </div></a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= url('contabilidad/polizas') ?>" class="text-decoration-none">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= $total_polizas_mes ?></div>
                <div class="stat-label"><i class="bi bi-file-earmark-text"></i> Pólizas del Mes</div>
            </div>
            <i class="bi bi-file-earmark-text stat-icon"></i>
        </div></a>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-warning">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($utilidad_mes) ?></div>
                <div class="stat-label"><i class="bi bi-bar-chart"></i> Utilidad del Mes</div>
            </div>
            <i class="bi bi-bar-chart stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= url('facturas/solicitudes') ?>" class="text-decoration-none">
        <div class="stat-card card-danger">
            <div class="stat-content">
                <div class="stat-number"><?= $total_facturas_pendientes ?></div>
                <div class="stat-label"><i class="bi bi-file-text"></i> Facturas Pendientes</div>
            </div>
            <i class="bi bi-file-text stat-icon"></i>
        </div></a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white"><i class="bi bi-pie-chart"></i> Estructura del Balance</div>
            <div class="card-body">
                <canvas id="balanceChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white"><i class="bi bi-cash-coin"></i> Resumen Financiero</div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6"><strong>Total Activos:</strong></div>
                    <div class="col-6 text-end fw-bold text-primary"><?= format_money($total_activos) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><strong>Total Pasivos:</strong></div>
                    <div class="col-6 text-end fw-bold text-warning"><?= format_money($total_pasivos) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><strong>Capital / Patrimonio:</strong></div>
                    <div class="col-6 text-end fw-bold text-success"><?= format_money($total_patrimonio) ?></div>
                </div>
                <hr>
                <div class="row mb-2">
                    <div class="col-6"><strong>Ingresos del Mes:</strong></div>
                    <div class="col-6 text-end text-success"><?= format_money($ingresos_mes) ?></div>
                </div>
                <div class="row mb-2">
                    <div class="col-6"><strong>Gastos del Mes:</strong></div>
                    <div class="col-6 text-end text-danger"><?= format_money($gastos_mes) ?></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6"><strong>Flujo de Efectivo:</strong></div>
                    <div class="col-6 text-end fw-bold <?= $flujo_efectivo >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= format_money($flujo_efectivo) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><i class="bi bi-clock-history"></i> Últimas Pólizas</span>
                <a href="<?= url('contabilidad/polizas') ?>" class="btn btn-sm btn-outline-light">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Folio</th><th>Fecha</th><th>Tipo</th><th>Concepto</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            <?php foreach ($ultimas_polizas as $p): ?>
                            <tr>
                                <td><a href="<?= url('contabilidad/polizas/show/' . $p['id_poliza']) ?>"><?= safe_string($p['folio']) ?></a></td>
                                <td><?= format_date($p['fecha']) ?></td>
                                <td><span class="badge bg-<?= $p['tipo'] === 'ingreso' ? 'success' : ($p['tipo'] === 'egreso' ? 'danger' : 'info') ?>"><?= safe_string($p['tipo']) ?></span></td>
                                <td><?= safe_string(truncate($p['concepto'], 60)) ?></td>
                                <td class="text-end"><?= format_money($p['total_cargo'] ?? 0) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ultimas_polizas)): ?>
                            <tr><td colspan="5" class="text-center text-muted">Sin pólizas registradas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-secondary text-white"><i class="bi bi-grid-3x3-gap-fill"></i> Acceso Rápido</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('contabilidad/polizas/create') ?>" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Nueva Póliza</a>
                    <a href="<?= url('contabilidad/plan-cuentas') ?>" class="btn btn-outline-dark"><i class="bi bi-book"></i> Plan de Cuentas</a>
                    <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-outline-dark"><i class="bi bi-list-check"></i> Balanza</a>
                    <a href="<?= url('contabilidad/balance-general') ?>" class="btn btn-outline-dark"><i class="bi bi-file-earmark-text"></i> Balance General</a>
                    <a href="<?= url('contabilidad/estado-resultados') ?>" class="btn btn-outline-dark"><i class="bi bi-bar-chart"></i> Estado Resultados</a>
                    <a href="<?= url('contabilidad/flujo-efectivo') ?>" class="btn btn-outline-dark"><i class="bi bi-cash-stack"></i> Flujo de Efectivo</a>
                    <a href="<?= url('contabilidad/presupuestos') ?>" class="btn btn-outline-dark"><i class="bi bi-calculator"></i> Presupuestos</a>
                </div>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-calendar-check"></i> Periodo Actual</div>
            <div class="card-body">
                <?php if ($periodo_actual): ?>
                <div class="d-flex justify-content-between mb-1">
                    <span>Periodo:</span>
                    <span><?= str_pad($periodo_actual['mes'], 2, '0', STR_PAD_LEFT) ?>/<?= $periodo_actual['anio'] ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Estatus:</span>
                    <?php if ($periodo_actual['cerrado']): ?>
                    <span class="badge bg-secondary">Cerrado</span>
                    <?php else: ?>
                    <span class="badge bg-success">Abierto</span>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Sin periodo definido</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('balanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Activos', 'Pasivos', 'Patrimonio'],
            datasets: [{
                data: [<?= max(0, $total_activos) ?>, <?= max(0, $total_pasivos) ?>, <?= max(0, $total_patrimonio) ?>],
                backgroundColor: ['#0d6efd', '#ffc107', '#198754'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

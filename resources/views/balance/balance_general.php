<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-file-earmark-text"></i> Balance General</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('contabilidad/estado-resultados') ?>" class="btn btn-sm btn-outline-dark">Estado Resultados</a>
        <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-sm btn-outline-dark">Balanza</a>
        <a href="<?= url('exportar/csv/balance?fecha=' . $fecha) ?>" class="btn btn-sm btn-outline-success" target="_blank"><i class="bi bi-download"></i> CSV</a>
        <a href="<?= url('exportar/pdf/balance?fecha=' . $fecha) ?>" class="btn btn-sm btn-outline-dark" target="_blank"><i class="bi bi-printer"></i> PDF</a>
    </div>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="date" name="fecha" class="form-control form-control-sm" value="<?= safe_string($fecha) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Actualizar</button>
    </div>
</form>

<?php
$activoSum = 0; $pasivoSum = 0; $capitalSum = 0;
foreach ($cuentas as $c) {
    if ($c['tipo'] === 'activo') $activoSum += $c['saldo'];
    if ($c['tipo'] === 'pasivo') $pasivoSum += $c['saldo'];
    if ($c['tipo'] === 'capital') $capitalSum += $c['saldo'];
}
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($activoSum) ?></div>
                <div class="stat-label"><i class="bi bi-box"></i> Total Activo</div>
            </div>
            <i class="bi bi-box stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card card-warning">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($pasivoSum) ?></div>
                <div class="stat-label"><i class="bi bi-credit-card"></i> Total Pasivo</div>
            </div>
            <i class="bi bi-credit-card stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($capitalSum) ?></div>
                <div class="stat-label"><i class="bi bi-building"></i> Capital Contable</div>
            </div>
            <i class="bi bi-building stat-icon"></i>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-primary text-white"><i class="bi bi-box"></i> ACTIVO</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Código</th><th>Cuenta</th><th class="text-end">Saldo</th></tr></thead>
                    <tbody>
                        <?php foreach ($cuentas as $c): ?>
                        <?php if ($c['tipo'] !== 'activo') continue; ?>
                        <tr>
                            <td><code><?= safe_string($c['codigo']) ?></code></td>
                            <td style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px"><?= safe_string($c['nombre']) ?></td>
                            <td class="text-end"><?= $c['saldo'] != 0 ? format_money($c['saldo']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr class="fw-bold table-primary"><td colspan="2">Total Activo</td><td class="text-end"><?= format_money($activoSum) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-warning text-dark"><i class="bi bi-credit-card"></i> PASIVO</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Código</th><th>Cuenta</th><th class="text-end">Saldo</th></tr></thead>
                    <tbody>
                        <?php foreach ($cuentas as $c): ?>
                        <?php if ($c['tipo'] !== 'pasivo') continue; ?>
                        <tr>
                            <td><code><?= safe_string($c['codigo']) ?></code></td>
                            <td style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px"><?= safe_string($c['nombre']) ?></td>
                            <td class="text-end"><?= $c['saldo'] != 0 ? format_money($c['saldo']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr class="fw-bold table-warning"><td colspan="2">Total Pasivo</td><td class="text-end"><?= format_money($pasivoSum) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white"><i class="bi bi-building"></i> CAPITAL CONTABLE</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Código</th><th>Cuenta</th><th class="text-end">Saldo</th></tr></thead>
                    <tbody>
                        <?php foreach ($cuentas as $c): ?>
                        <?php if ($c['tipo'] !== 'capital') continue; ?>
                        <tr>
                            <td><code><?= safe_string($c['codigo']) ?></code></td>
                            <td style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px"><?= safe_string($c['nombre']) ?></td>
                            <td class="text-end"><?= $c['saldo'] != 0 ? format_money($c['saldo']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr class="fw-bold table-dark"><td colspan="2">Total Capital</td><td class="text-end"><?= format_money($capitalSum) ?></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-dark text-white"><i class="bi bi-pie-chart"></i> Estructura Patrimonial</div>
            <div class="card-body">
                <canvas id="balancePieChart" height="250"></canvas>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white"><i class="bi bi-check-circle"></i> ECUACIÓN CONTABLE</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h5 class="text-primary">Activo</h5>
                        <h4><?= format_money($activoSum) ?></h4>
                    </div>
                    <div class="col-4">
                        <h5>=</h5>
                    </div>
                    <div class="col-4">
                        <h5 class="text-warning">Pasivo + Capital</h5>
                        <h4><?= format_money($pasivoSum + $capitalSum) ?></h4>
                    </div>
                </div>
                <hr>
                <?php $diff = abs($activoSum - ($pasivoSum + $capitalSum)); ?>
                <div class="text-center">
                    <span class="badge bg-<?= $diff < 0.01 ? 'success' : 'danger' ?> fs-6 p-2">
                        <?= $diff < 0.01 ? '✓ Balanceado' : '✗ Diferencia: ' . format_money($diff) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('balancePieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Activo', 'Pasivo', 'Capital'],
            datasets: [{
                data: [<?= max(0, $activoSum) ?>, <?= max(0, $pasivoSum) ?>, <?= max(0, $capitalSum) ?>],
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

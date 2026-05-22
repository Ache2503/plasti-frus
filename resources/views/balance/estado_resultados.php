<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-bar-chart"></i> Estado de Resultados</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('contabilidad/balance-general') ?>" class="btn btn-sm btn-outline-dark">Balance General</a>
        <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-sm btn-outline-dark">Balanza</a>
        <a href="<?= url('exportar/csv/resultados?anio=' . $anio) ?>" class="btn btn-sm btn-outline-success" target="_blank"><i class="bi bi-download"></i> CSV</a>
        <a href="<?= url('exportar/pdf/resultados?anio=' . $anio) ?>" class="btn btn-sm btn-outline-dark" target="_blank"><i class="bi bi-printer"></i> PDF</a>
    </div>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="anio" class="form-select form-select-sm">
            <?php for ($a = date('Y'); $a >= 2020; $a--): ?>
            <option value="<?= $a ?>" <?= $anio == $a ? 'selected' : '' ?>><?= $a ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Actualizar</button>
    </div>
    <div class="col-auto">
        <a href="<?= url('contabilidad/presupuestos/comparar?anio=' . $anio . '&mes=' . date('m')) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-graph-up"></i> Presupuesto vs Real</a>
    </div>
</form>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-success text-white d-flex justify-content-between">
        <span><i class="bi bi-arrow-up"></i> Ingresos</span>
        <span><?= count(array_filter($ingresos, fn($c) => $c['tipo'] === 'ingreso')) ?> cuenta(s)</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Código</th><th>Cuenta</th><th class="text-end">Saldo</th></tr></thead>
            <tbody>
                <?php $ingresoSum = 0; ?>
                <?php foreach ($ingresos as $c): ?>
                <?php if ($c['tipo'] !== 'ingreso') continue; ?>
                <?php $ingresoSum += $c['saldo']; ?>
                <tr>
                    <td><code><?= safe_string($c['codigo']) ?></code></td>
                    <td><?= safe_string($c['nombre']) ?></td>
                    <td class="text-end"><?= format_money($c['saldo']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if ($ingresoSum == 0): ?><tr><td colspan="3" class="text-center text-muted">Sin ingresos registrados</td></tr><?php endif; ?>
            </tbody>
            <tfoot><tr class="fw-bold"><td colspan="2">Total Ingresos</td><td class="text-end"><?= format_money($ingresoSum) ?></td></tr></tfoot>
        </table>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-danger text-white d-flex justify-content-between">
        <span><i class="bi bi-arrow-down"></i> Gastos</span>
        <span><?= count(array_filter($ingresos, fn($c) => $c['tipo'] === 'gasto')) ?> cuenta(s)</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Código</th><th>Cuenta</th><th class="text-end">Saldo</th></tr></thead>
            <tbody>
                <?php $gastoSum = 0; ?>
                <?php foreach ($ingresos as $c): ?>
                <?php if ($c['tipo'] !== 'gasto') continue; ?>
                <?php $gastoSum += $c['saldo']; ?>
                <tr>
                    <td><code><?= safe_string($c['codigo']) ?></code></td>
                    <td><?= safe_string($c['nombre']) ?></td>
                    <td class="text-end"><?= format_money($c['saldo']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if ($gastoSum == 0): ?><tr><td colspan="3" class="text-center text-muted">Sin gastos registrados</td></tr><?php endif; ?>
            </tbody>
            <tfoot><tr class="fw-bold"><td colspan="2">Total Gastos</td><td class="text-end"><?= format_money($gastoSum) ?></td></tr></tfoot>
        </table>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-dark text-white"><i class="bi bi-calculator"></i> Resultado del Ejercicio</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <h6>Total Ingresos</h6>
                <h5 class="text-success"><?= format_money($ingresoSum) ?></h5>
            </div>
            <div class="col-md-4">
                <h6>Total Gastos</h6>
                <h5 class="text-danger"><?= format_money($gastoSum) ?></h5>
            </div>
            <div class="col-md-4">
                <h6>Utilidad Neta</h6>
                <h5 class="<?= ($ingresoSum - $gastoSum) >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= format_money(abs($ingresoSum - $gastoSum)) ?>
                    <?= ($ingresoSum - $gastoSum) >= 0 ? '(Utilidad)' : '(Pérdida)' ?>
                </h5>
            </div>
        </div>
        <hr>
        <?php if (isset($total_presupuesto) && $total_presupuesto > 0): ?>
        <div class="row mt-2">
            <div class="col-md-6">
                <h6>Presupuesto Anual</h6>
                <h5 class="text-info"><?= format_money($total_presupuesto) ?></h5>
            </div>
            <div class="col-md-6">
                <h6>Variación</h6>
                <h5 class="<?= ($ingresoSum - $total_presupuesto) >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= format_money($ingresoSum - $total_presupuesto) ?>
                    (<?= $total_presupuesto > 0 ? number_format(($ingresoSum / $total_presupuesto) * 100, 1) : 0 ?>%)
                </h5>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

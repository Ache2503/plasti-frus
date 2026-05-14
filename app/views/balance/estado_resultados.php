<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-bar-chart"></i> Estado de Resultados</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('contabilidad/balance-general') ?>" class="btn btn-sm btn-outline-dark">Balance General</a>
        <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-sm btn-outline-dark">Balanza</a>
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
</form>

<div class="card shadow-sm">
    <div class="card-header bg-success text-white"><i class="bi bi-arrow-up"></i> Ingresos</div>
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
    <div class="card-header bg-danger text-white"><i class="bi bi-arrow-down"></i> Gastos</div>
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
            <div class="col-6"><h5>Utilidad Neta:</h5></div>
            <div class="col-6 text-end">
                <h5 class="<?= $utilidad >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= format_money(abs($utilidad)) ?>
                    <?= $utilidad >= 0 ? '(Utilidad)' : '(Pérdida)' ?>
                </h5>
            </div>
        </div>
    </div>
</div>

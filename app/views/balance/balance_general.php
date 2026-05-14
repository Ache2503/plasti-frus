<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-file-earmark-text"></i> Balance General</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('contabilidad/estado-resultados') ?>" class="btn btn-sm btn-outline-dark">Estado Resultados</a>
        <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-sm btn-outline-dark">Balanza</a>
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
?>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-primary text-white"><i class="bi bi-box"></i> ACTIVO</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Código</th><th>Cuenta</th><th class="text-end">Saldo</th></tr></thead>
            <tbody>
                <?php foreach ($cuentas as $c): ?>
                <?php if ($c['tipo'] !== 'activo') continue; ?>
                <?php $activoSum += $c['saldo']; ?>
                <tr style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px">
                    <td><code><?= safe_string($c['codigo']) ?></code></td>
                    <td style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px"><?= safe_string($c['nombre']) ?></td>
                    <td class="text-end"><?= $c['saldo'] != 0 ? format_money($c['saldo']) : '' ?></td>
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
                <?php $pasivoSum += $c['saldo']; ?>
                <tr style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px">
                    <td><code><?= safe_string($c['codigo']) ?></code></td>
                    <td style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px"><?= safe_string($c['nombre']) ?></td>
                    <td class="text-end"><?= $c['saldo'] != 0 ? format_money($c['saldo']) : '' ?></td>
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
                <?php $capitalSum += $c['saldo']; ?>
                <tr style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px">
                    <td><code><?= safe_string($c['codigo']) ?></code></td>
                    <td style="padding-left: <?= ($c['nivel'] - 1) * 20 ?>px"><?= safe_string($c['nombre']) ?></td>
                    <td class="text-end"><?= $c['saldo'] != 0 ? format_money($c['saldo']) : '' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot><tr class="fw-bold table-dark"><td colspan="2">Total Capital</td><td class="text-end"><?= format_money($capitalSum) ?></td></tr></tfoot>
        </table>
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
        <div class="text-center">
            <?php $diff = abs($activoSum - ($pasivoSum + $capitalSum)); ?>
            <span class="badge bg-<?= $diff < 0.01 ? 'success' : 'danger' ?> fs-6">
                <?= $diff < 0.01 ? '✓ Balanceado' : '✗ Diferencia: ' . format_money($diff) ?>
            </span>
        </div>
    </div>
</div>

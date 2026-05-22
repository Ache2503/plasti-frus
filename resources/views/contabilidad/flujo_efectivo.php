<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cash-stack"></i> Flujo de Efectivo</h1>
    <div class="d-flex gap-2 align-items-center">
        <form class="d-flex gap-2 align-items-center" method="GET">
            <select name="anio" class="form-select form-select-sm" style="width:100px">
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?= $y ?>" <?= $anio === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <select name="mes" class="form-select form-select-sm" style="width:100px">
                <option value="0">Anual</option>
                <?php foreach (range(1, 12) as $m): ?>
                <option value="<?= $m ?>" <?= $mes === $m ? 'selected' : '' ?>><?= str_pad((string) $m, 2, '0', STR_PAD_LEFT) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-dark">Filtrar</button>
        </form>
        <a href="<?= url('exportar/csv/flujo-efectivo?anio=' . $anio . ($mes > 0 ? '&mes=' . $mes : '')) ?>" class="btn btn-sm btn-outline-success" target="_blank"><i class="bi bi-download"></i> CSV</a>
        <a href="<?= url('exportar/pdf/flujo-efectivo?anio=' . $anio . ($mes > 0 ? '&mes=' . $mes : '')) ?>" class="btn btn-sm btn-outline-dark" target="_blank"><i class="bi bi-printer"></i> PDF</a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($saldo_inicial) ?></div>
                <div class="stat-label"><i class="bi bi-piggy-bank"></i> Saldo Inicial</div>
            </div>
            <i class="bi bi-piggy-bank stat-icon"></i>
        </div>
    </div>
    <?php
        $totalEntradas = 0; $totalSalidas = 0;
        foreach ($operacion as $r) { $totalEntradas += $r['total_abono']; $totalSalidas += $r['total_cargo']; }
        foreach ($inversion as $r) { $totalEntradas += $r['total_abono']; $totalSalidas += $r['total_cargo']; }
        foreach ($financiamiento as $r) { $totalEntradas += $r['total_abono']; $totalSalidas += $r['total_cargo']; }
        $flujoNeto = $totalEntradas - $totalSalidas;
    ?>
    <div class="col-md-4 mb-3">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($totalEntradas) ?></div>
                <div class="stat-label"><i class="bi bi-arrow-down-circle"></i> Entradas</div>
            </div>
            <i class="bi bi-arrow-down-circle stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card card-<?= $flujoNeto >= 0 ? 'success' : 'danger' ?>">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($saldo_inicial + $flujoNeto) ?></div>
                <div class="stat-label"><i class="bi bi-cash-coin"></i> Saldo Final Estimado</div>
            </div>
            <i class="bi bi-cash-coin stat-icon"></i>
        </div>
    </div>
</div>

<?php $categorias = [
    'operacion' => ['titulo' => 'Actividades de Operación', 'icono' => 'bi-cart4', 'color' => 'primary', 'data' => $operacion],
    'inversion' => ['titulo' => 'Actividades de Inversión', 'icono' => 'bi-building', 'color' => 'warning', 'data' => $inversion],
    'financiamiento' => ['titulo' => 'Actividades de Financiamiento', 'icono' => 'bi-bank', 'color' => 'info', 'data' => $financiamiento],
]; ?>
<?php foreach ($categorias as $key => $cat): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header bg-<?= $cat['color'] ?> text-white d-flex justify-content-between">
        <span><i class="bi <?= $cat['icono'] ?>"></i> <?= $cat['titulo'] ?></span>
        <span><?= count($cat['data']) ?> movimiento(s)</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($cat['data'])): ?>
        <div class="text-center py-3 text-muted small">Sin movimientos en este período</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Código</th><th>Cuenta</th><th class="text-end">Cargos (Salidas)</th><th class="text-end">Abonos (Entradas)</th></tr></thead>
                <tbody>
                    <?php $secCargo = 0; $secAbono = 0; ?>
                    <?php foreach ($cat['data'] as $r): ?>
                    <?php $secCargo += $r['total_cargo']; $secAbono += $r['total_abono']; ?>
                    <tr>
                        <td><?= safe_string($r['codigo']) ?></td>
                        <td><?= safe_string($r['nombre']) ?></td>
                        <td class="text-end"><?= $r['total_cargo'] > 0 ? format_money($r['total_cargo']) : '-' ?></td>
                        <td class="text-end"><?= $r['total_abono'] > 0 ? format_money($r['total_abono']) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active fw-bold">
                        <td colspan="2">Total <?= $cat['titulo'] ?></td>
                        <td class="text-end"><?= format_money($secCargo) ?></td>
                        <td class="text-end"><?= format_money($secAbono) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white"><i class="bi bi-calculator"></i> Resumen del Período</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Saldo Inicial:</strong> <?= format_money($saldo_inicial) ?></div>
            <div class="col-md-3"><strong>Total Entradas:</strong> <?= format_money($totalEntradas) ?></div>
            <div class="col-md-3"><strong>Total Salidas:</strong> <?= format_money($totalSalidas) ?></div>
            <div class="col-md-3"><strong class="<?= $flujoNeto >= 0 ? 'text-success' : 'text-danger' ?>">Flujo Neto: <?= format_money($flujoNeto) ?></strong></div>
        </div>
    </div>
</div>

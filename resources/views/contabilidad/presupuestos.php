<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-calculator"></i> Presupuestos</h1>
    <div class="d-flex gap-2">
        <form class="d-inline" method="GET">
            <select name="anio" class="form-select form-select-sm d-inline-block" style="width:100px" onchange="this.form.submit()">
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?= $y ?>" <?= $anio === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
        <a href="<?= url('contabilidad/presupuestos/comparar?anio=' . $anio) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-graph-up"></i> Comparar vs Real</a>
    </div>
</div>

<form method="POST" action="<?= url('contabilidad/presupuestos/guardar') ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="anio" value="<?= $anio ?>">

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <span><i class="bi bi-table"></i> Presupuesto Mensual por Cuenta — <?= $anio ?></span>
            <button type="submit" class="btn btn-sm btn-light"><i class="bi bi-floppy"></i> Guardar</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0" id="presupuestosTable">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <?php foreach ($meses as $m): ?>
                            <th class="text-center"><?= str_pad((string) $m, 2, '0', STR_PAD_LEFT) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $presupuestoMap = [];
                        foreach ($presupuestos as $p) {
                            $presupuestoMap[$p['cuenta_id']][$p['mes']] = $p['monto'];
                        }
                        ?>
                        <?php foreach ($cuentas as $cta): ?>
                        <tr>
                            <td><small><?= safe_string($cta['label']) ?></small></td>
                            <?php foreach ($meses as $m): ?>
                            <?php $val = $presupuestoMap[$cta['id_cuenta']][$m] ?? 0; ?>
                            <td class="text-center p-1">
                                <input type="hidden" name="cuenta_id[]" value="<?= $cta['id_cuenta'] ?>">
                                <input type="hidden" name="mes[]" value="<?= $m ?>">
                                <input type="number" name="monto[]" class="form-control form-control-sm text-center" style="width:85px;min-width:70px" step="0.01" value="<?= $val ?>">
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($cuentas)): ?>
                        <tr><td colspan="13" class="text-center text-muted">No hay cuentas de resultados disponibles</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-dark"><i class="bi bi-floppy"></i> Guardar Presupuestos</button>
        </div>
    </div>
</form>

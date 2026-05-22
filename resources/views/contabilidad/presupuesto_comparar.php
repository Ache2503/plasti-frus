<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-graph-up"></i> Presupuesto vs Real</h1>
    <div class="d-flex gap-2">
        <form class="d-inline" method="GET">
            <select name="anio" class="form-select form-select-sm d-inline-block" style="width:90px" onchange="this.form.submit()">
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?= $y ?>" <?= $anio === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <select name="mes" class="form-select form-select-sm d-inline-block" style="width:70px" onchange="this.form.submit()">
                <?php foreach (range(1, 12) as $m): ?>
                <option value="<?= $m ?>" <?= $mes === $m ? 'selected' : '' ?>><?= str_pad((string) $m, 2, '0', STR_PAD_LEFT) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="<?= url('contabilidad/presupuestos') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> Editar Presupuestos</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white d-flex justify-content-between">
        <span><i class="bi bi-table"></i> Comparación <?= str_pad((string) $mes, 2, '0', STR_PAD_LEFT) ?>/<?= $anio ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th class="text-end">Presupuesto</th>
                        <th class="text-end">Real</th>
                        <th class="text-end">Variación</th>
                        <th class="text-center">%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tPresupuesto = 0; $tReal = 0;
                    foreach ($comparacion as $c):
                        $tPresupuesto += $c['presupuesto'];
                        $tReal += $c['real_mes'];
                    ?>
                    <tr>
                        <td><?= safe_string($c['codigo']) ?></td>
                        <td><?= safe_string($c['nombre']) ?></td>
                        <td class="text-end"><?= format_money($c['presupuesto']) ?></td>
                        <td class="text-end"><?= format_money($c['real_mes']) ?></td>
                        <td class="text-end <?= ($c['real_mes'] - $c['presupuesto']) > 0 ? ($c['tipo'] === 'ingreso' ? 'text-success' : 'text-danger') : ($c['tipo'] === 'ingreso' ? 'text-danger' : 'text-success') ?>">
                            <?php $variacion = $c['real_mes'] - $c['presupuesto']; ?>
                            <?= $variacion >= 0 ? '+' : '' ?><?= format_money($variacion) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($c['presupuesto'] > 0): ?>
                            <?= number_format(($c['real_mes'] / $c['presupuesto']) * 100, 1) ?>%
                            <?php else: ?>
                            —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-dark">
                        <td colspan="2" class="fw-bold">Totales</td>
                        <td class="text-end fw-bold"><?= format_money($tPresupuesto) ?></td>
                        <td class="text-end fw-bold"><?= format_money($tReal) ?></td>
                        <td class="text-end fw-bold"><?= format_money($tReal - $tPresupuesto) ?></td>
                        <td class="text-center fw-bold"><?= $tPresupuesto > 0 ? number_format(($tReal / $tPresupuesto) * 100, 1) . '%' : '—' ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

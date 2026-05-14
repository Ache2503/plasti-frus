<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-list-check"></i> Balanza de Comprobación</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('contabilidad/balance-general') ?>" class="btn btn-sm btn-outline-dark">Balance General</a>
        <a href="<?= url('contabilidad/estado-resultados') ?>" class="btn btn-sm btn-outline-dark">Estado Resultados</a>
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

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cuenta</th>
                        <th>Tipo</th>
                        <th>Naturaleza</th>
                        <th class="text-end">Cargos</th>
                        <th class="text-end">Abonos</th>
                        <th class="text-end">Saldo Deudor</th>
                        <th class="text-end">Saldo Acreedor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sumCargos = 0; $sumAbonos = 0;
                    $sumSaldoDeudor = 0; $sumSaldoAcreedor = 0;
                    ?>
                    <?php foreach ($cuentas as $c): ?>
                    <?php
                        $saldo = $c['naturaleza'] === 'deudora'
                            ? $c['total_cargo'] - $c['total_abono']
                            : $c['total_abono'] - $c['total_cargo'];
                        $saldoDeudor = $saldo > 0 && $c['naturaleza'] === 'deudora' ? $saldo : 0;
                        $saldoAcreedor = $saldo > 0 && $c['naturaleza'] === 'acreedora' ? $saldo : 0;
                        $sumCargos += $c['total_cargo'];
                        $sumAbonos += $c['total_abono'];
                        $sumSaldoDeudor += $saldoDeudor;
                        $sumSaldoAcreedor += $saldoAcreedor;
                    ?>
                    <tr>
                        <td><code><?= safe_string($c['codigo']) ?></code></td>
                        <td><?= safe_string($c['nombre']) ?></td>
                        <td><span class="badge bg-<?= $c['tipo'] === 'activo' ? 'primary' : ($c['tipo'] === 'pasivo' ? 'warning text-dark' : ($c['tipo'] === 'capital' ? 'dark' : ($c['tipo'] === 'ingreso' ? 'success' : 'danger'))) ?>"><?= $c['tipo'] ?></span></td>
                        <td><?= $c['naturaleza'] === 'deudora' ? 'Deudora' : 'Acreedora' ?></td>
                        <td class="text-end"><?= format_money($c['total_cargo']) ?></td>
                        <td class="text-end"><?= format_money($c['total_abono']) ?></td>
                        <td class="text-end"><?= $saldoDeudor > 0 ? format_money($saldoDeudor) : '' ?></td>
                        <td class="text-end"><?= $saldoAcreedor > 0 ? format_money($saldoAcreedor) : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold table-dark">
                        <td colspan="4" class="text-end">Totales:</td>
                        <td class="text-end"><?= format_money($sumCargos) ?></td>
                        <td class="text-end"><?= format_money($sumAbonos) ?></td>
                        <td class="text-end"><?= format_money($sumSaldoDeudor) ?></td>
                        <td class="text-end"><?= format_money($sumSaldoAcreedor) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

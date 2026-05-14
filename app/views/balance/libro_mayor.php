<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-book"></i> Libro Mayor</h1>
    <div class="d-flex gap-2">
        <p class="mb-0"><strong><?= safe_string($cuenta['codigo']) ?></strong> — <?= safe_string($cuenta['nombre']) ?></p>
        <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-arrow-left"></i> Balanza</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <span class="badge bg-<?= $cuenta['tipo'] === 'activo' ? 'primary' : ($cuenta['tipo'] === 'pasivo' ? 'warning text-dark' : ($cuenta['tipo'] === 'capital' ? 'dark' : ($cuenta['tipo'] === 'ingreso' ? 'success' : 'danger'))) ?>"><?= safe_string($cuenta['tipo']) ?></span>
        <span class="badge bg-secondary"><?= $cuenta['naturaleza'] === 'deudora' ? 'Deudora' : 'Acreedora' ?></span>
        <span class="badge bg-info">Nivel <?= $cuenta['nivel'] ?></span>
    </div>
    <div class="col-md-6 text-end">
        <strong>Saldo Final:</strong>
        <span class="fs-5 <?= $saldo_final >= 0 ? 'text-success' : 'text-danger' ?>"><?= format_money($saldo_final) ?></span>
    </div>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= safe_string($fecha_desde) ?>">
    </div>
    <div class="col-auto">
        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= safe_string($fecha_hasta) ?>">
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
                    <tr><th>Fecha</th><th>Folio</th><th>Concepto</th><th class="text-end">Cargo</th><th class="text-end">Abono</th><th class="text-end">Saldo</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td><?= format_date($m['fecha']) ?></td>
                        <td><a href="<?= url('contabilidad/polizas/show/' . $m['id_poliza']) ?>"><?= safe_string($m['folio']) ?></a></td>
                        <td><?= safe_string($m['concepto'] ?: $m['poliza_concepto']) ?></td>
                        <td class="text-end"><?= $m['cargo'] > 0 ? format_money($m['cargo']) : '' ?></td>
                        <td class="text-end"><?= $m['abono'] > 0 ? format_money($m['abono']) : '' ?></td>
                        <td class="text-end fw-bold"><?= format_money($m['saldo']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($movimientos)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Sin movimientos en el período</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold table-dark">
                        <td colspan="3" class="text-end">Saldo Final:</td>
                        <td class="text-end"><?= format_money(array_sum(array_column($movimientos, 'cargo'))) ?></td>
                        <td class="text-end"><?= format_money(array_sum(array_column($movimientos, 'abono'))) ?></td>
                        <td class="text-end"><?= format_money($saldo_final) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-journal"></i> Libro Diario</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-sm btn-outline-dark">Balanza</a>
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
                    <tr><th>Fecha</th><th>Folio</th><th>Cuenta</th><th>Concepto</th><th class="text-end">Cargo</th><th class="text-end">Abono</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td><?= format_date($m['fecha']) ?></td>
                        <td><a href="<?= url('contabilidad/polizas/show/' . $m['id_poliza']) ?>"><?= safe_string($m['folio']) ?></a></td>
                        <td><code><?= safe_string($m['codigo']) ?></code> <?= safe_string($m['cuenta_nombre'] ?? '') ?></td>
                        <td><?= safe_string($m['partida_concepto'] ?: $m['poliza_concepto']) ?></td>
                        <td class="text-end"><?= $m['cargo'] > 0 ? format_money($m['cargo']) : '' ?></td>
                        <td class="text-end"><?= $m['abono'] > 0 ? format_money($m['abono']) : '' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($movimientos)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Sin movimientos en el período</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="fw-bold">
                    <tr class="table-dark">
                        <td colspan="4" class="text-end">Totales:</td>
                        <td class="text-end"><?= format_money($total_cargo) ?></td>
                        <td class="text-end"><?= format_money($total_abono) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-journal-text"></i> Kardex: <?= safe_string($material['nombre']) ?></h1>
    <a href="<?= url('kardex') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="row mb-4">
    <div class="col-md-3"><strong>Stock Actual:</strong> <?= number_format($material['stock_actual_kg'], 2) ?> kg</div>
    <div class="col-md-3"><strong>Punto Reorden:</strong> <?= number_format($material['punto_reorden_kg'], 2) ?> kg</div>
    <div class="col-md-3"><strong>Tipo:</strong> <?= safe_string($material['tipo']) ?></div>
</div>
<div class="card shadow-sm"><div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead><tr><th>Fecha</th><th>Movimiento</th><th>Cantidad</th><th>Stock Final</th><th>Operador</th></tr></thead>
    <tbody>
        <?php foreach ($movimientos as $k): ?>
        <tr>
            <td><?= format_datetime($k['fecha']) ?></td>
            <td><span class="badge bg-<?= $k['movimiento'] === 'entrada' ? 'success' : 'danger' ?>"><?= safe_string($k['movimiento']) ?></span></td>
            <td><?= number_format($k['cantidad'], 2) ?></td>
            <td><?= number_format($k['stock_final'], 2) ?></td>
            <td><?= safe_string($k['operador_nombre'] ?? $k['operador']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($movimientos)): ?>
        <tr><td colspan="5" class="text-center text-muted">Sin movimientos</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div></div></div>

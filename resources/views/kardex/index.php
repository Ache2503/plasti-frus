<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-journal-text"></i> Kardex de Materiales</h1>
    <a href="<?= url('kardex/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nuevo Movimiento</a>
</div>

<?php
$filterAction = 'kardex';
$id_material = $_GET['id_material'] ?? '';
$movimiento = $_GET['movimiento'] ?? '';
$filterExtra = '
<div class="col-12">
    <label class="form-label small fw-semibold mb-0">Material</label>
    <select name="id_material" class="form-select form-select-sm">
        <option value="">Todos</option>';
foreach ($materiales as $mat) {
    $sel = ((string)$id_material === (string)$mat['id_material']) ? "selected" : "";
    $filterExtra .= '<option value="' . $mat['id_material'] . '" ' . $sel . '>' . safe_string($mat['nombre']) . '</option>';
}
$filterExtra .= '
    </select>
</div>
<div class="col-12">
    <label class="form-label small fw-semibold mb-0">Movimiento</label>
    <select name="movimiento" class="form-select form-select-sm">
        <option value="">Todos</option>
        <option value="entrada" ' . ($movimiento === "entrada" ? "selected" : "") . '>Entrada</option>
        <option value="salida" ' . ($movimiento === "salida" ? "selected" : "") . '>Salida</option>
    </select>
</div>';
include __DIR__ . '/../partials/filter_bar.php';
?>

<div class="card shadow-sm"><div class="card-body p-0">
<div class="table-responsive">
<table class="table datatable table-hover mb-0">
    <thead><tr><th>ID</th><th>Material</th><th>Fecha</th><th>Movimiento</th><th>Cantidad</th><th>Stock Final</th><th>Operador</th></tr></thead>
    <tbody>
        <?php foreach ($movimientos as $k): ?>
        <tr>
            <td><?= $k['id_kardex'] ?></td>
            <td><a href="<?= url('kardex/detalle/' . $k['id_material']) ?>"><?= safe_string($k['material_nombre'] ?? 'N/A') ?></a></td>
            <td><?= format_date($k['fecha']) ?></td>
            <td><span class="badge bg-<?= $k['movimiento'] === 'entrada' ? 'success' : 'danger' ?>"><?= safe_string($k['movimiento']) ?></span></td>
            <td><?= number_format($k['cantidad'], 2) ?> <?= safe_string($k['unidad_medida'] ?? 'kg') ?></td>
            <td><?= number_format($k['stock_final'], 2) ?></td>
            <td><?= safe_string($k['operador']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($movimientos)): ?>
        <tr><td colspan="7" class="text-center text-muted">Sin movimientos</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div></div></div>

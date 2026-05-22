<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-exclamation-triangle"></i> Incidencias de Producción</h1>
    <a href="<?= url('incidencias/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Incidencia</a>
</div>

<?php
$filterAction = 'incidencias';
$estatus = $_GET['estatus'] ?? '';
$filterExtra = '
<div class="col-12">
    <label class="form-label small fw-semibold mb-0">Estatus</label>
    <select name="estatus" class="form-select form-select-sm">
        <option value="">Todos</option>
        <option value="abierta" ' . ($estatus === "abierta" ? "selected" : "") . '>Abierta</option>
        <option value="cerrada" ' . ($estatus === "cerrada" ? "selected" : "") . '>Cerrada</option>
    </select>
</div>';
include __DIR__ . '/../partials/filter_bar.php';
?>

<div class="card shadow-sm"><div class="card-body p-0">
<div class="table-responsive">
<table class="table datatable table-hover mb-0">
    <thead><tr><th>ID</th><th>Orden</th><th>Producto</th><th>Fecha</th><th>Impacto</th><th>Descripción</th><th>Estatus</th><th class="no-sort">Acciones</th></tr></thead>
    <tbody>
        <?php foreach ($incidencias as $inc): ?>
        <tr>
            <td><?= $inc['id_incidencia'] ?></td>
            <td>#<?= $inc['id_orden_cabe'] ?? 'N/A' ?></td>
            <td><?= safe_string($inc['producto_nombre'] ?? 'N/A') ?></td>
            <td><?= format_date($inc['fecha']) ?></td>
            <td><span class="badge bg-<?= $inc['impacto'] === 'alto' ? 'danger' : ($inc['impacto'] === 'medio' ? 'warning' : 'info') ?>"><?= safe_string($inc['impacto']) ?></span></td>
            <td><?= safe_string(truncate($inc['descripcion'] ?? '', 60)) ?></td>
            <td><span class="badge bg-<?= $inc['estatus'] === 'cerrada' ? 'success' : 'danger' ?>"><?= safe_string($inc['estatus']) ?></span></td>
            <td>
                <?php if ($inc['estatus'] !== 'cerrada'): ?>
                <form method="POST" action="<?= url('incidencias/cerrar/' . $inc['id_incidencia']) ?>" class="d-inline">
                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('¿Cerrar incidencia?')"><i class="bi bi-check-lg"></i></button>
                </form>
                <?php endif; ?>
                <form method="POST" action="<?= url('incidencias/delete/' . $inc['id_incidencia']) ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar incidencia?')"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($incidencias)): ?>
        <tr><td colspan="8" class="text-center text-muted">Sin incidencias</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div></div></div>

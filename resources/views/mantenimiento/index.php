<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-tools"></i> Mantenimiento de Máquinas</h1>
    <div>
        <a href="<?= url('mantenimiento/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Registrar</a>
        <a href="<?= url('mantenimiento/plan') ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-calendar-plus"></i> Programar</a>
        <a href="<?= url('mantenimiento/paros') ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-stop-circle"></i> Paros</a>
    </div>
</div>

<?php if (!empty($pendientes)): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-circle"></i> <strong>Mantenimientos Pendientes:</strong>
    <ul class="mb-0 mt-1"><?php foreach (array_slice($pendientes, 0, 5) as $p): ?>
        <li><?= safe_string($p['maquina_nombre']) ?> - <?= format_date($p['fecha_programada']) ?> (<?= safe_string($p['tipo_mantenimiento_nombre'] ?? $p['tipo_mantenimiento']) ?>)</li>
    <?php endforeach; ?></ul>
</div>
<?php endif; ?>

<?php
$filterAction = 'mantenimiento';
$tipo = $_GET['id_tipo_mantenimiento'] ?? '';
$filterExtra = '
<div class="col-12">
    <label class="form-label small fw-semibold mb-0">Tipo</label>
    <select name="id_tipo_mantenimiento" class="form-select form-select-sm">
        <option value="">Todos</option>';
foreach (($tipos_mantenimiento ?? []) as $tipoMant) {
    $sel = ((string) $tipo === (string) $tipoMant['id_tipo_mantenimiento']) ? 'selected' : '';
    $filterExtra .= '<option value="' . $tipoMant['id_tipo_mantenimiento'] . '" ' . $sel . '>' . safe_string($tipoMant['nombre']) . '</option>';
}
$filterExtra .= '
    </select>
</div>';
include __DIR__ . '/../partials/filter_bar.php';
?>

<div class="card shadow-sm"><div class="card-body p-0">
<div class="table-responsive">
<table class="table datatable table-hover mb-0">
    <thead><tr><th>ID</th><th>Máquina</th><th>Fecha</th><th>Tipo</th><th>Técnico</th><th>Horas Paro</th><th>Resultado</th><th class="no-sort">Acciones</th></tr></thead>
    <tbody>
        <?php foreach ($mantenimientos as $m): ?>
        <tr>
            <td><?= $m['id_mantenimiento'] ?></td>
            <td><?= safe_string($m['maquina_nombre'] ?? 'N/A') ?></td>
            <td><?= format_date($m['fecha_mantenimiento']) ?></td>
            <td><?= safe_string($m['tipo_mantenimiento_nombre'] ?? $m['tipo_mantenimiento']) ?></td>
            <td><?= safe_string($m['tecnico_responsable_nombre'] ?? $m['tecnico_responsable']) ?></td>
            <td><?= $m['horas_paro'] ?></td>
            <td><span class="badge bg-<?= $m['resultado'] === 'completado' ? 'success' : 'warning' ?>"><?= safe_string($m['resultado']) ?></span></td>
            <td>
                <a href="<?= url('mantenimiento/edit/' . $m['id_mantenimiento']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= url('mantenimiento/delete/' . $m['id_mantenimiento']) ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($mantenimientos)): ?>
        <tr><td colspan="8" class="text-center text-muted">Sin registros</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div></div></div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-x-circle"></i> Rechazos de Calidad</h1>
    <a href="<?= url('calidad/rechazos/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nuevo Rechazo</a>
</div>

<?php $filterAction = 'calidad/rechazos'; include __DIR__ . '/../partials/filter_bar.php'; ?>

<div class="card shadow-sm"><div class="card-body p-0">
<div class="table-responsive">
<table class="table datatable table-hover mb-0">
    <thead><tr><th>ID</th><th>Producto</th><th>Fecha</th><th>Cant. Rechazada</th><th>Motivo</th><th>Inspector</th><th>Estatus</th><th>Acciones</th></tr></thead>
    <tbody>
        <?php foreach ($rechazos as $r): ?>
        <tr>
            <td><?= $r['id_rechazo'] ?></td>
            <td><?= safe_string($r['producto_nombre'] ?? 'N/A') ?></td>
            <td><?= format_date($r['fecha']) ?></td>
            <td class="text-danger"><?= $r['cantidad_rechazada'] ?></td>
            <td><?= safe_string(truncate($r['motivo_rechazo_nombre'] ?? $r['motivo_rechazo'] ?? '', 50)) ?></td>
            <td><?= safe_string($r['inspector_nombre'] ?? $r['inspector'] ?? '') ?></td>
            <td><span class="badge bg-<?= $r['estatus'] === 'resuelto' ? 'success' : 'warning' ?>"><?= safe_string($r['estatus']) ?></span></td>
            <td>
                <form method="POST" action="<?= url('calidad/rechazos/delete/' . $r['id_rechazo']) ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rechazos)): ?>
        <tr><td colspan="8" class="text-center text-muted">Sin rechazos</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div></div></div>

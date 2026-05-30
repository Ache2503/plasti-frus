<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-stop-circle"></i> Bitácora de Paros</h1>
    <a href="<?= url('mantenimiento/paros/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nuevo Paro</a>
</div>

<?php $filterAction = 'mantenimiento/paros'; include __DIR__ . '/../partials/filter_bar.php'; ?>

<div class="card shadow-sm"><div class="card-body p-0">
<div class="table-responsive">
<table class="table datatable table-hover mb-0">
    <thead><tr><th>ID</th><th>Máquina</th><th>Fecha</th><th>Inicio</th><th>Fin</th><th>Duración (h)</th><th>Motivo</th><th>Estatus</th><th class="no-sort">Acciones</th></tr></thead>
    <tbody>
        <?php foreach ($paros as $p): ?>
        <tr>
            <td><?= $p['id_bitacora'] ?></td>
            <td><?= safe_string($p['maquina_nombre'] ?? 'N/A') ?></td>
            <td><?= format_date($p['fecha']) ?></td>
            <td><?= $p['hora_inicio'] ? substr($p['hora_inicio'], 0, 5) : '-' ?></td>
            <td><?= $p['hora_fin'] ? substr($p['hora_fin'], 0, 5) : '-' ?></td>
            <td><?= number_format($p['duracion_paro'], 1) ?></td>
            <td><?= safe_string(truncate($p['motivo_paro_nombre'] ?? $p['motivo_paro'] ?? '', 50)) ?></td>
            <td><span class="badge bg-<?= $p['estatus'] === 'resuelto' ? 'success' : 'danger' ?>"><?= safe_string($p['estatus']) ?></span></td>
            <td>
                <a href="<?= url('mantenimiento/paros/edit/' . $p['id_bitacora']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <form method="POST" action="<?= url('mantenimiento/paros/delete/' . $p['id_bitacora']) ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($paros)): ?>
        <tr><td colspan="9" class="text-center text-muted">Sin paros registrados</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div></div></div>

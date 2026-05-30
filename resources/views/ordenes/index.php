<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clipboard-check"></i> Órdenes de Producción</h1>
    <a href="<?= url('ordenes/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Orden</a>
</div>

<?php if (isset($stats)): ?>
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label"><i class="bi bi-list-check"></i> Total Órdenes</div>
            </div>
            <i class="bi bi-list-check stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= $stats['total_planificadas'] ?></div>
                <div class="stat-label"><i class="bi bi-calendar-check"></i> Planificadas</div>
            </div>
            <i class="bi bi-calendar-check stat-icon"></i>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card card-info">
            <div class="stat-content">
                <div class="stat-number"><?= $stats['total_producidas'] ?></div>
                <div class="stat-label"><i class="bi bi-check-circle"></i> Producidas</div>
            </div>
            <i class="bi bi-check-circle stat-icon"></i>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$filterAction = 'ordenes';
$turno = $_GET['turno'] ?? '';
$id_producto = $_GET['id_producto'] ?? '';
$filterExtra = '
<div class="col-12">
    <label class="form-label small fw-semibold mb-0">Turno</label>
    <select name="turno" class="form-select form-select-sm">
        <option value="">Todos</option>
        <option value="matutino" ' . ($turno === "matutino" ? "selected" : "") . '>Matutino</option>
        <option value="vespertino" ' . ($turno === "vespertino" ? "selected" : "") . '>Vespertino</option>
        <option value="nocturno" ' . ($turno === "nocturno" ? "selected" : "") . '>Nocturno</option>
    </select>
</div>
<div class="col-12">
    <label class="form-label small fw-semibold mb-0">Producto</label>
    <select name="id_producto" class="form-select form-select-sm">
        <option value="">Todos</option>';
foreach ($productos as $p) {
    $sel = ((string)$id_producto === (string)$p['id_producto']) ? "selected" : "";
    $filterExtra .= '<option value="' . $p['id_producto'] . '" ' . $sel . '>' . safe_string($p['codigo'] . ' - ' . $p['nombre']) . '</option>';
}
$filterExtra .= '
    </select>
</div>';
include __DIR__ . '/../partials/filter_bar.php';
?>

<?php if (empty($ordenes)): ?>
<div class="alert alert-info text-center py-4">
    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
    No hay registros disponibles.
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Máquina</th>
                        <th>Molde</th>
                        <th>Cant. Plan</th>
                        <th>Cant. Real</th>
                        <th>Fecha</th>
                        <th>Turno</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenes as $o): ?>
                    <tr>
                        <td><?= $o['id_orden_cabe'] ?></td>
                        <td><?= safe_string($o['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= safe_string($o['maquina_nombre'] ?? 'N/A') ?></td>
                        <td><?= safe_string($o['molde_nombre'] ?? 'N/A') ?></td>
                        <td><?= $o['cantidad_planificada'] ?></td>
                        <td><?= $o['cantidad_real_buenas'] ?? '-' ?></td>
                        <td><?= format_date($o['fecha']) ?></td>
                        <td><?= safe_string($o['turno']) ?></td>
                        <td>
                            <a href="<?= url('ordenes/detalle/' . $o['id_orden_cabe']) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="<?= url('ordenes/edit/' . $o['id_orden_cabe']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= url('ordenes/delete/' . $o['id_orden_cabe']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar orden?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/pagination.php'; ?>
<?php endif; ?>

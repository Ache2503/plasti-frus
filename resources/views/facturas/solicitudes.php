<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-file-text"></i> Solicitudes de Factura</h1>
</div>

<?php
$pendientes = array_filter($solicitudes, fn($s) => $s['estatus'] === 'pendiente');
$procesadas = array_filter($solicitudes, fn($s) => $s['estatus'] !== 'pendiente');
?>

<?php if (!empty($pendientes)): ?>
<div class="card shadow-sm mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <i class="bi bi-clock-history"></i> Pendientes (<?= count($pendientes) ?>)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>#</th><th>Cliente</th><th>RFC</th><th>Producto</th><th>Monto</th><th>Fecha Venta</th><th>Solicitada</th><th class="no-sort">Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($pendientes as $s): ?>
                    <tr>
                        <td><?= $s['id_solicitud'] ?></td>
                        <td><?= safe_string($s['razon_social'] ?? 'N/A') ?></td>
                        <td><?= safe_string($s['rfc'] ?? 'N/A') ?></td>
                        <td><?= safe_string($s['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= format_money(($s['cantidad_vendida'] ?? 0) * ($s['precio_unitario'] ?? 0), $s['moneda']) ?></td>
                        <td><?= format_date($s['fecha_venta']) ?></td>
                        <td><?= format_datetime($s['fecha_solicitud']) ?></td>
                        <td>
                            <form method="POST" action="<?= url('facturas/procesar/' . $s['id_solicitud']) ?>" style="display:inline" onsubmit="return confirm('¿Generar factura?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Procesar</button>
                            </form>
                            <form method="POST" action="<?= url('facturas/rechazar/' . $s['id_solicitud']) ?>" style="display:inline" onsubmit="return confirm('¿Rechazar solicitud?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($procesadas)): ?>
<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white">
        <i class="bi bi-check-circle"></i> Procesadas / Rechazadas (<?= count($procesadas) ?>)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>#</th><th>Cliente</th><th>RFC</th><th>Producto</th><th>Monto</th><th>Estatus</th><th>Procesada</th><th>Procesó</th></tr></thead>
                <tbody>
                    <?php foreach ($procesadas as $s): ?>
                    <tr>
                        <td><?= $s['id_solicitud'] ?></td>
                        <td><?= safe_string($s['razon_social'] ?? 'N/A') ?></td>
                        <td><?= safe_string($s['rfc'] ?? 'N/A') ?></td>
                        <td><?= safe_string($s['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= format_money(($s['cantidad_vendida'] ?? 0) * ($s['precio_unitario'] ?? 0), $s['moneda']) ?></td>
                        <td>
                            <span class="badge bg-<?= $s['estatus'] === 'procesada' ? 'success' : 'danger' ?>">
                                <?= $s['estatus'] ?>
                            </span>
                        </td>
                        <td><?= $s['fecha_procesada'] ? format_datetime($s['fecha_procesada']) : '—' ?></td>
                        <td><?= safe_string($s['procesado_por'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($solicitudes)): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle"></i> No hay solicitudes de factura registradas.
</div>
<?php endif; ?>

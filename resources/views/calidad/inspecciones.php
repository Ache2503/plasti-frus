<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clipboard-check"></i> Inspecciones de Calidad</h1>
    <a href="<?= url('calidad/inspecciones/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Inspección</a>
</div>

<?php $filterAction = 'calidad/inspecciones'; include __DIR__ . '/../partials/filter_bar.php'; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead><tr><th>ID</th><th>Producto</th><th>Fecha</th><th>Muestreo</th><th>Aprobadas</th><th>Rechazadas</th><th>Inspector</th><th>Resultado</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($inspecciones as $i): ?>
                    <tr>
                        <td><?= safe_string($i['id_inspeccion']) ?></td>
                        <td><?= safe_string($i['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= format_date($i['fecha_inspeccion']) ?></td>
                        <td><?= $i['muestreo_piezas'] ?></td>
                        <td class="text-success"><?= $i['piezas_aprobadas'] ?></td>
                        <td class="text-danger"><?= $i['piezas_rechazadas'] ?></td>
                        <td><?= safe_string($i['inspector']) ?></td>
                        <td><span class="badge bg-<?= $i['resultado'] === 'aprobado' ? 'success' : ($i['resultado'] === 'rechazado' ? 'danger' : 'warning') ?>"><?= safe_string($i['resultado']) ?></span></td>
                        <td>
                            <form method="POST" action="<?= url('calidad/inspecciones/delete/' . $i['id_inspeccion']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($inspecciones)): ?>
                    <tr><td colspan="9" class="text-center text-muted">Sin inspecciones</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

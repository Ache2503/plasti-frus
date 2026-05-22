<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-journal-text"></i> Recetas de Producción</h1>
    <a href="<?= url('recetas/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Receta</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead>
                    <tr><th>ID</th><th>Producto</th><th>Versión</th><th>Máquina</th><th>Temp. (°C)</th><th>Presión (bar)</th><th>Enf. (s)</th><th>Fecha Versión</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recetas as $r): ?>
                    <tr>
                        <td><?= $r['id_receta_cabe'] ?></td>
                        <td><?= safe_string($r['producto_nombre'] ?? 'N/A') ?></td>
                        <td><?= safe_string($r['version']) ?></td>
                        <td><?= safe_string($r['maquina_nombre'] ?? 'N/A') ?></td>
                        <td><?= $r['temperatura_inyeccion_C'] ?></td>
                        <td><?= $r['presion_inyeccion_bar'] ?></td>
                        <td><?= $r['tiempo_enfriamiento_s'] ?></td>
                        <td><?= format_date($r['fecha_version']) ?></td>
                        <td>
                            <a href="<?= url('recetas/edit/' . $r['id_receta_cabe']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= url('recetas/delete/' . $r['id_receta_cabe']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar receta?')"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recetas)): ?>
                    <tr><td colspan="9" class="text-center text-muted">Sin recetas registradas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-boxes"></i> Materiales</h1>
    <?php if (in_array(user_rol(), [1, 3])): ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= url('materiales/create') ?>" class="btn btn-sm btn-dark">
            <i class="bi bi-plus-lg"></i> Nuevo Material
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Presentación</th>
                        <th>Stock (kg)</th>
                        <th>Punto Reorden</th>
                        <th>Proveedor</th>
                        <?php if (in_array(user_rol(), [1, 3])): ?>
                        <th class="no-sort">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materiales as $m): ?>
                    <tr class="<?= ($m['stock_actual_kg'] <= $m['punto_reorden_kg']) ? 'table-danger' : '' ?>">
                        <td><?= $m['id_material'] ?></td>
                        <td><?= safe_string($m['nombre']) ?></td>
                        <td><?= safe_string($m['tipo']) ?></td>
                        <td><?= safe_string($m['presentacion']) ?></td>
                        <td><?= number_format($m['stock_actual_kg'], 2) ?></td>
                        <td><?= number_format($m['punto_reorden_kg'], 2) ?></td>
                        <td><?= safe_string($m['proveedor'] ?? 'N/A') ?></td>
                        <?php if (in_array(user_rol(), [1, 3])): ?>
                        <td>
                            <a href="<?= url('materiales/edit/' . $m['id_material']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= url('materiales/delete/' . $m['id_material']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar material?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($materiales)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Sin materiales registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

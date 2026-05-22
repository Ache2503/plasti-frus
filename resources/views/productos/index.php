<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cube"></i> Productos</h1>
    <a href="<?= url('productos/create') ?>" class="btn btn-sm btn-dark">
        <i class="bi bi-plus-lg"></i> Nuevo Producto
    </a>
</div>

<?php if (empty($productos)): ?>
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
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Familia</th>
                        <th>Línea</th>
                        <th>Color</th>
                        <th>Peso (grs)</th>
                        <th>Recetas</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= safe_string($p['codigo']) ?></td>
                        <td><?= safe_string($p['nombre']) ?></td>
                        <td><?= safe_string($p['familia']) ?></td>
                        <td><?= safe_string($p['linea']) ?></td>
                        <td><?= safe_string($p['color']) ?></td>
                        <td><?= $p['peso_unitario_grs'] ?></td>
                        <td><span class="badge bg-info"><?= $p['total_recetas'] ?? 0 ?></span></td>
                        <td>
                            <a href="<?= url('productos/show/' . $p['id_producto']) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="<?= url('productos/edit/' . $p['id_producto']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= url('productos/delete/' . $p['id_producto']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar producto?')"><i class="bi bi-trash"></i></button>
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

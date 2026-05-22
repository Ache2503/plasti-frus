<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-truck"></i> Proveedores</h1>
    <a href="<?= url('proveedores/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nuevo Proveedor</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable table-hover mb-0">
                <thead>
                    <tr><th>ID</th><th>Razón Social</th><th>RFC</th><th>Tipo Material</th><th>Teléfono</th><th>Correo</th><th>Estatus</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $p): ?>
                    <tr>
                        <td><?= $p['id_proveedor'] ?></td>
                        <td><?= safe_string($p['razon_social']) ?></td>
                        <td><?= safe_string($p['rfc']) ?></td>
                        <td><?= safe_string($p['tipo_material']) ?></td>
                        <td><?= safe_string($p['telefono']) ?></td>
                        <td><?= safe_string($p['correo']) ?></td>
                        <td><span class="badge bg-<?= $p['estatus'] === 'activo' ? 'success' : 'secondary' ?>"><?= safe_string($p['estatus']) ?></span></td>
                        <td>
                            <?php if (!es_contador()): ?>
                            <a href="<?= url('proveedores/edit/' . $p['id_proveedor']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="<?= url('proveedores/delete/' . $p['id_proveedor']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar proveedor?')"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($proveedores)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Sin proveedores registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

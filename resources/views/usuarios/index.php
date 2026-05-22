<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people-fill"></i> Usuarios</h1>
    <?php if (!($readonly ?? false)): ?>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= url('usuarios/create') ?>" class="btn btn-sm btn-dark">
            <i class="bi bi-plus-lg"></i> Nuevo Usuario
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
                        <th>Usuario</th>
                        <th>Empleado</th>
                        <th>Rol</th>
                        <th>Activo</th>
                        <th class="no-sort">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['id_usuario'] ?></td>
                        <td><?= safe_string($u['nombre_usuario']) ?></td>
                        <td><?= safe_string(($u['nombre'] ?? '') . ' ' . ($u['apellido_paterno'] ?? '')) ?></td>
                        <td><span class="badge bg-<?= ($u['id_rol'] ?? 2) == 1 ? 'danger' : (($u['id_rol'] ?? 2) == 3 ? 'warning text-dark' : 'info') ?>"><?= safe_string($u['rol']) ?></span></td>
                        <td>
                            <?php if ($u['activo']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!($readonly ?? false)): ?>
                            <a href="<?= url('usuarios/edit/' . $u['id_usuario']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($u['id_usuario'] != $_SESSION['user_id']): ?>
                            <form method="POST" action="<?= url('usuarios/delete/' . $u['id_usuario']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar usuario?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="text-muted"><i class="bi bi-eye"></i> Solo lectura</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6" class="text-center text-muted">Sin usuarios registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

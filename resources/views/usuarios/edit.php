<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Usuario</h1>
    <a href="<?= url('usuarios') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('usuarios/update/' . $usuario['id_usuario']) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" value="<?= safe_string($usuario['nombre_usuario']) ?>" disabled>
                    <small class="text-muted">No se puede cambiar el nombre de usuario</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rol</label>
                    <select name="id_rol" class="form-select">
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id_rol'] ?>" <?= ($usuario['id_rol'] ?? '') == $r['id_rol'] ? 'selected' : '' ?>>
                            <?= safe_string($r['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Activo</label>
                    <select name="activo" class="form-select">
                        <option value="1" <?= $usuario['activo'] ? 'selected' : '' ?>>Sí</option>
                        <option value="0" <?= !$usuario['activo'] ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nueva Contraseña</label>
                    <input type="password" name="password" class="form-control" minlength="6" placeholder="Dejar vacío para mantener">
                </div>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

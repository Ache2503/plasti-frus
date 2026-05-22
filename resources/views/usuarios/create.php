<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nuevo Usuario</h1>
    <a href="<?= url('usuarios') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('usuarios/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_usuario" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rol <span class="text-danger">*</span></label>
                    <select name="id_rol" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id_rol'] ?>"><?= safe_string($r['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr>
            <h6 class="text-muted">Vincular a empleado existente</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Empleado</label>
                    <select name="id_empleado" class="form-select">
                        <option value="">-- Crear nuevo empleado --</option>
                        <?php foreach ($empleados_disponibles as $e): ?>
                        <option value="<?= $e['id_empleado'] ?>"><?= safe_string($e['nombre'] . ' ' . $e['apellido_paterno']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr>
            <h6 class="text-muted">O crear nuevo empleado</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Apellido Materno</label>
                    <input type="text" name="apellido_materno" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
        </form>
    </div>
</div>

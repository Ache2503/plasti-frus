<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Máquina</h1>
    <a href="<?= url('maquinas') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('maquinas/update/' . $maquina['id_maquina']) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= safe_string($maquina['nombre']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Modelo</label>
                    <input type="text" name="modelo" class="form-control" value="<?= safe_string($maquina['modelo']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Serie</label>
                    <input type="text" name="numero_serie" class="form-control" value="<?= safe_string($maquina['numero_serie']) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Estatus</label>
                <select name="estatus" class="form-select">
                    <option value="activo" <?= $maquina['estatus'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $maquina['estatus'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    <option value="mantenimiento" <?= $maquina['estatus'] === 'mantenimiento' ? 'selected' : '' ?>>En Mantenimiento</option>
                </select>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

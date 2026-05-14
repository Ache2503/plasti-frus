<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Molde</h1>
    <a href="<?= url('moldes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('moldes/update/' . $molde['id_molde']) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre del Molde</label>
                    <input type="text" name="nombre_molde" class="form-control" value="<?= safe_string($molde['nombre_molde']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Cavidades</label>
                    <input type="number" name="numero_cavidades" class="form-control" value="<?= $molde['numero_cavidades'] ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Material</label>
                    <input type="text" name="material_molde" class="form-control" value="<?= safe_string($molde['material_molde']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Vida Útil (golpes)</label>
                    <input type="number" name="vida_util_golpes" class="form-control" value="<?= $molde['vida_util_golpes'] ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ciclos Acumulados</label>
                    <input type="number" name="ciclos_acumulados" class="form-control" value="<?= $molde['ciclos_acumulados'] ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-select">
                        <option value="activo" <?= $molde['estatus'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $molde['estatus'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        <option value="mantenimiento" <?= $molde['estatus'] === 'mantenimiento' ? 'selected' : '' ?>>En Mantenimiento</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">CEDIS</label>
                <select name="id_cedes" class="form-select">
                    <option value="">Sin asignar</option>
                    <?php foreach ($cedes as $c): ?>
                    <option value="<?= $c['id_cedes'] ?>" <?= ($molde['id_cedes'] ?? '') == $c['id_cedes'] ? 'selected' : '' ?>><?= safe_string($c['nombre_cede']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

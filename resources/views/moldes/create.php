<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nuevo Molde</h1>
    <a href="<?= url('moldes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('moldes/store') ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre del Molde <span class="text-danger">*</span></label>
                    <input type="text" name="nombre_molde" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Núm. Cavidades</label>
                    <input type="number" name="numero_cavidades" class="form-control" value="1" min="1">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Material del Molde</label>
                    <input type="text" name="material_molde" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Vida Útil (golpes)</label>
                    <input type="number" name="vida_util_golpes" class="form-control" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ciclos Acumulados</label>
                    <input type="number" name="ciclos_acumulados" class="form-control" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-select">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="mantenimiento">En Mantenimiento</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">CEDIS (Ubicación)</label>
                <select name="id_cedes" class="form-select">
                    <option value="">Sin asignar</option>
                    <?php foreach ($cedes as $c): ?>
                    <option value="<?= $c['id_cedes'] ?>"><?= safe_string($c['nombre_cede']) ?> - <?= safe_string($c['ubicacion']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
        </form>
    </div>
</div>

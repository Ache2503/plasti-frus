<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nuevo Proveedor</h1>
    <a href="<?= url('proveedores') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('proveedores/store') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                    <input type="text" name="razon_social" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">RFC</label>
                    <input type="text" name="rfc" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-select">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo Material</label>
                    <input type="text" name="tipo_material" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <input type="text" name="estado" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">País</label>
                    <input type="text" name="pais" class="form-control" value="México">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Sector</label>
                <input type="text" name="sector" class="form-control" list="sectores">
                <datalist id="sectores"><?php foreach ($sectores as $s): ?><option value="<?= safe_string($s['sector']) ?>"><?php endforeach; ?></datalist>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
        </form>
    </div>
</div>

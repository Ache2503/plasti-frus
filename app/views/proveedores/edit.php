<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Proveedor</h1>
    <a href="<?= url('proveedores') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('proveedores/update/' . $proveedor['id_proveedor']) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Razón Social</label>
                    <input type="text" name="razon_social" class="form-control" value="<?= safe_string($proveedor['razon_social']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">RFC</label>
                    <input type="text" name="rfc" class="form-control" value="<?= safe_string($proveedor['rfc']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-select">
                        <option value="activo" <?= $proveedor['estatus'] === 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="inactivo" <?= $proveedor['estatus'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo Material</label>
                    <input type="text" name="tipo_material" class="form-control" value="<?= safe_string($proveedor['tipo_material']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="<?= safe_string($proveedor['telefono']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" value="<?= safe_string($proveedor['correo']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" value="<?= safe_string($proveedor['ciudad']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <input type="text" name="estado" class="form-control" value="<?= safe_string($proveedor['estado']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">País</label>
                    <input type="text" name="pais" class="form-control" value="<?= safe_string($proveedor['pais'] ?? 'México') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Sector</label>
                <input type="text" name="sector" class="form-control" value="<?= safe_string($proveedor['sector']) ?>">
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Material</h1>
    <a href="<?= url('materiales') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('materiales/update/' . $material['id_material']) ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="<?= safe_string($material['nombre']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select">
                        <option value="">Seleccionar</option>
                        <?php foreach (['resina','aditivo','colorante','masterbatch','empaque','otro'] as $t): ?>
                        <option value="<?= $t ?>" <?= $material['tipo'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Presentación</label>
                    <input type="text" name="presentacion" class="form-control" value="<?= safe_string($material['presentacion']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Unidad de Medida</label>
                    <input type="text" name="unidad_medida" class="form-control" value="<?= safe_string($material['unidad_medida']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stock Actual (kg)</label>
                    <input type="number" step="0.01" name="stock_actual_kg" class="form-control" value="<?= $material['stock_actual_kg'] ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Punto de Reorden (kg)</label>
                    <input type="number" step="0.01" name="punto_reorden_kg" class="form-control" value="<?= $material['punto_reorden_kg'] ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lote Recepción</label>
                    <input type="text" name="lote_recepcion" class="form-control" value="<?= safe_string($material['lote_recepcion']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Proveedor</label>
                    <select name="id_proveedor" class="form-select">
                        <option value="">Sin proveedor</option>
                        <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p['id_proveedor'] ?>" <?= ($material['id_proveedor'] ?? '') == $p['id_proveedor'] ? 'selected' : '' ?>>
                            <?= safe_string($p['razon_social']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

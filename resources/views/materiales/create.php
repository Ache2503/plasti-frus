<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nuevo Material</h1>
    <a href="<?= url('materiales') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('materiales/store') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="resina">Resina</option>
                        <option value="aditivo">Aditivo</option>
                        <option value="colorante">Colorante</option>
                        <option value="masterbatch">Masterbatch</option>
                        <option value="empaque">Empaque</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Presentación</label>
                    <input type="text" name="presentacion" class="form-control" placeholder="ej. Saco 25kg">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Unidad de Medida</label>
                    <input type="text" name="unidad_medida" class="form-control" value="kg">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Stock Actual (kg)</label>
                    <input type="number" step="0.01" name="stock_actual_kg" class="form-control" value="0">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Punto de Reorden (kg)</label>
                    <input type="number" step="0.01" name="punto_reorden_kg" class="form-control" value="0">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lote Recepción</label>
                    <input type="text" name="lote_recepcion" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Proveedor</label>
                    <select name="id_proveedor" class="form-select">
                        <option value="">Sin proveedor</option>
                        <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p['id_proveedor'] ?>"><?= safe_string($p['razon_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
        </form>
    </div>
</div>

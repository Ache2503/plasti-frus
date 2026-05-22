<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nuevo Producto</h1>
    <a href="<?= url('productos') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('productos/store') ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" name="codigo" class="form-control" required>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Familia</label>
                    <input type="text" name="familia" class="form-control" list="familias">
                    <datalist id="familias">
                        <?php foreach ($familias as $f): ?>
                        <option value="<?= safe_string($f['familia']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Línea</label>
                    <input type="text" name="linea" class="form-control" list="lineas">
                    <datalist id="lineas">
                        <?php foreach ($lineas as $l): ?>
                        <option value="<?= safe_string($l['linea']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Color</label>
                    <input type="text" name="color" class="form-control" list="colores">
                    <datalist id="colores">
                        <?php foreach ($colores as $c): ?>
                        <option value="<?= safe_string($c['color']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Peso Unitario (grs)</label>
                    <input type="number" step="0.01" name="peso_unitario_grs" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Dimensiones</label>
                    <input type="text" name="dimensiones" class="form-control" placeholder="ej. 10x5x3 cm">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Publicar en Web</label>
                    <select name="publicar_web" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Sí</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción Comercial</label>
                <textarea name="descripcion_comercial" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
        </form>
    </div>
</div>

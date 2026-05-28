<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Producto</h1>
    <a href="<?= url('productos') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('productos/update/' . $producto['id_producto']) ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Código</label>
                    <input type="text" name="codigo" class="form-control" value="<?= safe_string($producto['codigo']) ?>" required>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= safe_string($producto['nombre']) ?>" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Familia</label>
                    <input type="text" name="familia" class="form-control" value="<?= safe_string($producto['familia']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Línea</label>
                    <input type="text" name="linea" class="form-control" value="<?= safe_string($producto['linea']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Color</label>
                    <input type="text" name="color" class="form-control" value="<?= safe_string($producto['color']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Peso Unitario (grs)</label>
                    <input type="number" step="0.01" name="peso_unitario_grs" class="form-control" value="<?= $producto['peso_unitario_grs'] ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Dimensiones</label>
                    <input type="text" name="dimensiones" class="form-control" value="<?= safe_string($producto['dimensiones']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Publicar en Web</label>
                    <select name="publicar_web" class="form-select">
                        <option value="0" <?= !$producto['publicar_web'] ? 'selected' : '' ?>>No</option>
                        <option value="1" <?= $producto['publicar_web'] ? 'selected' : '' ?>>Sí</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción Comercial</label>
                <textarea name="descripcion_comercial" class="form-control" rows="3"><?= safe_string($producto['descripcion_comercial']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

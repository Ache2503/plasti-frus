<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nuevo Rechazo</h1>
    <a href="<?= url('calidad/rechazos') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('calidad/rechazos/store') ?>">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Producto <span class="text-danger">*</span></label>
            <select name="id_producto" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id_producto'] ?>"><?= safe_string($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
            <input type="number" name="cantidad_rechazada" class="form-control" required value="0">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Inspector <span class="text-danger">*</span></label>
            <input type="text" name="inspector" class="form-control" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Motivo del Rechazo <span class="text-danger">*</span></label>
            <textarea name="motivo_rechazo" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Estatus</label>
            <select name="estatus" class="form-select">
                <option value="abierto">Abierto</option>
                <option value="en_revision">En Revisión</option>
                <option value="resuelto">Resuelto</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
</form>
</div></div>

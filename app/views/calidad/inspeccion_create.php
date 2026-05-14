<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nueva Inspección</h1>
    <a href="<?= url('calidad/inspecciones') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('calidad/inspecciones/store') ?>">
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
            <label class="form-label">Orden (opcional)</label>
            <select name="id_orden" class="form-select">
                <option value="">Sin orden</option>
                <?php foreach ($ordenes as $o): ?>
                <option value="<?= $o['id_orden_cabe'] ?>">#<?= $o['id_orden_cabe'] ?> - <?= safe_string($o['producto_nombre'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha_inspeccion" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Inspector <span class="text-danger">*</span></label>
            <input type="text" name="inspector" class="form-control" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 mb-3">
            <label class="form-label">Muestreo</label>
            <input type="number" name="muestreo_piezas" class="form-control" value="0">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Aprobadas</label>
            <input type="number" name="piezas_aprobadas" class="form-control" value="0">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Rechazadas</label>
            <input type="number" name="piezas_rechazadas" class="form-control" value="0">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Resultado <span class="text-danger">*</span></label>
            <select name="resultado" class="form-select" required>
                <option value="aprobado">Aprobado</option>
                <option value="rechazado">Rechazado</option>
                <option value="pendiente">Pendiente</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
</form>
</div></div>

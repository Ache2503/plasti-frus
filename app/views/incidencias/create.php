<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nueva Incidencia</h1>
    <a href="<?= url('incidencias') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('incidencias/store') ?>">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Orden (opcional)</label>
            <select name="id_orden_cabe" class="form-select">
                <option value="">Sin orden</option>
                <?php foreach ($ordenes as $o): ?>
                <option value="<?= $o['id_orden_cabe'] ?>">#<?= $o['id_orden_cabe'] ?> - <?= safe_string($o['producto_nombre'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Impacto</label>
            <select name="impacto" class="form-select">
                <option value="bajo">Bajo</option>
                <option value="medio">Medio</option>
                <option value="alto" selected>Alto</option>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Estatus</label>
            <select name="estatus" class="form-select">
                <option value="abierta">Abierta</option>
                <option value="en_curso">En Curso</option>
                <option value="cerrada">Cerrada</option>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Descripción <span class="text-danger">*</span></label>
            <textarea name="descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Acciones Correctivas</label>
            <textarea name="acciones_correctivas" class="form-control" rows="3"></textarea>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
</form>
</div></div>

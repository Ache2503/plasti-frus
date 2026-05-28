<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-calendar-plus"></i> Programar Mantenimiento</h1>
    <a href="<?= url('mantenimiento') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('mantenimiento/plan/store') ?>">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Máquina <span class="text-danger">*</span></label>
            <select name="id_maquina" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($maquinas as $m): ?>
                <option value="<?= $m['id_maquina'] ?>"><?= safe_string($m['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Fecha Programada <span class="text-danger">*</span></label>
            <input type="date" name="fecha_programada" class="form-control" required>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Tipo <span class="text-danger">*</span></label>
            <select name="tipo_mantenimiento" class="form-select" required>
                <option value="preventivo">Preventivo</option>
                <option value="predictivo">Predictivo</option>
                <option value="calibración">Calibración</option>
            </select>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Frecuencia (horas)</label>
            <input type="number" step="1" name="frecuencia_horas" class="form-control">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Descripción <span class="text-danger">*</span></label>
            <textarea name="descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Último Mantenimiento</label>
            <input type="date" name="ultimo_mantenimiento" class="form-control">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Técnico Responsable</label>
            <select name="id_tecnico_responsable" class="form-select">
                <option value="">Sin asignar</option>
                <?php foreach ($tecnicos as $tecnico): ?>
                <option value="<?= $tecnico['id_usuario'] ?>"><?= safe_string($tecnico['nombre_completo']) ?><?= !empty($tecnico['rol']) ? ' - ' . safe_string($tecnico['rol']) : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Programar</button>
</form>
</div></div>

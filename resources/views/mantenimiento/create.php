<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Registrar Mantenimiento</h1>
    <a href="<?= url('mantenimiento') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('mantenimiento/store') ?>">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Máquina <span class="text-danger">*</span></label>
            <select name="id_maquina" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($maquinas as $m): ?>
                <option value="<?= $m['id_maquina'] ?>"><?= safe_string($m['nombre']) ?> (<?= safe_string($m['estatus']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha_mantenimiento" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Tipo <span class="text-danger">*</span></label>
            <select name="tipo_mantenimiento" class="form-select" required>
                <option value="preventivo">Preventivo</option>
                <option value="correctivo">Correctivo</option>
                <option value="predictivo">Predictivo</option>
            </select>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Horas Paro</label>
            <input type="number" step="0.5" name="horas_paro" class="form-control" value="0">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Técnico Responsable <span class="text-danger">*</span></label>
            <select name="id_tecnico_responsable" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($tecnicos as $tecnico): ?>
                <option value="<?= $tecnico['id_usuario'] ?>"><?= safe_string($tecnico['nombre_completo']) ?><?= !empty($tecnico['rol']) ? ' - ' . safe_string($tecnico['rol']) : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Resultado <span class="text-danger">*</span></label>
            <select name="resultado" class="form-select" required>
                <option value="completado">Completado</option>
                <option value="pendiente">Pendiente</option>
                <option value="parcial">Parcial</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
</form>
</div></div>

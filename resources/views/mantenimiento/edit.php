<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Mantenimiento</h1>
    <a href="<?= url('mantenimiento') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('mantenimiento/update/' . $mantenimiento['id_mantenimiento']) ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Máquina <span class="text-danger">*</span></label>
            <select name="id_maquina" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($maquinas as $m): ?>
                <option value="<?= $m['id_maquina'] ?>" <?= (int) $mantenimiento['id_maquina'] === (int) $m['id_maquina'] ? 'selected' : '' ?>><?= safe_string($m['nombre']) ?> (<?= safe_string($m['estatus']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha_mantenimiento" class="form-control" required value="<?= safe_string($mantenimiento['fecha_mantenimiento']) ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Tipo <span class="text-danger">*</span></label>
            <select name="id_tipo_mantenimiento" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($tipos_mantenimiento as $tipo): ?>
                <option value="<?= $tipo['id_tipo_mantenimiento'] ?>" <?= (int) ($mantenimiento['id_tipo_mantenimiento'] ?? 0) === (int) $tipo['id_tipo_mantenimiento'] ? 'selected' : '' ?>><?= safe_string($tipo['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Horas Paro</label>
            <input type="number" step="0.5" name="horas_paro" class="form-control" value="<?= safe_string($mantenimiento['horas_paro'] ?? 0) ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Técnico Responsable <span class="text-danger">*</span></label>
            <select name="id_tecnico_responsable" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($tecnicos as $tecnico): ?>
                <option value="<?= $tecnico['id_usuario'] ?>" <?= (int) ($mantenimiento['id_tecnico_responsable'] ?? 0) === (int) $tecnico['id_usuario'] ? 'selected' : '' ?>><?= safe_string($tecnico['nombre_completo']) ?><?= !empty($tecnico['rol']) ? ' - ' . safe_string($tecnico['rol']) : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Resultado <span class="text-danger">*</span></label>
            <select name="resultado" class="form-select" required>
                <?php foreach (['completado' => 'Completado', 'pendiente' => 'Pendiente', 'parcial' => 'Parcial'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= ($mantenimiento['resultado'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
</form>
</div></div>

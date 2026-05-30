<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Paro</h1>
    <a href="<?= url('mantenimiento/paros') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('mantenimiento/paros/update/' . $paro['id_bitacora']) ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Máquina <span class="text-danger">*</span></label>
            <select name="id_maquina" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($maquinas as $m): ?>
                <option value="<?= $m['id_maquina'] ?>" <?= (int) $paro['id_maquina'] === (int) $m['id_maquina'] ? 'selected' : '' ?>><?= safe_string($m['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" class="form-control" required value="<?= safe_string($paro['fecha']) ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Hora Inicio <span class="text-danger">*</span></label>
            <input type="time" name="hora_inicio" class="form-control" required value="<?= safe_string(substr((string) $paro['hora_inicio'], 0, 5)) ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Hora Fin</label>
            <input type="time" name="hora_fin" class="form-control" value="<?= safe_string(substr((string) ($paro['hora_fin'] ?? ''), 0, 5)) ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Operador</label>
            <select name="id_operador" class="form-select">
                <option value="">Sin asignar</option>
                <?php foreach ($operadores as $operador): ?>
                <option value="<?= $operador['id_usuario'] ?>" <?= (int) ($paro['id_operador'] ?? 0) === (int) $operador['id_usuario'] ? 'selected' : '' ?>><?= safe_string($operador['nombre_completo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">Motivo del Paro <span class="text-danger">*</span></label>
            <select name="id_motivo_paro" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($motivos_paro as $motivo): ?>
                <option value="<?= $motivo['id_motivo_paro'] ?>" <?= (int) ($paro['id_motivo_paro'] ?? 0) === (int) $motivo['id_motivo_paro'] ? 'selected' : '' ?>><?= safe_string($motivo['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
</form>
</div></div>

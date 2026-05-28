<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Registrar Paro</h1>
    <a href="<?= url('mantenimiento/paros') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('mantenimiento/paros/store') ?>">
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
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Hora Inicio <span class="text-danger">*</span></label>
            <input type="time" name="hora_inicio" class="form-control" required>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Hora Fin</label>
            <input type="time" name="hora_fin" class="form-control">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Operador</label>
            <select name="id_operador" class="form-select">
                <option value="">Sin asignar</option>
                <?php foreach ($operadores as $operador): ?>
                <option value="<?= $operador['id_usuario'] ?>"><?= safe_string($operador['nombre_completo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 mb-3">
            <label class="form-label">Motivo del Paro <span class="text-danger">*</span></label>
            <textarea name="motivo_paro" class="form-control" rows="3" required></textarea>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Registrar</button>
</form>
</div></div>

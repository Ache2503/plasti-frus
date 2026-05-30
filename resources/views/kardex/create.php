<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nuevo Movimiento</h1>
    <a href="<?= url('kardex') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>
<div class="card shadow-sm"><div class="card-body">
<form method="POST" action="<?= url('kardex/store') ?>">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Material <span class="text-danger">*</span></label>
            <select name="id_material" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($materiales as $m): ?>
                <option value="<?= $m['id_material'] ?>"><?= safe_string($m['nombre']) ?> (stock: <?= $m['stock_actual_kg'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Tipo <span class="text-danger">*</span></label>
            <select name="movimiento" class="form-select" required>
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
            </select>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="cantidad" class="form-control" required>
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Fecha <span class="text-danger">*</span></label>
            <input type="date" name="fecha" class="form-control" required value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Operador <span class="text-danger">*</span></label>
            <select name="id_operador" class="form-select" required>
                <option value="">Seleccionar</option>
                <?php foreach ($operadores as $operador): ?>
                <option value="<?= $operador['id_usuario'] ?>"><?= safe_string($operador['nombre_completo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Registrar</button>
</form>
</div></div>

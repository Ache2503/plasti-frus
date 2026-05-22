<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Cuenta</h1>
    <a href="<?= url('contabilidad/plan-cuentas') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= url('contabilidad/plan-cuentas/update/' . $cuenta['id_cuenta']) ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" name="codigo" class="form-control" value="<?= safe_string($cuenta['codigo']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="<?= safe_string($cuenta['nombre']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select" required>
                        <?php foreach (['activo','pasivo','capital','ingreso','gasto'] as $t): ?>
                        <option value="<?= $t ?>" <?= $cuenta['tipo'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nivel</label>
                    <input type="number" name="nivel" class="form-control" value="<?= $cuenta['nivel'] ?>" min="1" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Naturaleza</label>
                    <select name="naturaleza" class="form-select" required>
                        <option value="deudora" <?= $cuenta['naturaleza'] === 'deudora' ? 'selected' : '' ?>>Deudora</option>
                        <option value="acreedora" <?= $cuenta['naturaleza'] === 'acreedora' ? 'selected' : '' ?>>Acreedora</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Cuenta Padre</label>
                    <select name="id_padre" class="form-select">
                        <option value="">-- Sin padre --</option>
                        <?php foreach ($cuentas_padre as $padre): ?>
                        <option value="<?= $padre['id_cuenta'] ?>" <?= $cuenta['id_padre'] == $padre['id_cuenta'] ? 'selected' : '' ?>><?= safe_string($padre['codigo'] . ' - ' . $padre['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Activo</label>
                    <select name="activo" class="form-select">
                        <option value="1" <?= $cuenta['activo'] ? 'selected' : '' ?>>Sí</option>
                        <option value="0" <?= !$cuenta['activo'] ? 'selected' : '' ?>>No</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

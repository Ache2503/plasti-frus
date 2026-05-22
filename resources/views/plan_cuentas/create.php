<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nueva Cuenta</h1>
    <a href="<?= url('contabilidad/plan-cuentas') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= url('contabilidad/plan-cuentas/store') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" name="codigo" class="form-control" required placeholder="Ej: 1.1.1.001">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select name="tipo" class="form-select" required>
                        <option value="activo">Activo</option>
                        <option value="pasivo">Pasivo</option>
                        <option value="capital">Capital</option>
                        <option value="ingreso">Ingreso</option>
                        <option value="gasto">Gasto</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nivel <span class="text-danger">*</span></label>
                    <input type="number" name="nivel" class="form-control" min="1" max="10" value="3" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Naturaleza <span class="text-danger">*</span></label>
                    <select name="naturaleza" class="form-select" required>
                        <option value="deudora">Deudora</option>
                        <option value="acreedora">Acreedora</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Cuenta Padre</label>
                    <select name="id_padre" class="form-select">
                        <option value="">-- Sin padre (raíz) --</option>
                        <?php foreach ($cuentas_padre as $padre): ?>
                        <option value="<?= $padre['id_cuenta'] ?>"><?= safe_string($padre['codigo'] . ' - ' . $padre['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
        </form>
    </div>
</div>

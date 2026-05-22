<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nuevo Cliente</h1>
    <a href="<?= url('clientes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('clientes/store') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                    <input type="text" name="razon_social" class="form-control" value="<?= safe_string(old('razon_social')) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">RFC</label>
                    <input type="text" name="rfc" class="form-control" placeholder="XXXX000000XXX" value="<?= safe_string(old('rfc')) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sector</label>
                    <input type="text" name="sector" class="form-control" list="sectores" value="<?= safe_string(old('sector')) ?>">
                    <datalist id="sectores"><?php foreach ($sectores as $s): ?><option value="<?= safe_string($s['sector']) ?>"><?php endforeach; ?></datalist>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" value="<?= safe_string(old('ciudad')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <input type="text" name="estado" class="form-control" value="<?= safe_string(old('estado')) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="<?= safe_string(old('telefono')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="correo" class="form-control" value="<?= safe_string(old('correo')) ?>">
            </div>
            <?php if (isset($vendedores)): ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vendedor Asignado</label>
                    <select name="id_vendedor" class="form-select">
                        <option value="">Sin vendedor</option>
                        <?php foreach ($vendedores as $vend): ?>
                        <option value="<?= $vend['id_usuario'] ?>" <?= old('id_vendedor') == $vend['id_usuario'] ? 'selected' : '' ?>>
                            <?= safe_string($vend['nombre'] . ' ' . ($vend['apellido_paterno'] ?? '')) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
        </form>
    </div>
</div>

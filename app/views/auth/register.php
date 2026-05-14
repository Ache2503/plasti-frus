<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 50%, #16213e 100%);">
    <div class="col-md-6 col-lg-5 px-3">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle" style="width: 64px; height: 64px;">
                <i class="bi bi-person-plus" style="color: #1a1a2e; font-size: 2rem;"></i>
            </div>
            <h4 class="text-white mt-3 fw-bold">Crear Cuenta</h4>
            <p class="text-white-50 small">Registro en Plasti Frus</p>
        </div>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger py-2 small"><?= safe_string($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <form method="POST" action="<?= url('register') ?>" id="registerForm">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tipo de Cuenta <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select" id="tipoCuenta" required>
                            <option value="vendedor" <?= old('tipo', 'vendedor') === 'vendedor' ? 'selected' : '' ?>>Vendedor (Personal interno)</option>
                            <option value="cliente" <?= old('tipo') === 'cliente' ? 'selected' : '' ?>>Cliente (Acceso a portal)</option>
                        </select>
                    </div>

                    <div id="vendedorFields" <?= old('tipo') === 'cliente' ? 'style="display:none"' : '' ?>>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Nombre <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" name="nombre" class="form-control border-start-0 ps-0" placeholder="Nombre" value="<?= safe_string(old('nombre')) ?>" required autofocus>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-semibold">Apellido</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" name="apellido" class="form-control border-start-0 ps-0" placeholder="Apellido" value="<?= safe_string(old('apellido')) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="clienteFields" <?= old('tipo') === 'cliente' ? '' : 'style="display:none"' ?>>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Empresa <span class="text-danger">*</span></label>
                            <select name="id_cliente" class="form-select">
                                <option value="">Seleccionar empresa</option>
                                <?php $oldCliente = old('id_cliente'); ?>
                                <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id_cliente'] ?>" <?= (string)$oldCliente === (string)$c['id_cliente'] ? 'selected' : '' ?>><?= safe_string($c['razon_social'] . ' (' . $c['rfc'] . ')') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Selecciona la empresa a la que perteneces</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Usuario <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-at text-muted"></i></span>
                            <input type="text" name="nombre_usuario" class="form-control border-start-0 ps-0" placeholder="Nombre de usuario" value="<?= safe_string(old('nombre_usuario')) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Contraseña <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Mínimo 6 caracteres" required minlength="6">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Confirmar Contraseña <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
                            <input type="password" name="password_confirm" class="form-control border-start-0 ps-0" placeholder="Repite la contraseña" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold rounded-3">
                        <i class="bi bi-person-check me-1"></i> Crear Cuenta
                    </button>
                    <div class="text-center mt-3">
                        <small class="text-muted">¿Ya tienes cuenta? <a href="<?= url('login') ?>" class="fw-semibold">Inicia sesión</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('tipoCuenta').addEventListener('change', function() {
    var isCliente = this.value === 'cliente';
    document.getElementById('vendedorFields').style.display = isCliente ? 'none' : '';
    document.getElementById('clienteFields').style.display = isCliente ? '' : 'none';
    document.querySelector('[name="nombre"]').required = !isCliente;
    document.querySelector('[name="id_cliente"]').required = isCliente;
});
</script>

<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 50%, #16213e 100%);">
    <div class="col-md-5 col-lg-4 px-3">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle" style="width: 64px; height: 64px;">
                <i class="bi bi-gear-fill" style="color: #1a1a2e; font-size: 2rem;"></i>
            </div>
            <h4 class="text-white mt-3 fw-bold">Plasti Frus</h4>
            <p class="text-white-50 small">Sistema de Gestión de Producción</p>
        </div>

        <div class="card shadow-lg border-0 rounded-4" style="backdrop-filter: blur(10px);">
            <div class="card-body p-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger py-2 small"><?= safe_string($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <form method="POST" action="<?= url('login') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="nombre_usuario" class="form-control border-start-0 ps-0" placeholder="Ingresa tu usuario" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold rounded-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                    </button>
                </form>
                    <div class="d-flex justify-content-between mt-3">
                        <small class="text-muted"><a href="<?= url('olvide-contrasena') ?>" class="text-decoration-none">¿Olvidaste tu contraseña?</a></small>
                        <small class="text-muted">¿No tienes cuenta? <a href="<?= url('register') ?>" class="fw-semibold">Regístrate</a></small>
                    </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-white-50">© <?= date('Y') ?> Plasti Frus</small>
        </div>
    </div>
</div>

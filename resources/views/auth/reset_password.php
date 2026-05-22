<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-lock"></i> Nueva Contraseña</h4>
                </div>
                <div class="card-body p-4">
                    <?php flash_message() ?>
                    <form method="POST" action="<?= url('restablecer-contrasena') ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="token" value="<?= safe_string($token) ?>">
                        <input type="hidden" name="email" value="<?= safe_string($email) ?>">
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="password" class="form-control" minlength="6" required placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="password_confirm" class="form-control" minlength="6" required placeholder="Repite la contraseña">
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Restablecer Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

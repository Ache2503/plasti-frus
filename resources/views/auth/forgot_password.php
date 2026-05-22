<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-key"></i> Recuperar Contraseña</h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
                    <?php flash_message() ?>
                    <form method="POST" action="<?= url('olvide-contrasena') ?>">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" required placeholder="tu@correo.com">
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Enviar Enlace</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="<?= url('login') ?>" class="text-decoration-none small">← Volver al inicio de sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

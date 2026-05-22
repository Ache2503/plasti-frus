<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person-circle"></i> Mi Perfil</h1>
    <span class="badge bg-secondary fs-6"><?= safe_string($usuario['rol'] ?? 'Usuario') ?></span>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <!-- Información de Usuario -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-info-circle"></i> Informaci&oacute;n de Usuario</div>
            <div class="card-body">
                <table class="table table-sm borderless mb-0">
                    <tr>
                        <td class="text-muted ps-0" style="width: 30%;">Usuario</td>
                        <td class="fw-semibold"><?= safe_string($usuario['nombre_usuario']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Nombre</td>
                        <td class="fw-semibold">
                            <?php if (es_cliente() && $cliente): ?>
                                <?= safe_string($cliente['contacto_nombre'] ?: $cliente['razon_social']) ?>
                            <?php else: ?>
                                <?= safe_string(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido_paterno'] ?? '') . ' ' . ($usuario['apellido_materno'] ?? ''))) ?: $usuario['nombre_usuario'] ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Rol</td>
                        <td><span class="badge bg-info"><?= safe_string($usuario['rol']) ?></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (es_cliente() && $cliente): ?>
        <!-- Datos del Representante -->
        <div class="card shadow-sm mt-4">
            <div class="card-header" style="background: linear-gradient(135deg, #0f3460 0%, #1a4a7a 100%); color: #fff;">
                <i class="bi bi-person-badge"></i> Datos del Representante
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">Informaci&oacute;n de la persona de contacto encargada de la cuenta.</p>
                <form method="POST" action="<?= url('profile/update-contacto') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label">Nombre completo del representante</label>
                        <input type="text" name="contacto_nombre" class="form-control" value="<?= safe_string($cliente['contacto_nombre'] ?? '') ?>" placeholder="Nombre y apellidos">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cargo / Puesto</label>
                        <input type="text" name="contacto_cargo" class="form-control" value="<?= safe_string($cliente['contacto_cargo'] ?? '') ?>" placeholder="Gerente, due&ntilde;o, administrador...">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tel&eacute;fono</label>
                            <input type="text" name="contacto_telefono" class="form-control" value="<?= safe_string($cliente['contacto_telefono'] ?? '') ?>" placeholder="+52 000 000 0000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo electr&oacute;nico</label>
                            <input type="email" name="contacto_correo" class="form-control" value="<?= safe_string($cliente['contacto_correo'] ?? '') ?>" placeholder="correo@ejemplo.com">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar Representante</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <!-- Información Personal (para empleados internos) -->
        <div class="card shadow-sm mt-4">
            <div class="card-header" style="background: linear-gradient(135deg, #0f3460 0%, #1a4a7a 100%); color: #fff;">
                <i class="bi bi-person-lines-fill"></i> Informaci&oacute;n Personal
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('profile/update-personal') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control" value="<?= safe_string($usuario['nombre'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control" value="<?= safe_string($usuario['apellido_paterno'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Ap. Materno</label>
                            <input type="text" name="apellido_materno" class="form-control" value="<?= safe_string($usuario['apellido_materno'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo electr&oacute;nico</label>
                        <input type="email" name="correo" class="form-control" value="<?= safe_string($usuario['correo'] ?? '') ?>" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tel&eacute;fono</label>
                        <input type="text" name="telefono" class="form-control" value="<?= safe_string($usuario['telefono'] ?? '') ?>" placeholder="+52 000 000 0000">
                    </div>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar Datos Personales</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cambiar Contraseña -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-warning text-dark"><i class="bi bi-key"></i> Cambiar Contrase&ntilde;a</div>
            <div class="card-body">
                <form method="POST" action="<?= url('profile/update-password') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label">Contrase&ntilde;a Actual</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Contrase&ntilde;a</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar Nueva Contrase&ntilde;a</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar Contrase&ntilde;a</button>
                </form>
            </div>
        </div>
    </div>

    <?php if (es_cliente() && $cliente): ?>
    <div class="col-md-6 mb-4">
        <!-- Datos de la Empresa -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-building"></i> Datos de la Empresa</div>
            <div class="card-body">
                <form method="POST" action="<?= url('profile/update-cliente') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label">Raz&oacute;n Social</label>
                        <input type="text" name="razon_social" class="form-control" value="<?= safe_string($cliente['razon_social'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RFC</label>
                        <input type="text" name="rfc" class="form-control" value="<?= safe_string($cliente['rfc'] ?? '') ?>" placeholder="XXXX000000XXX">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control" value="<?= safe_string($cliente['ciudad'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Estado</label>
                            <input type="text" name="estado" class="form-control" value="<?= safe_string($cliente['estado'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">C&oacute;digo Postal</label>
                        <input type="text" name="codigo_postal" class="form-control" value="<?= safe_string($cliente['codigo_postal'] ?? '') ?>" maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Domicilio fiscal</label>
                        <input type="text" name="domicilio" class="form-control" value="<?= safe_string($cliente['domicilio'] ?? '') ?>" placeholder="Calle, n&uacute;mero, colonia">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Referencia de domicilio</label>
                        <input type="text" name="referencia_domicilio" class="form-control" value="<?= safe_string($cliente['referencia_domicilio'] ?? '') ?>" placeholder="Entre calles, puntos de referencia">
                    </div>
                    <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

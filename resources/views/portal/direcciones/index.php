<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-geo-alt"></i> Mis Direcciones</h1>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#nuevaDireccionModal">
        <i class="bi bi-plus-lg"></i> Nueva Dirección
    </button>
</div>

<?php if (empty($direcciones)): ?>
<div class="text-center py-5">
    <i class="bi bi-geo-alt text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted">No tienes direcciones guardadas</h4>
    <p class="text-muted">Agrega direcciones de envío para agilizar tus compras.</p>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#nuevaDireccionModal">
        <i class="bi bi-plus-lg"></i> Agregar Dirección
    </button>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($direcciones as $d): ?>
    <div class="col-md-6">
        <div class="card shadow-sm h-100 <?= $d['predeterminada'] ? 'border-primary' : '' ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="card-title mb-1">
                            <?= safe_string($d['alias']) ?>
                            <?php if ($d['predeterminada']): ?>
                            <span class="badge bg-primary ms-1">Predeterminada</span>
                            <?php endif; ?>
                        </h5>
                        <?php if (!empty($d['destinatario'])): ?>
                        <small class="text-muted">Attn: <?= safe_string($d['destinatario']) ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editarDireccionModal<?= $d['id_direccion'] ?>"><i class="bi bi-pencil"></i> Editar</button></li>
                            <?php if (!$d['predeterminada']): ?>
                            <li>
                                <form method="POST" action="<?= url('direcciones/predeterminada/' . $d['id_direccion']) ?>" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="dropdown-item"><i class="bi bi-check-circle"></i> Hacer predeterminada</button>
                                </form>
                            </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?= url('direcciones/eliminar/' . $d['id_direccion']) ?>" class="d-inline" onsubmit="return confirm('¿Eliminar esta dirección?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i> Eliminar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <p class="card-text mb-1">
                    <?= safe_string($d['calle']) ?>
                    <?= !empty($d['numero_exterior']) ? ' #' . safe_string($d['numero_exterior']) : '' ?>
                    <?= !empty($d['numero_interior']) ? ' Int. ' . safe_string($d['numero_interior']) : '' ?>
                </p>
                <?php if (!empty($d['colonia'])): ?>
                <p class="card-text mb-1"><?= safe_string($d['colonia']) ?></p>
                <?php endif; ?>
                <p class="card-text mb-1"><?= safe_string($d['ciudad']) ?>, <?= safe_string($d['estado']) ?>, CP <?= safe_string($d['codigo_postal']) ?></p>
                <?php if (!empty($d['telefono_contacto'])): ?>
                <p class="card-text small text-muted mb-0"><i class="bi bi-telephone"></i> <?= safe_string($d['telefono_contacto']) ?></p>
                <?php endif; ?>
                <?php if (!empty($d['referencia'])): ?>
                <p class="card-text small text-muted mb-0"><i class="bi bi-pin-map"></i> <?= safe_string($d['referencia']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="editarDireccionModal<?= $d['id_direccion'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Editar Dirección</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= url('direcciones/actualizar/' . $d['id_direccion']) ?>">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <?php $fields = [
                            'alias' => ['label' => 'Alias', 'type' => 'text', 'required' => true],
                            'destinatario' => ['label' => 'Destinatario', 'type' => 'text', 'required' => false],
                            'telefono_contacto' => ['label' => 'Teléfono de contacto', 'type' => 'text', 'required' => false],
                            'calle' => ['label' => 'Calle', 'type' => 'text', 'required' => true],
                            'numero_exterior' => ['label' => 'Núm. Exterior', 'type' => 'text', 'required' => false],
                            'numero_interior' => ['label' => 'Núm. Interior', 'type' => 'text', 'required' => false],
                            'colonia' => ['label' => 'Colonia', 'type' => 'text', 'required' => false],
                            'ciudad' => ['label' => 'Ciudad', 'type' => 'text', 'required' => true],
                            'estado' => ['label' => 'Estado', 'type' => 'text', 'required' => true],
                            'codigo_postal' => ['label' => 'Código Postal', 'type' => 'text', 'required' => true],
                            'referencia' => ['label' => 'Referencia', 'type' => 'textarea', 'required' => false],
                        ]; ?>
                        <?php foreach ($fields as $field => $cfg): ?>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1"><?= $cfg['label'] ?><?= $cfg['required'] ? ' <span class="text-danger">*</span>' : '' ?></label>
                            <?php if ($cfg['type'] === 'textarea'): ?>
                            <textarea name="<?= $field ?>" class="form-control form-control-sm"><?= safe_string($d[$field] ?? '') ?></textarea>
                            <?php else: ?>
                            <input type="<?= $cfg['type'] ?>" name="<?= $field ?>" class="form-control form-control-sm" value="<?= safe_string($d[$field] ?? '') ?>" <?= $cfg['required'] ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-dark">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal Nueva Dirección -->
<div class="modal fade" id="nuevaDireccionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Nueva Dirección</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('direcciones/agregar') ?>">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <?php $fields = [
                        'alias' => ['label' => 'Alias', 'type' => 'text', 'required' => true, 'placeholder' => 'Ej: Oficina, Casa'],
                        'destinatario' => ['label' => 'Destinatario', 'type' => 'text', 'required' => false],
                        'telefono_contacto' => ['label' => 'Teléfono de contacto', 'type' => 'text', 'required' => false],
                        'calle' => ['label' => 'Calle', 'type' => 'text', 'required' => true],
                        'numero_exterior' => ['label' => 'Núm. Exterior', 'type' => 'text', 'required' => false],
                        'numero_interior' => ['label' => 'Núm. Interior', 'type' => 'text', 'required' => false],
                        'colonia' => ['label' => 'Colonia', 'type' => 'text', 'required' => false],
                        'ciudad' => ['label' => 'Ciudad', 'type' => 'text', 'required' => true],
                        'estado' => ['label' => 'Estado', 'type' => 'text', 'required' => true],
                        'codigo_postal' => ['label' => 'Código Postal', 'type' => 'text', 'required' => true],
                        'referencia' => ['label' => 'Referencia', 'type' => 'textarea', 'required' => false],
                    ]; ?>
                    <?php foreach ($fields as $field => $cfg): ?>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold mb-1"><?= $cfg['label'] ?><?= $cfg['required'] ? ' <span class="text-danger">*</span>' : '' ?></label>
                        <?php if ($cfg['type'] === 'textarea'): ?>
                        <textarea name="<?= $field ?>" class="form-control form-control-sm" placeholder="<?= $cfg['placeholder'] ?? '' ?>"></textarea>
                        <?php else: ?>
                        <input type="<?= $cfg['type'] ?>" name="<?= $field ?>" class="form-control form-control-sm" placeholder="<?= $cfg['placeholder'] ?? '' ?>" <?= $cfg['required'] ? 'required' : '' ?>>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <div class="form-check mt-3">
                        <input type="checkbox" name="predeterminada" class="form-check-input" value="1" id="predeterminadaNew" <?= empty($direcciones) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="predeterminadaNew">Establecer como dirección predeterminada</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-dark">Guardar Dirección</button>
                </div>
            </form>
        </div>
    </div>
</div>

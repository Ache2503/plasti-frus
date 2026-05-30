<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Orden de Producción</h1>
    <a href="<?= url('ordenes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="<?= url('ordenes/update/' . $orden['id_orden_cabe']) ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Producto <span class="text-danger">*</span></label>
                            <select name="id_producto" class="form-select" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id_producto'] ?>" <?= (int) $orden['id_producto'] === (int) $p['id_producto'] ? 'selected' : '' ?>><?= safe_string($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cant. Planificada <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad_planificada" class="form-control" value="<?= safe_string($orden['cantidad_planificada']) ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Máquina</label>
                            <select name="id_maquina" class="form-select">
                                <option value="">Seleccionar</option>
                                <?php foreach ($maquinas as $m): ?>
                                <option value="<?= $m['id_maquina'] ?>" <?= (int) ($orden['id_maquina'] ?? 0) === (int) $m['id_maquina'] ? 'selected' : '' ?>><?= safe_string($m['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Molde</label>
                            <select name="id_molde" class="form-select">
                                <option value="">Seleccionar</option>
                                <?php foreach ($moldes as $mo): ?>
                                <option value="<?= $mo['id_molde'] ?>" <?= (int) ($orden['id_molde'] ?? 0) === (int) $mo['id_molde'] ? 'selected' : '' ?>><?= safe_string($mo['nombre_molde']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Receta</label>
                            <select name="id_receta" class="form-select">
                                <option value="">Seleccionar</option>
                                <?php foreach ($recetas as $r): ?>
                                <option value="<?= $r['id_receta_cabe'] ?>" <?= (int) ($orden['id_receta'] ?? 0) === (int) $r['id_receta_cabe'] ? 'selected' : '' ?>><?= safe_string('V' . $r['version'] . ' - ' . $r['id_receta_cabe']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= safe_string($orden['fecha']) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Turno</label>
                            <select name="turno" class="form-select" required>
                                <?php foreach (['matutino' => 'Matutino', 'vespertino' => 'Vespertino', 'nocturno' => 'Nocturno'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($orden['turno'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Cant. Real</label>
                            <input type="number" name="cantidad_real_buenas" class="form-control" value="<?= safe_string($orden['cantidad_real_buenas'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Estatus</label>
                            <select name="estatus" class="form-select">
                                <?php foreach (['pendiente' => 'Pendiente', 'en_progreso' => 'En Progreso', 'completada' => 'Completada'] as $value => $label): ?>
                                <option value="<?= $value ?>" <?= ($orden['estatus'] ?? '') === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark btn-lg px-5"><i class="bi bi-save"></i> Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

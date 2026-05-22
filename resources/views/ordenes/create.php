<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nueva Orden de Producción</h1>
    <a href="<?= url('ordenes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="<?= url('ordenes/store') ?>">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Producto <span class="text-danger">*</span></label>
                            <select name="id_producto" class="form-select" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($productos as $p): ?>
                                <option value="<?= $p['id_producto'] ?>"><?= safe_string($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cant. Planificada <span class="text-danger">*</span></label>
                            <input type="number" name="cantidad_planificada" class="form-control" placeholder="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Máquina</label>
                            <select name="id_maquina" class="form-select">
                                <option value="">Seleccionar</option>
                                <?php foreach ($maquinas as $m): ?>
                                <option value="<?= $m['id_maquina'] ?>"><?= safe_string($m['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Molde</label>
                            <select name="id_molde" class="form-select">
                                <option value="">Seleccionar</option>
                                <?php foreach ($moldes as $mo): ?>
                                <option value="<?= $mo['id_molde'] ?>"><?= safe_string($mo['nombre_molde']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Receta</label>
                            <select name="id_receta" class="form-select">
                                <option value="">Seleccionar</option>
                                <?php foreach ($recetas as $r): ?>
                                <option value="<?= $r['id_receta_cabe'] ?>"><?= safe_string('V' . $r['version'] . ' - ' . $r['id_receta_cabe']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Turno</label>
                            <select name="turno" class="form-select">
                                <option value="">Seleccionar</option>
                                <option value="matutino">Matutino</option>
                                <option value="vespertino">Vespertino</option>
                                <option value="nocturno">Nocturno</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cant. Real (Buenas)</label>
                            <input type="number" name="cantidad_real_buenas" class="form-control" placeholder="Opcional">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark btn-lg px-5"><i class="bi bi-save"></i> Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Receta</h1>
    <a href="<?= url('recetas') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('recetas/update/' . $receta['id_receta_cabe']) ?>">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Producto</label>
                    <select name="id_producto" class="form-select" required>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>" <?= ($receta['id_producto'] ?? '') == $p['id_producto'] ? 'selected' : '' ?>><?= safe_string($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Versión</label>
                    <input type="text" name="version" class="form-control" value="<?= safe_string($receta['version']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fecha Versión</label>
                    <input type="date" name="fecha_version" class="form-control" value="<?= $receta['fecha_version'] ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Máquina</label>
                    <select name="id_maquina" class="form-select">
                        <option value="">N/A</option>
                        <?php foreach ($maquinas as $m): ?>
                        <option value="<?= $m['id_maquina'] ?>" <?= ($receta['id_maquina'] ?? '') == $m['id_maquina'] ? 'selected' : '' ?>><?= safe_string($m['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Temperatura (°C)</label>
                    <input type="number" name="temperatura_inyeccion_C" class="form-control" value="<?= $receta['temperatura_inyeccion_C'] ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Presión (bar)</label>
                    <input type="number" name="presion_inyeccion_bar" class="form-control" value="<?= $receta['presion_inyeccion_bar'] ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Enfriamiento (s)</label>
                    <input type="number" name="tiempo_enfriamiento_s" class="form-control" value="<?= $receta['tiempo_enfriamiento_s'] ?>">
                </div>
            </div>

            <hr>
            <h5><i class="bi bi-list-check"></i> Materiales de la Receta</h5>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead><tr><th>Material</th><th>% Peso</th><th>Tolerancia</th></tr></thead>
                    <tbody>
                        <?php foreach ($detalles as $d): ?>
                        <tr>
                            <td><?= safe_string($d['material_nombre'] ?? 'N/A') ?></td>
                            <td><?= $d['porcentaje_peso'] ?>%</td>
                            <td>±<?= $d['tolerancia_percent'] ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($detalles)): ?>
                        <tr><td colspan="3" class="text-muted text-center">Sin materiales asignados</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

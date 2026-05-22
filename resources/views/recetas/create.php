<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nueva Receta</h1>
    <a href="<?= url('recetas') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('recetas/store') ?>" id="recetaForm">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Producto <span class="text-danger">*</span></label>
                    <select name="id_producto" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>"><?= safe_string($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Versión <span class="text-danger">*</span></label>
                    <input type="text" name="version" class="form-control" placeholder="ej. 1.0" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fecha Versión</label>
                    <input type="date" name="fecha_version" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Máquina</label>
                    <select name="id_maquina" class="form-select">
                        <option value="">N/A</option>
                        <?php foreach ($maquinas as $m): ?>
                        <option value="<?= $m['id_maquina'] ?>"><?= safe_string($m['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Temperatura Inyección (°C)</label>
                    <input type="number" name="temperatura_inyeccion_C" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Presión Inyección (bar)</label>
                    <input type="number" name="presion_inyeccion_bar" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tiempo Enfriamiento (s)</label>
                    <input type="number" name="tiempo_enfriamiento_s" class="form-control">
                </div>
            </div>

            <hr>
            <h5><i class="bi bi-list-check"></i> Materiales de la Receta</h5>
            <div id="materialesContainer">
                <div class="row material-row mb-2">
                    <div class="col-md-5"><select name="materiales[]" class="form-select"><option value="">Seleccionar</option><?php foreach ($materiales as $mat): ?><option value="<?= $mat['id_material'] ?>"><?= safe_string($mat['nombre']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-3"><input type="number" step="0.01" name="porcentajes[]" class="form-control" placeholder="% Peso"></div>
                    <div class="col-md-3"><input type="number" step="0.01" name="tolerancias[]" class="form-control" placeholder="± % Tolerancia"></div>
                    <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm remove-material"><i class="bi bi-x"></i></button></div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addMaterial"><i class="bi bi-plus"></i> Agregar Material</button>

            <div>
                <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('addMaterial')?.addEventListener('click', function() {
    const container = document.getElementById('materialesContainer');
    const firstRow = container.querySelector('.material-row');
    const newRow = firstRow.cloneNode(true);
    newRow.querySelectorAll('select, input').forEach(el => el.value = '');
    container.appendChild(newRow);
});
document.querySelectorAll('.remove-material').forEach(btn => {
    btn.addEventListener('click', function() {
        const rows = document.querySelectorAll('.material-row');
        if (rows.length > 1) this.closest('.material-row').remove();
    });
});
</script>

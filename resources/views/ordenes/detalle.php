<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clipboard-check"></i> Orden #<?= $orden['id_orden_cabe'] ?></h1>
    <div>
        <?php if (empty($orden['cantidad_real_buenas'])): ?>
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#completarModal">
            <i class="bi bi-check-lg"></i> Completar
        </button>
        <?php endif; ?>
        <a href="<?= url('ordenes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-info-circle"></i> Datos de la Orden</div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Producto</th><td><?= safe_string($orden['producto_nombre'] ?? 'N/A') ?></td></tr>
                    <tr><th>Código Producto</th><td><?= safe_string($orden['producto_codigo'] ?? 'N/A') ?></td></tr>
                    <tr><th>Máquina</th><td><?= safe_string($orden['maquina_nombre'] ?? 'N/A') ?> (<?= safe_string($orden['maquina_modelo'] ?? '') ?>)</td></tr>
                    <tr><th>Molde</th><td><?= safe_string($orden['molde_nombre'] ?? 'N/A') ?> (<?= $orden['numero_cavidades'] ?? '' ?> cav.)</td></tr>
                    <tr><th>Receta</th><td>Versión <?= safe_string($orden['receta_version'] ?? 'N/A') ?></td></tr>
                    <tr><th>Cant. Planificada</th><td><?= $orden['cantidad_planificada'] ?></td></tr>
                    <tr><th>Cant. Real (Buenas)</th><td><?= $orden['cantidad_real_buenas'] ?? 'Pendiente' ?></td></tr>
                    <tr><th>Fecha</th><td><?= format_date($orden['fecha']) ?></td></tr>
                    <tr><th>Turno</th><td><?= safe_string($orden['turno']) ?></td></tr>
                </table>
            </div>
        </div>
        <?php if ($orden['temperatura_inyeccion_C'] || $orden['presion_inyeccion_bar']): ?>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-info text-white"><i class="bi bi-sliders"></i> Parámetros de Receta</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><th>Temperatura Inyección</th><td><?= $orden['temperatura_inyeccion_C'] ?> °C</td></tr>
                    <tr><th>Presión Inyección</th><td><?= $orden['presion_inyeccion_bar'] ?> bar</td></tr>
                    <tr><th>Tiempo Enfriamiento</th><td><?= $orden['tiempo_enfriamiento_s'] ?> s</td></tr>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark"><i class="bi bi-trash"></i> Mermas</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Tipo</th><th>Cantidad (kg)</th><th>Destino</th></tr></thead>
                    <tbody>
                        <?php foreach ($mermas as $m): ?>
                        <tr><td><?= safe_string($m['tipo_merma']) ?></td><td><?= number_format($m['cantidad_kg'], 2) ?></td><td><?= safe_string($m['destino']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($mermas)): ?>
                        <tr><td colspan="3" class="text-muted text-center">Sin mermas registradas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-secondary text-white"><i class="bi bi-clock-history"></i> Seguimiento</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Fecha</th><th>Estatus</th><th>Comentarios</th></tr></thead>
                    <tbody>
                        <?php foreach ($seguimiento as $s): ?>
                        <tr><td><?= format_datetime($s['fecha']) ?></td><td><?= safe_string($s['estatus']) ?></td><td><?= safe_string($s['comentarios']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($seguimiento)): ?>
                        <tr><td colspan="3" class="text-muted text-center">Sin seguimiento</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
    </div>
</div>

<div class="modal fade" id="completarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('ordenes/completar/' . $orden['id_orden_cabe']) ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-lg"></i> Completar Orden</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Cantidad Real (Buenas)</label>
                        <input type="number" name="cantidad_real_buenas" class="form-control" required value="<?= $orden['cantidad_planificada'] ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Merma (kg) <span class="text-muted">opcional</span></label>
                        <input type="number" step="0.01" name="merma_kg" class="form-control" placeholder="0.00">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tipo de Merma</label>
                        <select name="merma_tipo" class="form-select">
                            <option value="general">General</option>
                            <option value="arranque">Arranque</option>
                            <option value="defecto">Pieza defectuosa</option>
                            <option value="sobrante">Sobrante</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Destino Merma</label>
                        <select name="merma_destino" class="form-select">
                            <option value="reciclaje">Reciclaje</option>
                            <option value="desperdicio">Desperdicio</option>
                            <option value="reproceso">Reproceso</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

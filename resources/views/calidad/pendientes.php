<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-clipboard-check"></i> Inspecciones Pendientes</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('calidad/inspecciones') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-list"></i> Todas las Inspecciones</a>
        <a href="<?= url('calidad/rechazos') ?>" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle"></i> Rechazos</a>
    </div>
</div>

<?php if (empty($pendientes)): ?>
<div class="alert alert-success"><i class="bi bi-check-circle"></i> No hay inspecciones pendientes.</div>
<?php else: ?>
<div class="row g-2">
    <?php foreach ($pendientes as $ins): ?>
    <div class="col-12 col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark py-1 px-2 d-flex justify-content-between">
                <span><i class="bi bi-search"></i> <?= safe_string($ins['id_inspeccion']) ?></span>
                <span class="small"><?= format_date($ins['fecha_inspeccion']) ?></span>
            </div>
            <div class="card-body py-1 px-2" style="font-size:0.8rem;">
                <div><strong>Producto:</strong> <?= safe_string($ins['producto_nombre'] ?? 'N/A') ?></div>
                <?php if ($ins['maquina_nombre']): ?>
                <div><strong>Máquina:</strong> <?= safe_string($ins['maquina_nombre']) ?></div>
                <?php endif; ?>
                <div><strong>Turno:</strong> <?= safe_string($ins['turno'] ?? '—') ?> | <strong>Plan:</strong> <?= $ins['cantidad_planificada'] ?? '—' ?></div>
                <div><strong>Muestreo:</strong> <?= (int) $ins['muestreo_piezas'] ?> piezas</div>
            </div>
            <div class="card-footer p-1">
                <button type="button" class="btn btn-sm btn-success w-100" data-bs-toggle="modal" data-bs-target="#inspeccionModal"
                    data-id="<?= safe_string($ins['id_inspeccion']) ?>"
                    data-producto="<?= safe_string($ins['producto_nombre'] ?? '') ?>"
                    data-muestreo="<?= (int) $ins['muestreo_piezas'] ?>">
                    <i class="bi bi-check-lg"></i> Realizar Inspección
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal fade" id="inspeccionModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-check-circle"></i> Realizar Inspección</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="font-size:0.85rem;">
                    <p><strong id="inspeccionProducto"></strong></p>
                    <div class="mb-2">
                        <label class="form-label small">Piezas Aprobadas</label>
                        <input type="number" name="piezas_aprobadas" class="form-control form-control-sm" required min="0">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Piezas Rechazadas</label>
                        <input type="number" name="piezas_rechazadas" class="form-control form-control-sm" required min="0">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Resultado</label>
                        <select name="resultado" class="form-select form-select-sm" required>
                            <option value="conforme">Conforme</option>
                            <option value="no_conforme">No Conforme</option>
                            <option value="observaciones">Con Observaciones</option>
                        </select>
                    </div>
                    <div class="mb-2" id="motivoRechazoGroup" style="display:none;">
                        <label class="form-label small">Motivo de Rechazo</label>
                        <select name="id_motivo_rechazo" class="form-select form-select-sm">
                            <option value="">Seleccionar...</option>
                            <?php foreach (($motivos_rechazo ?? []) as $motivo): ?>
                            <option value="<?= $motivo['id_motivo_rechazo'] ?>"><?= safe_string($motivo['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-save"></i> Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('inspeccionModal');
    modal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var producto = button.getAttribute('data-producto');
        document.getElementById('inspeccionProducto').textContent = producto;
        modal.querySelector('form').action = '<?= url('calidad/inspecciones/realizar/') ?>' + id;
    });
    modal.querySelector('[name="resultado"]').addEventListener('change', function() {
        document.getElementById('motivoRechazoGroup').style.display = this.value === 'no_conforme' ? 'block' : 'none';
    });
});
</script>

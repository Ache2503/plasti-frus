<div class="modal fade" id="completarModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="" id="completarForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="redirect_to" value="<?= $_SERVER['REQUEST_URI'] ?>">
                <div class="modal-header bg-success text-white py-1">
                    <h5 class="modal-title"><i class="bi bi-check-lg"></i> Completar Orden</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Cantidad Real (Buenas)</label>
                        <input type="number" name="cantidad_real_buenas" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Merma (kg) <span class="text-muted">opcional</span></label>
                        <input type="number" step="0.01" name="merma_kg" class="form-control form-control-sm" placeholder="0.00">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Tipo de Merma</label>
                        <select name="merma_tipo" class="form-select form-select-sm">
                            <option value="general">General</option>
                            <option value="arranque">Arranque</option>
                            <option value="defecto">Pieza defectuosa</option>
                            <option value="sobrante">Sobrante</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Destino Merma</label>
                        <select name="merma_destino" class="form-select form-select-sm">
                            <option value="reciclaje">Reciclaje</option>
                            <option value="desperdicio">Desperdicio</option>
                            <option value="reproceso">Reproceso</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Observaciones</label>
                        <textarea name="observaciones" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check-lg"></i> Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

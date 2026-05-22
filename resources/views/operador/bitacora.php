<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-journal-text"></i> Bitácora de Turno</h1>
    <div class="d-flex gap-2 align-items-center">
        <form method="get" class="d-flex gap-1">
            <input type="date" name="fecha" class="form-control form-control-sm" style="width:auto;" value="<?= safe_string($fecha) ?>" onchange="this.form.submit()">
        </form>
        <span class="badge bg-<?= $turno_actual === 'matutino' ? 'warning' : ($turno_actual === 'vespertino' ? 'info' : 'dark') ?> text-dark"><?= ucfirst($turno_actual) ?></span>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-pencil"></i> Nueva Nota</small></div>
            <div class="card-body">
                <form method="POST" action="<?= url('bitacora') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-2">
                        <label class="form-label small">Máquina (opcional)</label>
                        <select name="maquina_id" class="form-select form-select-sm">
                            <option value="">General</option>
                            <?php foreach ($maquinas as $m): ?>
                            <option value="<?= $m['id_maquina'] ?>"><?= safe_string($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Nota</label>
                        <textarea name="nota" class="form-control form-control-sm" rows="3" required placeholder="Describe novedades, incidencias, observaciones del turno..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm w-100"><i class="bi bi-save"></i> Guardar Nota</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-1 px-2 d-flex justify-content-between">
                <small><i class="bi bi-list"></i> Notas del <?= format_date($fecha) ?></small>
                <small><?= count($notas) ?> nota(s)</small>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notas)): ?>
                <div class="text-center text-muted py-3">Sin notas para esta fecha.</div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notas as $n): ?>
                    <div class="list-group-item py-1 px-2" style="font-size:0.8rem;">
                        <div class="d-flex justify-content-between">
                            <small class="text-muted"><i class="bi bi-clock"></i> <?= format_datetime($n['created_at']) ?></small>
                            <small>
                                <span class="badge bg-<?= $n['turno'] === 'matutino' ? 'warning' : ($n['turno'] === 'vespertino' ? 'info' : 'dark') ?> text-dark"><?= ucfirst($n['turno']) ?></span>
                                <?php if ($n['maquina_nombre']): ?>
                                <span class="badge bg-secondary"><?= safe_string($n['maquina_nombre']) ?></span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <div class="mt-1"><?= nl2br(safe_string($n['nota'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

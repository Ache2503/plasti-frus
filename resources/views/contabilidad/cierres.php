<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-lock"></i> Cierres Contables</h1>
</div>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-calendar-plus"></i> Cerrar Periodo</div>
            <div class="card-body">
                <form method="POST" action="<?= url('contabilidad/cierres/cerrar') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Año</label>
                        <select name="anio" class="form-select">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y === date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mes</label>
                        <select name="mes" class="form-select">
                            <?php foreach ($meses_abiertos as $m): ?>
                            <option value="<?= $m ?>"><?= str_pad((string) $m, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($meses_abiertos)): ?>
                        <small class="text-muted">Todos los meses de <?= $anio ?> están cerrados</small>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2" placeholder="Opcional"></textarea>
                    </div>
                    <button type="submit" class="btn btn-dark w-100" <?= empty($meses_abiertos) ? 'disabled' : '' ?>>
                        <i class="bi bi-lock"></i> Cerrar Periodo
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-clock-history"></i> Periodos Cerrados</div>
            <div class="card-body p-0">
                <?php if (empty($cierres)): ?>
                <div class="text-center py-4 text-muted">No hay cierres registrados</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Periodo</th><th>Tipo</th><th>Cerrado por</th><th>Fecha</th><th>Observaciones</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($cierres as $c): ?>
                            <tr>
                                <td><strong><?= str_pad((string) $c['mes'], 2, '0', STR_PAD_LEFT) ?>/<?= $c['anio'] ?></strong></td>
                                <td><span class="badge bg-info"><?= $c['tipo'] ?></span></td>
                                <td><?= safe_string($c['nombre_usuario'] ?? 'N/A') ?></td>
                                <td><?= format_datetime($c['fecha_cierre']) ?></td>
                                <td><small><?= safe_string($c['observaciones'] ?? '') ?></small></td>
                                <td>
                                    <?php if (contabilidad_permiso('cerrar_periodo')): ?>
                                    <form method="POST" action="<?= url('contabilidad/cierres/reabrir/' . $c['id']) ?>" class="d-inline" onsubmit="return confirm('¿Reabrir este periodo?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Reabrir"><i class="bi bi-unlock"></i></button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

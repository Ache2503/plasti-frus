<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-file-earmark-text"></i> Pólizas</h1>
    <div class="d-flex gap-2">
        <?php if (contabilidad_permiso('crear')): ?>
        <a href="<?= url('contabilidad/polizas/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Póliza</a>
        <?php endif; ?>
    </div>
</div>

<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= safe_string($filters['fecha_desde'] ?? '') ?>" placeholder="Desde">
    </div>
    <div class="col-auto">
        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= safe_string($filters['fecha_hasta'] ?? '') ?>" placeholder="Hasta">
    </div>
    <div class="col-auto">
        <select name="tipo" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="ingreso" <?= ($filters['tipo'] ?? '') === 'ingreso' ? 'selected' : '' ?>>Ingreso</option>
            <option value="egreso" <?= ($filters['tipo'] ?? '') === 'egreso' ? 'selected' : '' ?>>Egreso</option>
            <option value="diario" <?= ($filters['tipo'] ?? '') === 'diario' ? 'selected' : '' ?>>Diario</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search"></i> Filtrar</button>
        <a href="<?= url('contabilidad/polizas') ?>" class="btn btn-sm btn-outline-secondary">Limpiar</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 datatable">
                <thead>
                    <tr><th>Folio</th><th>Fecha</th><th>Tipo</th><th>Concepto</th><th>Cargos</th><th>Abonos</th><th>Partidas</th><th>Estatus</th><th>Usuario</th><th class="no-sort">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($polizas as $p): ?>
                    <tr class="<?= $p['estatus'] === 'cancelado' ? 'text-muted' : '' ?>">
                        <td><a href="<?= url('contabilidad/polizas/show/' . $p['id_poliza']) ?>"><?= safe_string($p['folio']) ?></a></td>
                        <td><?= format_date($p['fecha']) ?></td>
                        <td><span class="badge bg-<?= $p['tipo'] === 'ingreso' ? 'success' : ($p['tipo'] === 'egreso' ? 'danger' : 'info') ?>"><?= safe_string($p['tipo']) ?></span></td>
                        <td><?= safe_string(truncate($p['concepto'], 60)) ?></td>
                        <td class="text-end"><?= format_money($p['total_cargos']) ?></td>
                        <td class="text-end"><?= format_money($p['total_abonos']) ?></td>
                        <td class="text-center"><?= $p['num_partidas'] ?></td>
                        <td>
                            <?php if ($p['estatus'] === 'cancelado'): ?>
                            <span class="badge bg-danger">Cancelado</span>
                            <?php else: ?>
                            <span class="badge bg-success">Activo</span>
                            <?php endif; ?>
                        </td>
                        <td><?= safe_string($p['nombre_usuario'] ?? '') ?></td>
                        <td>
                            <a href="<?= url('contabilidad/polizas/show/' . $p['id_poliza']) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($polizas)): ?>
                    <tr><td colspan="10" class="text-center text-muted">Sin pólizas registradas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

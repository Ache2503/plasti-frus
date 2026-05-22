<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-calculator"></i> Contabilidad</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= url('contabilidad/plan-cuentas') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-journal-text"></i> Plan de Cuentas</a>
        <a href="<?= url('contabilidad/polizas') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-file-earmark-text"></i> Pólizas</a>
        <?php if (contabilidad_permiso('crear')): ?>
        <a href="<?= url('contabilidad/polizas/create') ?>" class="btn btn-sm btn-dark"><i class="bi bi-plus-lg"></i> Nueva Póliza</a>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= $total_cuentas ?></div>
                <div class="stat-label"><i class="bi bi-book"></i> Cuentas</div>
            </div>
            <i class="bi bi-book stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= $total_polizas ?></div>
                <div class="stat-label"><i class="bi bi-file-earmark-text"></i> Total Pólizas</div>
            </div>
            <i class="bi bi-file-earmark-text stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-warning">
            <div class="stat-content">
                <div class="stat-number"><?= $total_polizas_mes ?></div>
                <div class="stat-label"><i class="bi bi-calendar-month"></i> Pólizas del Mes</div>
            </div>
            <i class="bi bi-calendar-month stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <?php $balanceMes = $cargos_mes - $abonos_mes; ?>
        <div class="stat-card card-<?= $balanceMes >= 0 ? 'info' : 'danger' ?>">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($cargos_mes) ?></div>
                <div class="stat-label"><i class="bi bi-arrow-up"></i> Cargos del Mes</div>
            </div>
            <i class="bi bi-arrow-up stat-icon"></i>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card card-info">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($abonos_mes) ?></div>
                <div class="stat-label"><i class="bi bi-arrow-down"></i> Abonos del Mes</div>
            </div>
            <i class="bi bi-arrow-down stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-<?= ($periodo_actual['cerrado'] ?? 0) ? 'secondary' : 'success' ?>">
            <div class="stat-content">
                <div class="stat-number"><?= ($periodo_actual['cerrado'] ?? 0) ? 'Cerrado' : 'Abierto' ?></div>
                <div class="stat-label"><i class="bi bi-calendar-check"></i> <?= str_pad($mes_actual, 2, '0', STR_PAD_LEFT) ?>/<?= $anio_actual ?></div>
            </div>
            <i class="bi bi-calendar-check stat-icon"></i>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><i class="bi bi-clock-history"></i> Últimas Pólizas</span>
                <a href="<?= url('contabilidad/polizas') ?>" class="btn btn-sm btn-outline-light">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Folio</th><th>Fecha</th><th>Tipo</th><th>Concepto</th><th>Usuario</th></tr></thead>
                        <tbody>
                            <?php foreach ($ultimas_polizas as $p): ?>
                            <tr>
                                <td><a href="<?= url('contabilidad/polizas/show/' . $p['id_poliza']) ?>"><?= safe_string($p['folio']) ?></a></td>
                                <td><?= format_date($p['fecha']) ?></td>
                                <td><span class="badge bg-<?= $p['tipo'] === 'ingreso' ? 'success' : ($p['tipo'] === 'egreso' ? 'danger' : 'info') ?>"><?= safe_string($p['tipo']) ?></span></td>
                                <td><?= safe_string(truncate($p['concepto'], 60)) ?></td>
                                <td><?= safe_string($p['nombre_usuario'] ?? '') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ultimas_polizas)): ?>
                            <tr><td colspan="5" class="text-center text-muted">Sin pólizas registradas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white"><i class="bi bi-grid-3x3-gap-fill"></i> Reportes</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-outline-dark"><i class="bi bi-list-check"></i> Balanza de Comprobación</a>
                    <a href="<?= url('contabilidad/estado-resultados') ?>" class="btn btn-outline-dark"><i class="bi bi-bar-chart"></i> Estado de Resultados</a>
                    <a href="<?= url('contabilidad/balance-general') ?>" class="btn btn-outline-dark"><i class="bi bi-file-earmark-text"></i> Balance General</a>
                    <a href="<?= url('contabilidad/libro-diario') ?>" class="btn btn-outline-dark"><i class="bi bi-journal"></i> Libro Diario</a>
                    <a href="<?= url('contabilidad/periodos') ?>" class="btn btn-outline-dark"><i class="bi bi-calendar-check"></i> Periodos</a>
                </div>
            </div>
        </div>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-dark text-white"><i class="bi bi-info-circle"></i> Resumen del Mes</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <span>Cargos:</span>
                    <span class="fw-bold"><?= format_money($cargos_mes) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Abonos:</span>
                    <span class="fw-bold"><?= format_money($abonos_mes) ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span>Diferencia:</span>
                    <span class="fw-bold <?= abs($cargos_mes - $abonos_mes) < 0.01 ? 'text-success' : 'text-danger' ?>">
                        <?= format_money(abs($cargos_mes - $abonos_mes)) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

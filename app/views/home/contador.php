<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-calculator"></i> Dashboard Contabilidad</h1>
    <span class="badge bg-info text-dark fs-6"><?= safe_string($rol_nombre) ?></span>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="<?= url('contabilidad/plan-cuentas') ?>" class="text-decoration-none">
            <div class="stat-card card-primary">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_cuentas ?></div>
                    <div class="stat-label"><i class="bi bi-book"></i> Cuentas</div>
                </div>
                <i class="bi bi-book stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= url('contabilidad/polizas') ?>" class="text-decoration-none">
            <div class="stat-card card-success">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_polizas ?></div>
                    <div class="stat-label"><i class="bi bi-file-earmark-text"></i> Pólizas</div>
                </div>
                <i class="bi bi-file-earmark-text stat-icon"></i>
            </div>
        </a>
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
        <a href="<?= url('facturas/solicitudes') ?>" class="text-decoration-none">
            <div class="stat-card card-danger">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_facturas_pendientes ?></div>
                    <div class="stat-label"><i class="bi bi-file-text"></i> Facturas Pendientes</div>
                </div>
                <i class="bi bi-file-text stat-icon"></i>
            </div>
        </a>
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
                        <thead><tr><th>Folio</th><th>Fecha</th><th>Tipo</th><th>Concepto</th></tr></thead>
                        <tbody>
                            <?php foreach ($ultimas_polizas as $p): ?>
                            <tr>
                                <td><a href="<?= url('contabilidad/polizas/show/' . $p['id_poliza']) ?>"><?= safe_string($p['folio']) ?></a></td>
                                <td><?= format_date($p['fecha']) ?></td>
                                <td><span class="badge bg-<?= $p['tipo'] === 'ingreso' ? 'success' : ($p['tipo'] === 'egreso' ? 'danger' : 'info') ?>"><?= safe_string($p['tipo']) ?></span></td>
                                <td><?= safe_string(truncate($p['concepto'], 60)) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ultimas_polizas)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Sin pólizas registradas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white"><i class="bi bi-grid-3x3-gap-fill"></i> Acceso Rápido</div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= url('contabilidad') ?>" class="btn btn-outline-dark"><i class="bi bi-calculator"></i> Contabilidad</a>
                    <a href="<?= url('contabilidad/polizas/create') ?>" class="btn btn-outline-dark"><i class="bi bi-plus-lg"></i> Nueva Póliza</a>
                    <a href="<?= url('contabilidad/balanza') ?>" class="btn btn-outline-dark"><i class="bi bi-list-check"></i> Balanza</a>
                    <a href="<?= url('contabilidad/balance-general') ?>" class="btn btn-outline-dark"><i class="bi bi-file-earmark-text"></i> Balance General</a>
                    <a href="<?= url('contabilidad/estado-resultados') ?>" class="btn btn-outline-dark"><i class="bi bi-bar-chart"></i> Estado Resultados</a>
                    <a href="<?= url('facturas/solicitudes') ?>" class="btn btn-outline-dark"><i class="bi bi-file-text"></i> Facturas</a>
                </div>
            </div>
        </div>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-dark text-white"><i class="bi bi-calendar-check"></i> Periodo Actual</div>
            <div class="card-body">
                <?php if ($periodo_actual): ?>
                <div class="d-flex justify-content-between mb-1">
                    <span>Periodo:</span>
                    <span><?= str_pad($periodo_actual['mes'], 2, '0', STR_PAD_LEFT) ?>/<?= $periodo_actual['anio'] ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Estatus:</span>
                    <?php if ($periodo_actual['cerrado']): ?>
                    <span class="badge bg-secondary">Cerrado</span>
                    <?php else: ?>
                    <span class="badge bg-success">Abierto</span>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Sin periodo definido</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

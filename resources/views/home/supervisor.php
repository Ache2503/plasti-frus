<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard Supervisor</h1>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-warning text-dark fs-6"><?= safe_string($rol_nombre) ?></span>
        <?php if (!empty($notificaciones)): ?>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-warning position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <?php if ($notificaciones_no_leidas > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $notificaciones_no_leidas ?></span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px;">
                <?php foreach ($notificaciones as $n): ?>
                <li><a class="dropdown-item small <?= $n['leida'] ? '' : 'fw-bold' ?>" href="#">
                    <i class="bi bi-info-circle"></i> <?= safe_string($n['titulo']) ?>
                    <br><small class="text-muted"><?= safe_string($n['mensaje'] ?? '') ?></small>
                </a></li>
                <?php endforeach; ?>
                <?php if (empty($notificaciones)): ?>
                <li><span class="dropdown-item text-muted small">Sin notificaciones</span></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center small" href="<?= url('notificaciones') ?>">Ver todas</a></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="<?= url('materiales') ?>" class="text-decoration-none">
            <div class="stat-card card-primary">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_materiales ?></div>
                    <div class="stat-label"><i class="bi bi-boxes"></i> Materiales</div>
                </div>
                <i class="bi bi-boxes stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= url('productos') ?>" class="text-decoration-none">
            <div class="stat-card card-success">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_productos ?></div>
                    <div class="stat-label"><i class="bi bi-cube"></i> Productos</div>
                </div>
                <i class="bi bi-cube stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= url('ordenes') ?>" class="text-decoration-none">
            <div class="stat-card card-warning">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_ordenes ?></div>
                    <div class="stat-label"><i class="bi bi-clipboard-check"></i> Órdenes</div>
                </div>
                <i class="bi bi-clipboard-check stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= url('maquinas') ?>" class="text-decoration-none">
            <div class="stat-card card-info">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_maquinas ?></div>
                    <div class="stat-label"><i class="bi bi-tools"></i> Máquinas</div>
                </div>
                <i class="bi bi-tools stat-icon"></i>
            </div>
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="<?= url('clientes') ?>" class="text-decoration-none">
            <div class="stat-card card-dark">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_clientes ?></div>
                    <div class="stat-label"><i class="bi bi-people"></i> Clientes</div>
                </div>
                <i class="bi bi-people stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= url('proveedores') ?>" class="text-decoration-none">
            <div class="stat-card card-danger">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_proveedores ?></div>
                    <div class="stat-label"><i class="bi bi-truck"></i> Proveedores</div>
                </div>
                <i class="bi bi-truck stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= url('moldes') ?>" class="text-decoration-none">
            <div class="stat-card card-secondary">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_moldes ?></div>
                    <div class="stat-label"><i class="bi bi-bounding-box-circles"></i> Moldes</div>
                </div>
                <i class="bi bi-bounding-box-circles stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= $total_usuarios ?></div>
                <div class="stat-label"><i class="bi bi-people-fill"></i> Usuarios</div>
            </div>
            <i class="bi bi-people-fill stat-icon"></i>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-bar-chart-line"></i> Producción por Día</div>
            <div class="card-body">
                <canvas id="prodChart" height="80"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white"><i class="bi bi-calendar-day"></i> Hoy — <?= format_date($fecha_hoy) ?></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="fs-4 fw-bold text-primary"><?= count($ordenes_hoy) ?></div>
                            <small class="text-muted">Órdenes</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="fs-4 fw-bold text-success"><?= count($completadas_hoy) ?></div>
                            <small class="text-muted">Completadas</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="fs-4 fw-bold text-warning"><?= count($en_progreso_hoy) ?></div>
                            <small class="text-muted">En Progreso</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center p-2 bg-light rounded">
                            <div class="fs-4 fw-bold text-danger"><?= count($pendientes_hoy) ?></div>
                            <small class="text-muted">Pendientes</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-1">
                    <span>Producido:</span>
                    <span class="fw-bold"><?= number_format($total_producido_hoy) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Planificado:</span>
                    <span class="fw-bold"><?= number_format($total_planificado_hoy) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Merma:</span>
                    <span class="fw-bold text-danger"><?= number_format($merma_hoy, 2) ?> kg</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-exclamation-triangle"></i> Incidencias Activas</span>
                <span class="badge bg-light text-danger"><?= $total_incidencias_activas ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>#</th><th>Producto</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach ($incidencias_activas as $inc): ?>
                            <tr>
                                <td><?= $inc['id_incidencia'] ?></td>
                                <td><?= safe_string($inc['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= format_date($inc['fecha']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($incidencias_activas)): ?>
                            <tr><td colspan="3" class="text-center text-success">Sin incidencias activas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent p-1 text-end">
                    <a href="<?= url('incidencias') ?>" class="btn btn-sm btn-outline-danger">Ver todas</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <span><i class="bi bi-stopwatch"></i> Paros Activos</span>
                <span class="badge bg-dark text-warning"><?= $total_paros_activos ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Máquina</th><th>Motivo</th><th>Desde</th></tr></thead>
                        <tbody>
                            <?php foreach ($paros_activos as $p): ?>
                            <tr>
                                <td><?= safe_string($p['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= safe_string($p['motivo_paro']) ?></td>
                                <td><?= $p['hora_inicio'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($paros_activos)): ?>
                            <tr><td colspan="3" class="text-center text-success">Sin paros activos</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent p-1 text-end">
                    <a href="<?= url('mantenimiento/paros') ?>" class="btn btn-sm btn-outline-warning">Bitácora</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-white"><i class="bi bi-tools"></i> Estado Máquinas</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Máquina</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php foreach ($maquinas_con_estado as $mq): ?>
                            <tr>
                                <td><?= safe_string($mq['nombre']) ?></td>
                                <td>
                                    <?php if (($mq['estado_real'] ?? $mq['estatus']) === 'paro'): ?>
                                    <span class="badge bg-danger" title="<?= safe_string($mq['motivo_paro'] ?? '') ?>">
                                        <i class="bi bi-exclamation-circle"></i> Paro
                                    </span>
                                    <?php elseif (($mq['estado_real'] ?? $mq['estatus']) === 'mantenimiento'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-wrench"></i> Mantenimiento</span>
                                    <?php else: ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Activo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-clock-history"></i> Últimas Órdenes</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>#</th><th>Producto</th><th>Máquina</th><th>Plan</th><th>Estatus</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($ordenes_recientes, 0, 8) as $o): ?>
                            <tr>
                                <td><?= $o['id_orden_cabe'] ?></td>
                                <td><?= safe_string($o['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= safe_string($o['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= $o['cantidad_planificada'] ?></td>
                                <td>
                                    <?php if ($o['estatus'] === 'completada'): ?>
                                    <span class="badge bg-success">Completada</span>
                                    <?php elseif ($o['estatus'] === 'en_progreso'): ?>
                                    <span class="badge bg-primary">En Progreso</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= format_date($o['fecha']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ordenes_recientes)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Sin registros</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent p-1 text-end">
                    <a href="<?= url('ordenes') ?>" class="btn btn-sm btn-outline-dark">Todas las órdenes</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Materiales con Stock Bajo</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Material</th><th>Stock</th><th>Reorden</th><th>Prov.</th></tr></thead>
                        <tbody>
                            <?php foreach ($materiales_bajos as $m): ?>
                            <tr class="table-danger">
                                <td><?= safe_string($m['nombre']) ?></td>
                                <td><?= number_format($m['stock_actual_kg'], 1) ?> kg</td>
                                <td><?= number_format($m['punto_reorden_kg'], 1) ?> kg</td>
                                <td><?= safe_string($m['proveedor'] ?? 'N/A') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($materiales_bajos)): ?>
                            <tr><td colspan="4" class="text-center text-success">Sin alertas</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-transparent p-1 text-end">
                    <a href="<?= url('materiales') ?>" class="btn btn-sm btn-outline-warning">Ver materiales</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white"><i class="bi bi-grid-3x3-gap-fill"></i> Accesos Rápidos</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4"><a href="<?= url('reportes/kpi') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-graph-up"></i> KPIs</a></div>
                    <div class="col-md-4"><a href="<?= url('reportes/produccion') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a></div>
                    <div class="col-md-4"><a href="<?= url('calidad/inspecciones') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-clipboard-check"></i> Calidad</a></div>
                    <div class="col-md-4"><a href="<?= url('mantenimiento') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-tools"></i> Mantenimiento</a></div>
                    <div class="col-md-4"><a href="<?= url('admin/horarios') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-clock"></i> Horarios</a></div>
                    <div class="col-md-4"><a href="<?= url('comisiones') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-cash-stack"></i> Comisiones</a></div>
                    <div class="col-md-4"><a href="<?= url('incidencias') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-exclamation-triangle"></i> Incidencias</a></div>
                    <div class="col-md-4"><a href="<?= url('facturas/solicitudes') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-file-text"></i> Facturas</a></div>
                    <div class="col-md-4"><a href="<?= url('usuarios') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-people-fill"></i> Usuarios</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-people"></i> Operadores</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Nombre</th><th>Acción</th></tr></thead>
                        <tbody>
                            <?php foreach ($operadores_activos as $op): ?>
                            <tr>
                                <td><?= safe_string(($op['nombre'] ?? '') . ' ' . ($op['apellido_paterno'] ?? '')) ?></td>
                                <td>
                                    <a href="<?= url('admin/horarios') ?>" class="btn btn-sm btn-outline-info" title="Ver horario"><i class="bi bi-clock"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($operadores_activos)): ?>
                            <tr><td colspan="2" class="text-center text-muted">Sin operadores</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ordenes = <?= json_encode(array_map(function($o) {
        return ['label' => '#' . $o['id_orden_cabe'], 'plan' => (int)($o['cantidad_planificada'] ?? 0)];
    }, array_slice($ordenes_recientes, 0, 10))) ?>;

    if (ordenes.length > 0 && document.getElementById('prodChart')) {
        new Chart(document.getElementById('prodChart'), {
            type: 'bar',
            data: {
                labels: ordenes.map(d => d.label),
                datasets: [{
                    label: 'Producción Planificada',
                    data: ordenes.map(d => d.plan),
                    backgroundColor: '#667eea',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } } }
            }
        });
    }
});
</script>

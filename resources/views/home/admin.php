<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2 me-2"></i> Dashboard Administrador</h1>
    <div>
        <span class="badge bg-dark fs-6 px-3 py-2"><?= safe_string($rol_nombre) ?></span>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3 col-6">
        <a href="<?= url('materiales') ?>" class="text-decoration-none">
            <div class="stat-card card-primary">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_materiales ?></div>
                    <div class="stat-label"><i class="bi bi-boxes me-1"></i> Materiales</div>
                </div>
                <i class="bi bi-boxes stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="<?= url('productos') ?>" class="text-decoration-none">
            <div class="stat-card card-success">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_productos ?></div>
                    <div class="stat-label"><i class="bi bi-cube me-1"></i> Productos</div>
                </div>
                <i class="bi bi-cube stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="<?= url('ordenes') ?>" class="text-decoration-none">
            <div class="stat-card card-warning">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_ordenes ?></div>
                    <div class="stat-label"><i class="bi bi-clipboard-check me-1"></i> Órdenes</div>
                </div>
                <i class="bi bi-clipboard-check stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="<?= url('maquinas') ?>" class="text-decoration-none">
            <div class="stat-card card-info">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_maquinas ?></div>
                    <div class="stat-label"><i class="bi bi-tools me-1"></i> Máquinas</div>
                </div>
                <i class="bi bi-tools stat-icon"></i>
            </div>
        </a>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3 col-6">
        <a href="<?= url('clientes') ?>" class="text-decoration-none">
            <div class="stat-card card-dark">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_clientes ?></div>
                    <div class="stat-label"><i class="bi bi-people me-1"></i> Clientes</div>
                </div>
                <i class="bi bi-people stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="<?= url('proveedores') ?>" class="text-decoration-none">
            <div class="stat-card card-danger">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_proveedores ?></div>
                    <div class="stat-label"><i class="bi bi-truck me-1"></i> Proveedores</div>
                </div>
                <i class="bi bi-truck stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <a href="<?= url('ventas') ?>" class="text-decoration-none">
            <div class="stat-card card-secondary">
                <div class="stat-content">
                    <div class="stat-number"><?= $total_ventas ?? 0 ?></div>
                    <div class="stat-label"><i class="bi bi-cash-coin me-1"></i> Ventas</div>
                </div>
                <i class="bi bi-cash-coin stat-icon"></i>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card card-primary">
            <div class="stat-content">
                <div class="stat-number"><?= $total_usuarios ?></div>
                <div class="stat-label"><i class="bi bi-people-fill me-1"></i> Usuarios</div>
            </div>
            <i class="bi bi-people-fill stat-icon"></i>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart-line"></i> Producción por Día
            </div>
            <div class="card-body">
                <canvas id="prodChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
                <i class="bi bi-clock-history"></i> Últimas Órdenes
            </div>
            <div class="card-body p-0">
                <?php if (!empty($ordenes_recientes)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>#</th><th>Producto</th><th>Máquina</th><th>Plan</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($ordenes_recientes, 0, 5) as $o): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark">#<?= $o['id_orden_cabe'] ?></span></td>
                                <td class="fw-semibold"><?= safe_string($o['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= safe_string($o['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= number_format($o['cantidad_planificada']) ?></td>
                                <td><?= format_date($o['fecha']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h5>Sin órdenes recientes</h5>
                    <p>Aún no se han registrado órdenes de producción en el sistema.</p>
                    <a href="<?= url('ordenes/create') ?>" class="btn btn-dark btn-sm mt-2">
                        <i class="bi bi-plus-circle"></i> Crear Primera Orden
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-warning text-dark d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle"></i> Materiales con Stock Bajo
            </div>
            <div class="card-body p-0">
                <?php if (!empty($materiales_bajos)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Material</th><th>Stock</th><th>Reorden</th><th>Prov.</th></tr></thead>
                        <tbody>
                            <?php foreach ($materiales_bajos as $m): ?>
                            <tr class="table-danger">
                                <td class="fw-semibold"><?= safe_string($m['nombre']) ?></td>
                                <td><span class="badge bg-danger"><?= number_format($m['stock_actual_kg'], 1) ?> kg</span></td>
                                <td><?= number_format($m['punto_reorden_kg'], 1) ?> kg</td>
                                <td><?= safe_string($m['proveedor'] ?? 'N/A') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-check-circle text-success"></i>
                    <h5>Sin alertas de stock</h5>
                    <p>Todos los materiales cuentan con inventario suficiente.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-secondary text-white d-flex align-items-center gap-2">
        <i class="bi bi-grid-3x3-gap-fill"></i> Accesos Rápidos
    </div>
    <div class="card-body quick-action">
        <div class="row g-2">
            <div class="col-md-3 col-6"><a href="<?= url('reportes/kpi') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-graph-up"></i> KPIs</a></div>
            <div class="col-md-3 col-6"><a href="<?= url('reportes/produccion') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a></div>
            <div class="col-md-3 col-6"><a href="<?= url('calidad/inspecciones') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-clipboard-check"></i> Calidad</a></div>
            <div class="col-md-3 col-6"><a href="<?= url('mantenimiento') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-tools"></i> Mantenimiento</a></div>
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
                    backgroundColor: 'rgba(102, 126, 234, .7)',
                    borderColor: '#667eea',
                    borderWidth: 1,
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

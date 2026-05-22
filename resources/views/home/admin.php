<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard Administrador</h1>
    <span class="badge bg-dark fs-6"><?= safe_string($rol_nombre) ?></span>
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
        <a href="<?= url('ventas') ?>" class="text-decoration-none">
            <div class="stat-card card-secondary">
                <div class="stat-content">
                    <div class="stat-label"><i class="bi bi-cash-coin"></i> Ventas</div>
                </div>
                <i class="bi bi-cash-coin stat-icon"></i>
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
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-bar-chart-line"></i> Producción por Día</div>
            <div class="card-body">
                <canvas id="prodChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-clock-history"></i> Últimas Órdenes</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>#</th><th>Producto</th><th>Máquina</th><th>Plan</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($ordenes_recientes, 0, 5) as $o): ?>
                            <tr>
                                <td><?= $o['id_orden_cabe'] ?></td>
                                <td><?= safe_string($o['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= safe_string($o['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= $o['cantidad_planificada'] ?></td>
                                <td><?= format_date($o['fecha']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ordenes_recientes)): ?>
                            <tr><td colspan="5" class="text-center text-muted">Sin registros</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Materiales con Stock Bajo</div>
            <div class="card-body p-0">
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
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-secondary text-white"><i class="bi bi-grid-3x3-gap-fill"></i> Accesos Rápidos</div>
    <div class="card-body quick-action">
        <div class="row">
            <div class="col-md-3 mb-2"><a href="<?= url('reportes/kpi') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-graph-up"></i> KPIs</a></div>
            <div class="col-md-3 mb-2"><a href="<?= url('reportes/produccion') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a></div>
            <div class="col-md-3 mb-2"><a href="<?= url('calidad/inspecciones') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-clipboard-check"></i> Calidad</a></div>
            <div class="col-md-3 mb-2"><a href="<?= url('mantenimiento') ?>" class="btn btn-outline-dark w-100"><i class="bi bi-tools"></i> Mantenimiento</a></div>
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

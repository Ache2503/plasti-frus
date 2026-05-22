<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Dashboard Supervisor</h1>
    <span class="badge bg-warning text-dark fs-6"><?= safe_string($rol_nombre) ?></span>
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
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-clock-history"></i> Últimas Órdenes</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>#</th><th>Producto</th><th>Cant. Plan</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach (array_slice($ordenes_recientes, 0, 8) as $orden): ?>
                            <tr>
                                <td><?= $orden['id_orden_cabe'] ?></td>
                                <td><?= safe_string($orden['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= $orden['cantidad_planificada'] ?></td>
                                <td><?= format_date($orden['fecha']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ordenes_recientes)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Sin registros</td></tr>
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
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Material</th><th>Stock Actual</th><th>Punto Reorden</th><th>Proveedor</th></tr></thead>
                        <tbody>
                            <?php foreach ($materiales_bajos as $mat): ?>
                            <tr class="table-danger">
                                <td><?= safe_string($mat['nombre']) ?></td>
                                <td><?= number_format($mat['stock_actual_kg'], 2) ?> kg</td>
                                <td><?= number_format($mat['punto_reorden_kg'], 2) ?> kg</td>
                                <td><?= safe_string($mat['proveedor'] ?? 'N/A') ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($materiales_bajos)): ?>
                            <tr><td colspan="4" class="text-center text-success">Sin alertas de stock</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

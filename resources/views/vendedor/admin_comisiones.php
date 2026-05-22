<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cash-stack"></i> Gestión de Comisiones</h1>
</div>

<div class="row mb-4">
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="stat-card card-warning">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($total_pendiente) ?></div>
                <div class="stat-label"><i class="bi bi-hourglass-split"></i> Pendiente Global</div>
            </div>
            <i class="bi bi-hourglass-split stat-icon"></i>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3 mb-3">
        <div class="stat-card card-success">
            <div class="stat-content">
                <div class="stat-number"><?= format_money($total_pagado) ?></div>
                <div class="stat-label"><i class="bi bi-check-circle"></i> Pagado Global</div>
            </div>
            <i class="bi bi-check-circle stat-icon"></i>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="vendedor" class="form-select form-select-sm">
                    <option value="">Todos los vendedores</option>
                    <?php foreach ($vendedores as $v): ?>
                    <option value="<?= $v['id_usuario'] ?>" <?= $filtro_vendedor == $v['id_usuario'] ? 'selected' : '' ?>>
                        <?= safe_string($v['nombre'] . ' ' . ($v['apellido_paterno'] ?? '')) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="estatus" class="form-select form-select-sm">
                    <option value="">Todos los estatus</option>
                    <option value="pendiente" <?= $filtro_estatus === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="pagada" <?= $filtro_estatus === 'pagada' ? 'selected' : '' ?>>Pagada</option>
                    <option value="cancelada" <?= $filtro_estatus === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-funnel"></i> Filtrar</button>
                <a href="<?= url('comisiones') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="tablaComisiones">
                <thead><tr><th>#</th><th>Vendedor</th><th>Venta</th><th>Cliente</th><th>%</th><th>Monto</th><th>Estatus</th><th>Fecha Cálculo</th><th>Fecha Pago</th><th class="no-sort">Acción</th></tr></thead>
                <tbody>
                    <?php foreach ($comisiones as $c): ?>
                    <tr>
                        <td><?= $c['id_comision'] ?></td>
                        <td><?= safe_string($c['vendedor_nombre'] ?? $c['nombre_usuario'] ?? 'N/A') ?></td>
                        <td><?= safe_string($c['venta_folio'] ?? '#'.$c['id_venta']) ?></td>
                        <td><?= safe_string($c['cliente'] ?? 'N/A') ?></td>
                        <td><?= $c['porcentaje_comision'] ?>%</td>
                        <td><?= format_money($c['monto_comision']) ?></td>
                        <td>
                            <span class="badge bg-<?= $c['estatus'] === 'pagada' ? 'success' : ($c['estatus'] === 'pendiente' ? 'warning' : 'secondary') ?>">
                                <?= safe_string($c['estatus']) ?>
                            </span>
                        </td>
                        <td><?= format_date($c['fecha_calculo']) ?></td>
                        <td><?= $c['fecha_pago'] ? format_date($c['fecha_pago']) : '—' ?></td>
                        <td>
                            <?php if ($c['estatus'] === 'pendiente'): ?>
                            <form method="POST" action="<?= url('comisiones/pagar/' . $c['id_comision']) ?>" style="display:inline" onsubmit="return confirm('¿Marcar esta comisión como pagada?')">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i> Pagar</button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($comisiones)): ?>
                    <tr><td colspan="10" class="text-center text-muted">Sin comisiones registradas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

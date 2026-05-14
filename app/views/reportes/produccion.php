<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-file-earmark-bar-graph"></i> Reporte de Producción</h1>
</div>

<?php $filterAction = 'reportes/produccion'; include __DIR__ . '/../partials/filter_bar.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-clipboard-check"></i> Órdenes de Producción <?= !empty($filters) ? '<span class="badge bg-light text-dark ms-2">Filtrado</span>' : '' ?></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr><th>#</th><th>Producto</th><th>Máquina</th><th>Molde</th><th>Cant. Plan</th><th>Cant. Real</th><th>Fecha</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produccion as $p): ?>
                            <tr>
                                <td><?= $p['id_orden_cabe'] ?></td>
                                <td><?= safe_string($p['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= safe_string($p['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= safe_string($p['molde_nombre'] ?? 'N/A') ?></td>
                                <td><?= $p['cantidad_planificada'] ?></td>
                                <td><?= $p['cantidad_real_buenas'] ?? '-' ?></td>
                                <td><?= format_date($p['fecha']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($produccion)): ?>
                            <tr><td colspan="7" class="text-center text-muted">Sin datos</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark"><i class="bi bi-cup-straw"></i> Consumo de Materiales</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Material</th><th>Cantidad</th><th>Fecha</th><th>Operador</th></tr></thead>
                    <tbody>
                        <?php foreach ($consumos as $c): ?>
                        <tr><td><?= safe_string($c['material_nombre'] ?? 'N/A') ?></td><td><?= number_format($c['cantidad_consumida'], 2) ?> kg</td><td><?= format_date($c['fecha']) ?></td><td><?= safe_string($c['operador']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($consumos)): ?>
                        <tr><td colspan="4" class="text-muted text-center">Sin consumos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white"><i class="bi bi-exclamation-triangle"></i> Incidencias</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Fecha</th><th>Descripción</th><th>Impacto</th><th>Estatus</th></tr></thead>
                    <tbody>
                        <?php foreach ($incidencias as $i): ?>
                        <tr><td><?= format_date($i['fecha']) ?></td><td><?= safe_string(truncate($i['descripcion'], 50)) ?></td><td><?= safe_string($i['impacto']) ?></td><td><?= safe_string($i['estatus']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($incidencias)): ?>
                        <tr><td colspan="4" class="text-muted text-center">Sin incidencias</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white"><i class="bi bi-recycle"></i> Scrap / Reciclado</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Producto</th><th>Tipo</th><th>Cantidad (kg)</th><th>Destino</th></tr></thead>
                    <tbody>
                        <?php foreach ($scrap as $s): ?>
                        <tr><td><?= safe_string($s['producto_nombre'] ?? 'N/A') ?></td><td><?= safe_string($s['tipo_scrap']) ?></td><td><?= number_format($s['cantidad_kg'], 2) ?></td><td><?= safe_string($s['destino_reciclado']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($scrap)): ?>
                        <tr><td colspan="4" class="text-muted text-center">Sin scrap registrado</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

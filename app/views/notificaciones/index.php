<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-bell"></i> Notificaciones</h1>
</div>
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Materiales con Stock Bajo</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Material</th><th>Stock</th><th>Reorden</th></tr></thead>
                    <tbody>
                        <?php foreach ($materiales_bajos as $m): ?>
                        <tr class="table-danger"><td><?= safe_string($m['nombre']) ?></td><td><?= number_format($m['stock_actual_kg'], 1) ?> kg</td><td><?= number_format($m['punto_reorden_kg'], 1) ?> kg</td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($materiales_bajos)): ?>
                        <tr><td colspan="3" class="text-center text-success">Sin alertas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white"><i class="bi bi-calendar-check"></i> Mantenimientos Programados</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Máquina</th><th>Fecha</th><th>Tipo</th></tr></thead>
                    <tbody>
                        <?php foreach ($mantenimientos_pendientes as $m): ?>
                        <tr><td><?= safe_string($m['maquina_nombre'] ?? 'N/A') ?></td><td><?= format_date($m['fecha_programada']) ?></td><td><?= safe_string($m['tipo_mantenimiento']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($mantenimientos_pendientes)): ?>
                        <tr><td colspan="3" class="text-center text-success">Sin pendientes</td></tr>
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
            <div class="card-header bg-danger text-white"><i class="bi bi-x-circle"></i> Incidencias Abiertas</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Producto</th><th>Impacto</th></tr></thead>
                    <tbody>
                        <?php foreach ($incidencias_abiertas as $inc): ?>
                        <tr><td><?= $inc['id_incidencia'] ?></td><td><?= safe_string($inc['producto_nombre'] ?? 'N/A') ?></td><td><span class="badge bg-<?= $inc['impacto'] === 'alto' ? 'danger' : 'warning' ?>"><?= safe_string($inc['impacto']) ?></span></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($incidencias_abiertas)): ?>
                        <tr><td colspan="3" class="text-center text-success">Sin incidencias abiertas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white"><i class="bi bi-graph-up"></i> Resumen Rápido</div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4"><h3 class="text-primary"><?= $total_ordenes_hoy ?></h3><small>Órdenes Hoy</small></div>
                    <div class="col-4"><h3 class="text-success"><?= $total_maquinas_activas ?></h3><small>Máq. Activas</small></div>
                    <div class="col-4"><h3 class="text-warning"><?= count($materiales_bajos) ?></h3><small>Alertas Stock</small></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($notificaciones_supervisor)): ?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell"></i> Notificaciones del Supervisor</span>
                <?php if (($notificaciones_supervisor_no_leidas ?? 0) > 0): ?>
                <form method="post" action="<?= url('notificaciones-supervisor/marcar-leidas') ?>" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-sm btn-light">Marcar todas leídas</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Título</th><th>Mensaje</th><th>Fecha</th><th>Leída</th></tr></thead>
                        <tbody>
                            <?php foreach ($notificaciones_supervisor as $n): ?>
                            <tr class="<?= $n['leida'] ? '' : 'fw-bold' ?>">
                                <td><?= safe_string($n['titulo']) ?></td>
                                <td><?= safe_string($n['mensaje'] ?? '') ?></td>
                                <td><?= format_datetime($n['created_at']) ?></td>
                                <td><?= $n['leida'] ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-warning text-dark">No</span>' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($notificaciones_supervisor)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Sin notificaciones</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

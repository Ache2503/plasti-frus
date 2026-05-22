<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-speedometer2"></i> Panel de Operador</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="POST" action="<?= url('set-turno') ?>" class="d-flex align-items-center gap-1" style="font-size:0.8rem;">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <select name="turno" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="auto" <?= !isset($_SESSION['operador_turno_override']) ? 'selected' : '' ?>>Auto</option>
                <option value="matutino" <?= ($_SESSION['operador_turno_override'] ?? '') === 'matutino' ? 'selected' : '' ?>>Matutino</option>
                <option value="vespertino" <?= ($_SESSION['operador_turno_override'] ?? '') === 'vespertino' ? 'selected' : '' ?>>Vespertino</option>
                <option value="nocturno" <?= ($_SESSION['operador_turno_override'] ?? '') === 'nocturno' ? 'selected' : '' ?>>Nocturno</option>
            </select>
        </form>
        <?php if ($notificaciones_op_no_leidas > 0): ?>
        <span class="badge bg-danger rounded-pill"><i class="bi bi-bell-fill"></i> <?= $notificaciones_op_no_leidas ?></span>
        <?php endif; ?>
        <span class="badge bg-info fs-6"><?= safe_string($rol_nombre) ?></span>
        <span class="badge bg-<?= $turno_actual === 'matutino' ? 'warning' : ($turno_actual === 'vespertino' ? 'info' : 'dark') ?> text-dark"><?= ucfirst($turno_actual) ?></span>
    </div>
</div>

<?php if (!empty($notificaciones_op)): ?>
<div class="alert mb-2 py-1 px-2 <?= $notificaciones_op_no_leidas > 0 ? 'alert-info' : 'alert-light' ?>" role="alert" style="border-left:4px solid var(--bs-info);font-size:0.8rem;">
    <div class="d-flex align-items-center gap-1 flex-wrap">
        <i class="bi bi-bell <?= $notificaciones_op_no_leidas > 0 ? 'text-info' : 'text-muted' ?>"></i>
        <span class="fw-bold small">Notificaciones:</span>
        <?php foreach ($notificaciones_op as $n): ?>
        <span class="badge bg-<?= $n['leida'] ? 'secondary' : 'info' ?> text-dark px-1 py-0" style="font-size:0.7rem;" title="<?= safe_string($n['mensaje']) ?>">
            <?= safe_string($n['titulo']) ?>
        </span>
        <?php endforeach; ?>
        <?php if ($notificaciones_op_no_leidas > 0): ?>
        <form method="POST" action="<?= url('notificaciones-operador/marcar-leidas') ?>" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-1" style="font-size:0.7rem;"><i class="bi bi-check-all"></i></button>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($alertas_operador)): ?>
<div class="mb-2">
    <?php foreach ($alertas_operador as $alerta): ?>
    <div class="alert alert-<?= $alerta['tipo'] ?> py-1 px-2 mb-1" style="font-size:0.75rem;border-left:3px solid var(--bs-<?= $alerta['tipo'] ?>);">
        <i class="bi <?= $alerta['icono'] ?>"></i> <?= safe_string($alerta['mensaje']) ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-4 col-md mb-0">
        <div class="stat-card card-primary px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= number_format($piezas_hoy) ?></div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-box-seam"></i> Piezas Hoy</div>
            </div>
        </div>
    </div>
    <div class="col-4 col-md mb-0">
        <div class="stat-card card-<?= $eficiencia >= 80 ? 'success' : ($eficiencia >= 50 ? 'warning' : 'danger') ?> px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $eficiencia ?>%</div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-graph-up"></i> Eficiencia</div>
            </div>
        </div>
    </div>
    <div class="col-4 col-md mb-0">
        <div class="stat-card card-<?= $tasa_defectos <= 2 ? 'success' : ($tasa_defectos <= 5 ? 'warning' : 'danger') ?> px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $tasa_defectos ?>%</div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-x-circle"></i> Defectos</div>
            </div>
        </div>
    </div>
    <div class="col-4 col-md mb-0">
        <div class="stat-card card-info px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $ordenes_activas ?></div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-play-circle"></i> Activas</div>
            </div>
        </div>
    </div>
    <div class="col-4 col-md mb-0">
        <div class="stat-card card-<?= $minutos_paro > 30 ? 'warning' : 'success' ?> px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $minutos_paro ?>m</div>
                <div class="stat-label" style="font-size:0.7rem;"><i class="bi bi-pause-circle"></i> Paros</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-2">
    <div class="col-12">
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-between bg-light rounded p-1 border" style="font-size:0.8rem;">
            <span><i class="bi bi-check-circle text-success"></i> <strong>Mi Turno:</strong> <?= $mis_completadas_count ?> completadas</span>
            <span><i class="bi bi-box-seam text-primary"></i> <strong>Producido:</strong> <?= number_format($mi_producido) ?> pzas</span>
            <span><i class="bi bi-clipboard-data text-dark"></i> <strong>Global:</strong> <?= $resumen_turno['total_ordenes'] ?? 0 ?> órdenes, <?= number_format($resumen_turno['total_producido'] ?? 0) ?>/<?= number_format($resumen_turno['total_planificado'] ?? 0) ?> pzas</span>
            <span class="text-muted" data-bs-toggle="tooltip" title="Órdenes completadas vs planificadas">
                <?= ($resumen_turno['total_planificado'] ?? 0) > 0 ? round(($resumen_turno['total_producido'] ?? 0) / $resumen_turno['total_planificado'] * 100) : 0 ?>% eficiencia
            </span>
        </div>
    </div>
</div>

<?php if (!empty($produccion_semanal)): ?>
<div class="row g-2 mb-3">
    <div class="col-12 mb-0">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-bar-chart"></i> Producción Semanal</small></div>
            <div class="card-body py-2 px-2">
                <canvas id="produccionChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-2 mb-3">
    <div class="col-12 mb-0">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-tools"></i> Estado de Máquinas</small></div>
            <div class="card-body py-1 px-2">
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($maquinas_con_estado as $mq): ?>
                    <?php $color = $mq['estado_real'] === 'paro' || $mq['estado_real'] === 'detenida' ? 'danger' : ($mq['estado_real'] === 'operando' ? 'success' : ($mq['estado_real'] === 'setup' ? 'warning' : ($mq['estado_real'] === 'mantenimiento' ? 'info' : 'secondary'))); ?>
                    <span class="badge bg-<?= $color ?> d-inline-flex align-items-center gap-1 py-1" style="font-size:0.75rem;"
                        title="<?= $mq['estado_real'] === 'detenida' ? 'Paro: ' . safe_string($mq['motivo_paro'] ?? '') : 'Estado: ' . $mq['estado_real'] ?>">
                        <span class="rounded-circle bg-white" style="width:6px;height:6px;opacity:0.8;"></span>
                        <?= safe_string($mq['nombre']) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-warning text-dark py-1 px-2"><small><i class="bi bi-hourglass-split"></i> Pendientes por Completar</small></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.8rem;">
                        <thead><tr><th>#</th><th>Producto</th><th class="text-center">Plan.</th></tr></thead>
                        <tbody>
                            <?php foreach ($pendientes_completar as $p): ?>
                            <tr>
                                <td><a href="<?= url('ordenes/detalle/' . $p['id_orden_cabe']) ?>"><?= $p['id_orden_cabe'] ?></a></td>
                                <td><?= safe_string($p['producto_nombre'] ?? 'N/A') ?></td>
                                <td class="text-center"><?= $p['cantidad_planificada'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pendientes_completar)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Ninguna pendiente</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-danger text-white py-1 px-2"><small><i class="bi bi-exclamation-triangle"></i> Incidencias de Hoy</small></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.8rem;">
                        <thead><tr><th>Producto</th><th>Estatus</th></tr></thead>
                        <tbody>
                            <?php foreach ($incidencias_hoy as $inc): ?>
                            <tr>
                                <td><?= safe_string($inc['producto_nombre'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-<?= $inc['estatus'] === 'abierta' ? 'danger' : 'warning' ?>"><?= safe_string($inc['estatus']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($incidencias_hoy)): ?>
                            <tr><td colspan="2" class="text-center text-muted">Sin incidencias</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-secondary text-white py-1 px-2"><small><i class="bi bi-pause-circle"></i> Paros Activos</small></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.8rem;">
                        <thead><tr><th>Máquina</th><th>Desde</th></tr></thead>
                        <tbody>
                            <?php foreach ($paros_activos as $paro): ?>
                            <tr>
                                <td><?= safe_string($paro['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= $paro['hora_inicio'] ? substr($paro['hora_inicio'], 0, 5) : '—' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($paros_activos)): ?>
                            <tr><td colspan="2" class="text-center text-muted">Sin paros activos</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-12 mb-0">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-calendar-check"></i> Órdenes de Mi Turno</small></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.8rem;">
                        <thead><tr><th>#</th><th>Producto</th><th>Máquina</th><th class="text-center">Plan.</th><th class="text-center">Reales</th><th style="width:120px;">Avance</th><th>Estatus</th><th class="text-end">Acción</th></tr></thead>
                        <tbody>
                            <?php foreach ($ordenes_mi_turno as $o): ?>
                            <tr>
                                <td><a href="<?= url('ordenes/detalle/' . $o['id_orden_cabe']) ?>"><?= $o['id_orden_cabe'] ?></a></td>
                                <td><?= safe_string($o['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= safe_string($o['maquina_nombre'] ?? '—') ?></td>
                                <td class="text-center"><?= $o['cantidad_planificada'] ?></td>
                                <td class="text-center"><?= $o['cantidad_real_buenas'] ?? '<span class="text-muted">—</span>' ?></td>
                                <td>
                                    <div class="progress" style="height:12px;">
                                        <div class="progress-bar bg-<?= $o['porcentaje_avance'] >= 100 ? 'success' : ($o['porcentaje_avance'] > 0 ? 'primary' : 'secondary') ?>"
                                             style="width:<?= $o['porcentaje_avance'] ?>%;font-size:0.6rem;line-height:12px;">
                                            <?= $o['porcentaje_avance'] ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php $est = $o['estatus'] ?? ($o['cantidad_real_buenas'] ? 'completada' : 'pendiente'); ?>
                                    <?php if ($est === 'completada'): ?>
                                    <span class="badge bg-success">Completada</span>
                                    <?php elseif ($est === 'en_progreso'): ?>
                                    <span class="badge bg-primary">En Progreso</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning text-dark">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (empty($o['cantidad_real_buenas']) && ($o['estatus'] ?? 'pendiente') !== 'en_progreso'): ?>
                                    <form method="POST" action="<?= url('ordenes/iniciar/' . $o['id_orden_cabe']) ?>" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm btn-primary py-0 px-1" title="Iniciar"><i class="bi bi-play-fill"></i></button>
                                    </form>
                                    <?php elseif (($o['estatus'] ?? '') === 'en_progreso'): ?>
                                    <button type="button" class="btn btn-sm btn-success py-0 px-1" title="Completar"
                                        data-bs-toggle="modal" data-bs-target="#completarModal"
                                        data-orden-id="<?= $o['id_orden_cabe'] ?>"
                                        data-planificada="<?= $o['cantidad_planificada'] ?>">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($ordenes_mi_turno)): ?>
                            <tr><td colspan="8" class="text-center text-muted">Sin órdenes para tu turno hoy</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($mantenimiento_proximo) || !empty($inspecciones_pendientes) || !empty($incidencias_abiertas)): ?>
<div class="row g-2 mb-3">
    <?php if (!empty($mantenimiento_proximo)): ?>
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-info text-white py-1 px-2"><small><i class="bi bi-tools"></i> Mantenimiento Próximo</small></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:0.75rem;">
                        <thead><tr><th>Máquina</th><th>Fecha</th><th>Tipo</th></tr></thead>
                        <tbody>
                            <?php foreach ($mantenimiento_proximo as $mtto): ?>
                            <tr>
                                <td><?= safe_string($mtto['maquina_nombre'] ?? 'N/A') ?></td>
                                <td><?= format_date($mtto['fecha_mantenimiento']) ?></td>
                                <td><?= safe_string($mtto['tipo_mantenimiento'] ?? '—') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($inspecciones_pendientes)): ?>
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white py-1 px-2"><small><i class="bi bi-clipboard-check"></i> Inspecciones Pendientes</small></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:0.75rem;">
                        <thead><tr><th>Producto</th><th>Fecha</th></tr></thead>
                        <tbody>
                            <?php foreach ($inspecciones_pendientes as $ins): ?>
                            <tr>
                                <td><?= safe_string($ins['producto_nombre'] ?? 'N/A') ?></td>
                                <td><?= format_date($ins['fecha_inspeccion']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($incidencias_abiertas)): ?>
    <div class="col-12 col-md-4 mb-0">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-danger text-white py-1 px-2"><small><i class="bi bi-exclamation-triangle"></i> Incidencias Abiertas</small></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="font-size:0.75rem;">
                        <thead><tr><th>Producto</th><th>Estatus</th></tr></thead>
                        <tbody>
                            <?php foreach ($incidencias_abiertas as $inc): ?>
                            <tr>
                                <td><?= safe_string($inc['producto_nombre'] ?? 'N/A') ?></td>
                                <td><span class="badge bg-<?= $inc['estatus'] === 'abierta' ? 'danger' : 'warning' ?>"><?= safe_string($inc['estatus']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-1">
            <a href="<?= url('ordenes/create') ?>" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Nueva Orden</a>
            <a href="<?= url('mis-ordenes') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-list-check"></i> Mis Órdenes</a>
            <a href="<?= url('maquinas/estado') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-tools"></i> Estado Máquinas</a>
            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reportarParoModal"><i class="bi bi-pause-circle"></i> Reportar Paro</button>
            <a href="<?= url('incidencias/create') ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-exclamation-triangle"></i> Registrar Incidencia</a>
            <a href="<?= url('calidad/pendientes') ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-clipboard-check"></i> Inspecciones</a>
            <a href="<?= url('bitacora') ?>" class="btn btn-outline-info btn-sm"><i class="bi bi-journal-text"></i> Bitácora</a>
            <a href="<?= url('materiales') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-boxes"></i> Materiales</a>
            <a href="<?= url('ordenes') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-table"></i> Todas las Órdenes</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/partials/completar_modal.php'; ?>

<div class="modal fade" id="reportarParoModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="<?= url('reportar-paro') ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="bi bi-pause-circle"></i> Reportar Paro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small">Máquina</label>
                        <select name="id_maquina" class="form-select form-select-sm" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($maquinas_activas as $m): ?>
                            <option value="<?= $m['id_maquina'] ?>"><?= safe_string($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Motivo</label>
                        <textarea name="motivo_paro" class="form-control form-control-sm" rows="2" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Hora Inicio</label>
                        <input type="time" name="hora_inicio" class="form-control form-control-sm" value="<?= date('H:i') ?>">
                    </div>
                </div>
                <div class="modal-footer p-1">
                    <button type="submit" class="btn btn-warning btn-sm w-100"><i class="bi bi-save"></i> Reportar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var completarModal = document.getElementById('completarModal');
    if (completarModal) {
        completarModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var ordenId = button.getAttribute('data-orden-id');
            var planificada = button.getAttribute('data-planificada');
            var form = completarModal.querySelector('#completarForm');
            form.action = '<?= url('ordenes/completar/') ?>' + ordenId;
            form.querySelector('[name="cantidad_real_buenas"]').value = planificada;
        });
    }

    <?php if (!empty($produccion_semanal)): ?>
    var ctx = document.getElementById('produccionChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php foreach ($produccion_semanal as $d): ?>'<?= substr($d['fecha'], 5) ?>',<?php endforeach; ?>],
                datasets: [{
                    label: 'Producido',
                    data: [<?php foreach ($produccion_semanal as $d): ?><?= (int) $d['producido'] ?>,<?php endforeach; ?>],
                    backgroundColor: '#198754',
                    borderRadius: 3
                }, {
                    label: 'Planificado',
                    data: [<?php foreach ($produccion_semanal as $d): ?><?= (int) $d['planificado'] ?>,<?php endforeach; ?>],
                    backgroundColor: '#0d6efd',
                    borderRadius: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { font: { size: 9 } } },
                    x: { ticks: { font: { size: 8 } } }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>

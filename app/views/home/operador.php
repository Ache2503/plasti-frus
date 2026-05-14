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

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3 mb-0">
        <div class="stat-card card-success px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= count($ordenes_hoy) ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-clipboard-check"></i> Órdenes Hoy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-0">
        <div class="stat-card card-primary px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= count($ordenes_mi_turno) ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-clock"></i> Mi Turno</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-0">
        <div class="stat-card card-danger px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $total_incidencias_hoy ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-exclamation-triangle"></i> Incidencias</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-0">
        <div class="stat-card card-warning px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= $total_paros_activos ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-pause-circle"></i> Paros Activos</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-2">
    <div class="col-12">
        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-between bg-light rounded p-1 border" style="font-size:0.8rem;">
            <span><i class="bi bi-check-circle text-success"></i> <strong>Mi Turno:</strong> <?= $mis_completadas_count ?> completadas</span>
            <span><i class="bi bi-box-seam text-primary"></i> <strong>Producido:</strong> <?= $mi_producido ?> pzas</span>
            <span><i class="bi bi-clipboard-data text-dark"></i> <strong>Global:</strong> <?= $resumen_turno['total_ordenes'] ?? 0 ?> órdenes, <?= $resumen_turno['total_producido'] ?? 0 ?>/<?= $resumen_turno['total_planificado'] ?? 0 ?> pzas</span>
            <span class="text-muted" data-bs-toggle="tooltip" title="Órdenes completadas vs planificadas"><?= $resumen_turno['total_planificado'] > 0 ? round(($resumen_turno['total_producido'] ?? 0) / $resumen_turno['total_planificado'] * 100) : 0 ?>% eficiencia</span>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-12 mb-0">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-1 px-2"><small><i class="bi bi-tools"></i> Estado de Máquinas</small></div>
            <div class="card-body py-1 px-2">
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($maquinas_con_estado as $mq): ?>
                    <?php $color = $mq['estado_real'] === 'paro' ? 'danger' : ($mq['estado_real'] === 'activo' ? 'success' : 'secondary'); ?>
                    <span class="badge bg-<?= $color ?> d-inline-flex align-items-center gap-1 py-1" style="font-size:0.75rem;"
                        title="<?= $mq['estado_real'] === 'paro' ? 'Paro: ' . safe_string($mq['motivo_paro']) : 'Estatus: ' . $mq['estado_real'] ?>">
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
                        <thead><tr><th>#</th><th>Producto</th><th class="text-center">Plan.</th><th class="text-center">Reales</th><th>Estatus</th><th class="text-end">Acción</th></tr></thead>
                        <tbody>
                            <?php foreach ($ordenes_mi_turno as $o): ?>
                            <tr>
                                <td><a href="<?= url('ordenes/detalle/' . $o['id_orden_cabe']) ?>"><?= $o['id_orden_cabe'] ?></a></td>
                                <td><?= safe_string($o['producto_nombre'] ?? 'N/A') ?></td>
                                <td class="text-center"><?= $o['cantidad_planificada'] ?></td>
                                <td class="text-center"><?= $o['cantidad_real_buenas'] ?? '<span class="text-muted">—</span>' ?></td>
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
                            <tr><td colspan="6" class="text-center text-muted">Sin órdenes para tu turno hoy</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-1">
            <a href="<?= url('ordenes/create') ?>" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Nueva Orden</a>
            <a href="<?= url('mis-ordenes') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-list-check"></i> Mis Órdenes</a>
            <a href="<?= url('ordenes') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-table"></i> Todas las Órdenes</a>
            <a href="<?= url('incidencias') ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-exclamation-triangle"></i> Incidencias</a>
            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reportarParoModal"><i class="bi bi-pause-circle"></i> Reportar Paro</button>
            <a href="<?= url('materiales') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-boxes"></i> Materiales</a>
            <a href="<?= url('maquinas') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-tools"></i> Máquinas</a>
            <a href="<?= url('productos') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-cube"></i> Productos</a>
            <a href="<?= url('recetas') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-journal-text"></i> Recetas</a>
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
});
</script>

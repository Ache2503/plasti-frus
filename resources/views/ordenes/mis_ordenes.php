<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-list-check"></i> Mis Órdenes</h1>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-<?= $turno_actual === 'matutino' ? 'warning' : ($turno_actual === 'vespertino' ? 'info' : 'dark') ?> text-dark"><?= ucfirst($turno_actual) ?></span>
        <span class="badge bg-info"><?= safe_string($rol_nombre) ?></span>
    </div>
</div>

<div class="row g-2 mb-2">
    <div class="col-12">
        <form method="GET" class="row g-1 align-items-end">
            <div class="col-auto">
                <label class="small text-muted">Fecha</label>
                <input type="date" name="fecha" class="form-control form-control-sm" value="<?= $fecha_buscar ?>">
            </div>
            <div class="col-auto">
                <label class="small text-muted">Turno</label>
                <select name="turno" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="matutino" <?= ($turno_filtro ?? '') === 'matutino' ? 'selected' : '' ?>>Matutino</option>
                    <option value="vespertino" <?= ($turno_filtro ?? '') === 'vespertino' ? 'selected' : '' ?>>Vespertino</option>
                    <option value="nocturno" <?= ($turno_filtro ?? '') === 'nocturno' ? 'selected' : '' ?>>Nocturno</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-outline-dark"><i class="bi bi-search"></i> Filtrar</button>
                <a href="<?= url('mis-ordenes') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-circle"></i> Hoy</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-3 col-md-3 mb-0">
        <div class="stat-card card-primary px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;"><?= count($ordenes) ?></div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-clipboard-check"></i> Asignadas</div>
            </div>
        </div>
    </div>
    <div class="col-3 col-md-3 mb-0">
        <div class="stat-card card-success px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;">
                    <?= count(array_filter($ordenes, fn($o) => $o['cantidad_real_buenas'] !== null && $o['cantidad_real_buenas'] > 0)) ?>
                </div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-check-circle"></i> Completadas</div>
            </div>
        </div>
    </div>
    <div class="col-3 col-md-3 mb-0">
        <div class="stat-card card-info px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;">
                    <?= count(array_filter($ordenes, fn($o) => ($o['estatus'] ?? '') === 'en_progreso')) ?>
                </div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-play-circle"></i> En Progreso</div>
            </div>
        </div>
    </div>
    <div class="col-3 col-md-3 mb-0">
        <div class="stat-card card-warning px-2 py-2">
            <div class="stat-content">
                <div class="stat-number" style="font-size:1.3rem;">
                    <?= count(array_filter($ordenes, fn($o) => ($o['cantidad_real_buenas'] === null || $o['cantidad_real_buenas'] == 0) && ($o['estatus'] ?? '') !== 'en_progreso')) ?>
                </div>
                <div class="stat-label" style="font-size:0.75rem;"><i class="bi bi-hourglass-split"></i> Pendientes</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white py-1 px-2">
        <small><i class="bi bi-list-check"></i> Órdenes del <?= format_date($fecha_buscar) ?></small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="text-center">Plan.</th>
                        <th class="text-center">Reales</th>
                        <th>Máquina</th>
                        <th>Turno</th>
                        <th>Avance</th>
                        <th>Estatus</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenes as $o): ?>
                    <?php $est = $o['estatus'] ?? ($o['cantidad_real_buenas'] ? 'completada' : 'pendiente'); ?>
                    <?php $avance = $o['cantidad_planificada'] > 0 ? round(($o['cantidad_real_buenas'] ?? 0) / $o['cantidad_planificada'] * 100) : 0; ?>
                    <tr>
                        <td><?= $o['id_orden_cabe'] ?></td>
                        <td><?php if (!empty($o['temperatura_inyeccion_C'])): ?>
                            <span data-bs-toggle="tooltip" title="Temp: <?= $o['temperatura_inyeccion_C'] ?>°C | Pres: <?= $o['presion_inyeccion_bar'] ?>bar | Enf: <?= $o['tiempo_enfriamiento_s'] ?>s">
                                <?= safe_string($o['producto_nombre'] ?? 'N/A') ?>
                                <i class="bi bi-info-circle text-info small"></i>
                            </span>
                            <?php else: ?>
                            <?= safe_string($o['producto_nombre'] ?? 'N/A') ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $o['cantidad_planificada'] ?></td>
                        <td class="text-center"><?= $o['cantidad_real_buenas'] ?? '<span class="text-muted">—</span>' ?></td>
                        <td><?= safe_string($o['maquina_nombre'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge bg-<?= $o['turno'] === 'matutino' ? 'warning' : ($o['turno'] === 'vespertino' ? 'info' : 'dark') ?> text-dark">
                                <?= ucfirst($o['turno']) ?>
                            </span>
                        </td>
                        <td style="min-width:80px;">
                            <div class="progress" style="height:14px;">
                                <div class="progress-bar bg-<?= $avance >= 100 ? 'success' : ($avance > 0 ? 'primary' : 'secondary') ?>"
                                    role="progressbar" style="width:<?= min($avance, 100) ?>%; font-size:10px; line-height:14px;">
                                    <?= $avance ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($est === 'completada'): ?>
                            <span class="badge bg-success">Completada</span>
                            <?php elseif ($est === 'en_progreso'): ?>
                            <span class="badge bg-primary">En Progreso</span>
                            <?php else: ?>
                            <span class="badge bg-warning text-dark">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($est === 'pendiente'): ?>
                            <form method="POST" action="<?= url('ordenes/iniciar/' . $o['id_orden_cabe']) ?>" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-sm btn-primary py-0 px-1" title="Iniciar"><i class="bi bi-play-fill"></i></button>
                            </form>
                            <?php elseif ($est === 'en_progreso'): ?>
                            <button type="button" class="btn btn-sm btn-success py-0 px-1" title="Completar"
                                data-bs-toggle="modal" data-bs-target="#completarModal"
                                data-orden-id="<?= $o['id_orden_cabe'] ?>"
                                data-planificada="<?= $o['cantidad_planificada'] ?>">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <?php endif; ?>
                            <a href="<?= url('ordenes/detalle/' . $o['id_orden_cabe']) ?>" class="btn btn-sm btn-outline-primary py-0 px-1"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ordenes)): ?>
                    <tr><td colspan="9" class="text-center text-muted">No hay órdenes para esta fecha</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?= url('ordenes') ?>" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left"></i> Todas las Órdenes</a>
    <a href="<?= url('ordenes/create') ?>" class="btn btn-dark btn-sm"><i class="bi bi-plus-lg"></i> Nueva Orden</a>
</div>

<?php $modalPath = __DIR__ . '/../home/partials/completar_modal.php'; if (file_exists($modalPath)) include $modalPath; ?>

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

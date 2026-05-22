<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h4 mb-0"><i class="bi bi-tools"></i> Estado de Máquinas</h1>
    <div class="d-flex gap-2 align-items-center">
        <form method="get" class="d-flex gap-1">
            <select name="seccion" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="">Todas las secciones</option>
                <?php foreach ($secciones as $s): ?>
                <option value="<?= safe_string($s) ?>" <?= ($seccion_activa ?? '') === $s ? 'selected' : '' ?>><?= safe_string($s) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="<?= url('maquinas') ?>" class="btn btn-sm btn-outline-dark"><i class="bi bi-gear"></i> Administrar</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <?php foreach ($maquinas as $mq): ?>
    <?php
        $estilos = ['operando' => 'success', 'setup' => 'warning', 'detenida' => 'danger', 'apagada' => 'secondary', 'mantenimiento' => 'info'];
        $color = $estilos[$mq['estado_real']] ?? 'secondary';
        $iconos = ['operando' => 'play-circle', 'setup' => 'gear', 'detenida' => 'exclamation-circle', 'apagada' => 'power', 'mantenimiento' => 'tools'];
        $icono = $iconos[$mq['estado_real']] ?? 'question-circle';
    ?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="card shadow-sm border-<?= $color ?> h-100">
            <div class="card-header bg-<?= $color ?> text-white py-1 px-2 d-flex justify-content-between align-items-center">
                <small class="fw-bold"><i class="bi bi-<?= $icono ?>"></i> <?= safe_string($mq['nombre']) ?></small>
                <span class="badge bg-light text-dark" style="font-size:0.6rem;"><?= safe_string($mq['estado_real']) ?></span>
            </div>
            <div class="card-body py-1 px-2" style="font-size:0.75rem;">
                <div>Modelo: <?= safe_string($mq['modelo'] ?? '—') ?></div>
                <div>Serie: <?= safe_string($mq['numero_serie'] ?? '—') ?></div>
                <?php if ($mq['estado_real'] === 'detenida' && $mq['motivo_paro']): ?>
                <div class="text-danger"><i class="bi bi-exclamation-triangle"></i> <?= safe_string($mq['motivo_paro']) ?></div>
                <div class="text-muted"><i class="bi bi-clock"></i> Desde: <?= $mq['paro_desde'] ? substr($mq['paro_desde'], 0, 5) : '—' ?></div>
                <?php endif; ?>
                <?php if ($mq['ultima_orden']): ?>
                <div class="text-muted">Última orden: <?= format_date($mq['ultima_orden']) ?></div>
                <?php endif; ?>
                <div>Órdenes pend.: <?= $mq['ordenes_pendientes'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($maquinas)): ?>
<div class="alert alert-info">No hay máquinas registradas.</div>
<?php endif; ?>

<div class="card shadow-sm mt-2">
    <div class="card-body py-1 px-2">
        <div class="d-flex flex-wrap gap-3 align-items-center" style="font-size:0.8rem;">
            <span><span class="badge bg-success">&nbsp;&nbsp;</span> Operando</span>
            <span><span class="badge bg-warning">&nbsp;&nbsp;</span> Setup</span>
            <span><span class="badge bg-danger">&nbsp;&nbsp;</span> Detenida</span>
            <span><span class="badge bg-secondary">&nbsp;&nbsp;</span> Apagada</span>
            <span><span class="badge bg-info">&nbsp;&nbsp;</span> Mantenimiento</span>
        </div>
    </div>
</div>

<script>
setTimeout(function() {
    fetch('<?= url('maquinas/estado-json') ?>')
        .then(r => r.json())
        .then(d => { if (!d.success) location.reload(); })
        .catch(() => {});
}, 30000);
</script>

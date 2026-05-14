<?php
$filterAction = $filterAction ?? '';
$filterExtra = $filterExtra ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
?>
<form method="GET" action="<?= url($filterAction) ?>" class="row row-cols-lg-auto g-2 align-items-end mb-3 bg-light p-3 rounded-3 border">
    <div class="col-12">
        <label class="form-label small fw-semibold mb-0">Desde</label>
        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= safe_string($fecha_desde) ?>">
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold mb-0">Hasta</label>
        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= safe_string($fecha_hasta) ?>">
    </div>
    <?= $filterExtra ?>
    <div class="col-12 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-funnel"></i> Filtrar</button>
        <a href="<?= url($filterAction) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
    </div>
</form>

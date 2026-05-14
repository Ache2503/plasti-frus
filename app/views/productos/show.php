<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cube"></i> <?= safe_string($producto['nombre']) ?></h1>
    <div>
        <a href="<?= url('productos/edit/' . $producto['id_producto']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Editar</a>
        <a href="<?= url('productos') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white"><i class="bi bi-info-circle"></i> Información General</div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr><th>Código</th><td><?= safe_string($producto['codigo']) ?></td></tr>
                    <tr><th>Nombre</th><td><?= safe_string($producto['nombre']) ?></td></tr>
                    <tr><th>Familia</th><td><?= safe_string($producto['familia']) ?></td></tr>
                    <tr><th>Línea</th><td><?= safe_string($producto['linea']) ?></td></tr>
                    <tr><th>Color</th><td><?= safe_string($producto['color']) ?></td></tr>
                    <tr><th>Peso Unitario</th><td><?= $producto['peso_unitario_grs'] ?> grs</td></tr>
                    <tr><th>Dimensiones</th><td><?= safe_string($producto['dimensiones']) ?></td></tr>
                    <tr><th>Descripción</th><td><?= safe_string($producto['descripcion_comercial']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white"><i class="bi bi-journal-text"></i> Recetas</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Versión</th><th>Máquina</th><th>Fecha</th></tr></thead>
                    <tbody>
                        <?php foreach ($recetas as $r): ?>
                        <tr><td><?= safe_string($r['version']) ?></td><td><?= safe_string($r['maquina_nombre'] ?? 'N/A') ?></td><td><?= format_date($r['fecha_version']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($recetas)): ?>
                        <tr><td colspan="3" class="text-muted text-center">Sin recetas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-secondary text-white"><i class="bi bi-clipboard-check"></i> Órdenes Recientes</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Máquina</th><th>Cant.</th><th>Fecha</th></tr></thead>
                    <tbody>
                        <?php foreach ($ordenes as $o): ?>
                        <tr><td><?= $o['id_orden_cabe'] ?></td><td><?= safe_string($o['maquina_nombre'] ?? 'N/A') ?></td><td><?= $o['cantidad_planificada'] ?></td><td><?= format_date($o['fecha']) ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($ordenes)): ?>
                        <tr><td colspan="4" class="text-muted text-center">Sin órdenes</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

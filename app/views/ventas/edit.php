<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Editar Venta</h1>
    <a href="<?= url('ventas') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('ventas/update/' . $venta['id_venta']) ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cliente</label>
                    <select name="id_cliente" class="form-select" required>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>" <?= ($venta['id_cliente'] ?? '') == $c['id_cliente'] ? 'selected' : '' ?>><?= safe_string($c['razon_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Producto</label>
                    <select name="id_producto" class="form-select" required>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>" <?= ($venta['id_producto'] ?? '') == $p['id_producto'] ? 'selected' : '' ?>><?= safe_string($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad_vendida" class="form-control" value="<?= $venta['cantidad_vendida'] ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Precio Unitario</label>
                    <input type="number" step="0.01" name="precio_unitario" class="form-control" value="<?= $venta['precio_unitario'] ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Moneda</label>
                    <select name="moneda" class="form-select">
                        <option value="MXN" <?= ($venta['moneda'] ?? 'MXN') === 'MXN' ? 'selected' : '' ?>>MXN</option>
                        <option value="USD" <?= ($venta['moneda'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha_venta" class="form-control" value="<?= $venta['fecha_venta'] ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Condiciones de Pago</label>
                    <input type="text" name="condiciones_pago" class="form-control" value="<?= safe_string($venta['condiciones_pago']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-select">
                        <option value="completado" <?= $venta['estatus'] === 'completado' ? 'selected' : '' ?>>Completado</option>
                        <option value="pendiente" <?= $venta['estatus'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="cancelado" <?= $venta['estatus'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Actualizar</button>
        </form>
    </div>
</div>

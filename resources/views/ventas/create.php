<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-lg"></i> Nueva Venta</h1>
    <a href="<?= url('ventas') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('ventas/store') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cliente <span class="text-danger">*</span></label>
                    <select name="id_cliente" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id_cliente'] ?>"><?= safe_string($c['razon_social']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Producto <span class="text-danger">*</span></label>
                    <select name="id_producto" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id_producto'] ?>"><?= safe_string($p['codigo'] . ' - ' . $p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" name="cantidad_vendida" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Precio Unitario <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="precio_unitario" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Moneda</label>
                    <select name="moneda" class="form-select">
                        <option value="MXN">MXN</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fecha Venta</label>
                    <input type="date" name="fecha_venta" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Condiciones de Pago</label>
                    <input type="text" name="condiciones_pago" class="form-control" placeholder="ej. Crédito 30 días">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-select">
                        <option value="completado">Completado</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-dark"><i class="bi bi-save"></i> Guardar</button>
        </form>
    </div>
</div>

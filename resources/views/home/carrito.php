<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-cart3"></i> Mi Carrito</h1>
    <a href="<?= url('catalogo') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Seguir comprando</a>
</div>

<?php if (empty($items)): ?>
<div class="text-center py-5">
    <i class="bi bi-cart-x text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted">Tu carrito está vacío</h4>
    <p class="text-muted">Explora nuestros productos y agrega los que necesites.</p>
    <a href="<?= url('catalogo') ?>" class="btn btn-dark"><i class="bi bi-shop"></i> Ver tienda</a>
</div>
<?php else: ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Producto</th><th>Precio Unit.</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box-seam" style="color: #667eea;"></i>
                                        </div>
                                        <div>
                                            <strong><?= safe_string($item['producto']['nombre']) ?></strong><br>
                                            <small class="text-muted"><?= safe_string($item['producto']['codigo']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= format_money($item['precio_unitario']) ?></td>
                                <td>
                                    <form method="POST" action="<?= url('carrito/actualizar/' . $item['key']) ?>" class="d-flex align-items-center gap-1" style="max-width: 130px;">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="button" class="btn btn-sm btn-outline-secondary qty-btn" onclick="var i=this.nextElementSibling; i.stepDown(); i.dispatchEvent(new Event('change'))">−</button>
                                        <input type="number" name="cantidad" class="form-control form-control-sm text-center" value="<?= $item['cantidad'] ?>" min="1" max="<?= $item['producto']['stock_actual'] ?: 999 ?>" style="width: 40px;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary qty-btn" onclick="var i=this.previousElementSibling; i.stepUp(); i.dispatchEvent(new Event('change'))">+</button>
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Actualizar cantidad"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                </td>
                                <td><strong><?= format_money($item['subtotal']) ?></strong></td>
                                <td>
                                    <form method="POST" action="<?= url('carrito/eliminar/' . $item['key']) ?>" style="display:inline" onsubmit="return confirm('¿Eliminar producto?')">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-receipt"></i> Resumen
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Productos:</span>
                    <span><?= count($items) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Total:</span>
                    <span class="h5 mb-0"><?= format_money($total) ?></span>
                </div>
                <form method="POST" action="<?= url('carrito/checkout') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <button type="submit" class="btn btn-dark w-100" onclick="return confirm('¿Confirmar compra?')">
                        <i class="bi bi-cart-check"></i> Realizar pedido
                    </button>
                </form>
                <p class="text-muted small mt-2 mb-0 text-center">
                    <i class="bi bi-info-circle"></i> Tu pedido quedará en estatus "pendiente" hasta que sea procesado.
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-box-seam"></i> <?= safe_string($producto['nombre']) ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= url('carrito') ?>" class="btn btn-sm btn-outline-dark position-relative">
            <i class="bi bi-cart3"></i> Carrito
            <?php $cartCountP = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad')); ?>
            <?php if ($cartCountP > 0): ?>
            <span class="badge bg-danger ms-1"><?= $cartCountP ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('catalogo') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-5 text-center mb-4 mb-md-0">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-4 bg-light" style="width: 180px; height: 180px;">
                            <i class="bi bi-box-seam" style="font-size: 5rem; color: #667eea;"></i>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h3 class="fw-bold mb-1"><?= safe_string($producto['nombre']) ?></h3>
                        <?php if (!empty($producto['codigo'])): ?>
                        <p class="text-muted mb-2"><strong>Código:</strong> <?= safe_string($producto['codigo']) ?></p>
                        <?php endif; ?>
                        <div class="product-price mb-3 text-start"><?= format_money($producto['precio_venta'] ?? $producto['costo_estimado'] ?? 0) ?></div>

                        <?php
                        $enCarrito = false;
                        $cantEnCarrito = 0;
                        foreach ($_SESSION['cart'] ?? [] as $cartItem) {
                            if ($cartItem['id_producto'] == $producto['id_producto']) {
                                $enCarrito = true;
                                $cantEnCarrito = $cartItem['cantidad'];
                                break;
                            }
                        }
                        ?>
                        <?php if ($enCarrito): ?>
                        <div class="alert alert-info py-2 mb-2 small d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i> Ya tienes <strong><?= $cantEnCarrito ?></strong> unidad(es) en tu <a href="<?= url('carrito') ?>" class="fw-semibold">carrito</a>.
                        </div>
                        <?php endif; ?>
                        <div class="d-flex gap-2 mb-3">
                            <form method="POST" action="<?= url('carrito/agregar') ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
                                <input type="hidden" name="precio_unitario" value="<?= $producto['precio_venta'] ?? $producto['costo_estimado'] ?? 0 ?>">
                                <div class="input-group" style="max-width: 260px;">
                                    <button type="button" class="btn btn-outline-secondary qty-btn" onclick="this.nextElementSibling.stepDown();this.nextElementSibling.dispatchEvent(new Event('change'))">−</button>
                                    <input type="number" name="cantidad" class="form-control text-center qty-input" value="1" min="1" max="<?= $producto['stock_actual'] ?: 999 ?>">
                                    <button type="button" class="btn btn-outline-secondary qty-btn" onclick="this.previousElementSibling.stepUp();this.previousElementSibling.dispatchEvent(new Event('change'))">+</button>
                                    <button type="submit" class="btn btn-dark px-4"><i class="bi bi-cart-plus"></i> Agregar</button>
                                </div>
                            </form>
                            <?php if (es_cliente()): ?>
                            <form method="POST" action="<?= url('wishlist/agregar/' . $producto['id_producto']) ?>" class="wishlist-form">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <button type="submit" class="btn btn-outline-danger wishlist-btn" title="<?= $enWishlist ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>">
                                    <i class="bi bi-heart<?= $enWishlist ? '-fill' : '' ?>"></i> Favorito
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <?php if (!empty($producto['familia'])): ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary"><?= safe_string($producto['familia']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($producto['linea'])): ?>
                            <span class="badge bg-info bg-opacity-10 text-info"><?= safe_string($producto['linea']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($producto['color'])): ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= safe_string($producto['color']) ?></span>
                            <?php endif; ?>
                        </div>
    </div>
</div>

<?php if (es_cliente()): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('.wishlist-form');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = form.querySelector('.wishlist-btn');
        var icon = btn.querySelector('i');
        var csrf = form.querySelector('[name="csrf_token"]').value;

        fetch(form.action, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=' + encodeURIComponent(csrf)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (data.agregado) {
                    icon.className = 'bi bi-heart-fill';
                    btn.title = 'Quitar de favoritos';
                } else {
                    icon.className = 'bi bi-heart';
                    btn.title = 'Agregar a favoritos';
                }
            }
        })
        .catch(function() {
            if (typeof showToast !== 'undefined') {
                showToast('Error al actualizar favoritos', 'error');
            }
        });
    });
});
</script>
<?php endif; ?>

                <?php if (!empty($producto['descripcion_comercial'])): ?>
                <hr>
                <div>
                    <h5><i class="bi bi-info-circle"></i> Descripción</h5>
                    <p class="mb-0"><?= nl2br(safe_string($producto['descripcion_comercial'])) ?></p>
                </div>
                <?php endif; ?>

                <hr>
                <div class="row text-center">
                    <?php if (!empty($producto['peso_unitario_grs'])): ?>
                    <div class="col">
                        <small class="text-muted d-block">Peso Unitario</small>
                        <strong><?= $producto['peso_unitario_grs'] ?> gr</strong>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($producto['dimensiones'])): ?>
                    <div class="col">
                        <small class="text-muted d-block">Dimensiones</small>
                        <strong><?= safe_string($producto['dimensiones']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-star"></i> Productos Recomendados
            </div>
            <div class="card-body p-3">
                <?php if (empty($recomendados)): ?>
                <p class="text-muted small mb-0 text-center">No hay recomendaciones disponibles.</p>
                <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($recomendados as $rec): ?>
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 border border-light recomended-item">
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-light flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bi bi-box-seam" style="color: #667eea;"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <a href="<?= url('producto/' . $rec['id_producto']) ?>" class="text-decoration-none fw-semibold small d-block text-truncate"><?= safe_string($rec['nombre']) ?></a>
                            <span class="text-primary fw-bold small"><?= format_money($rec['precio_venta'] ?? $rec['costo_estimado'] ?? 0) ?></span>
                        </div>
                        <form method="POST" action="<?= url('carrito/agregar') ?>">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id_producto" value="<?= $rec['id_producto'] ?>">
                            <input type="hidden" name="precio_unitario" value="<?= $rec['precio_venta'] ?? $rec['costo_estimado'] ?? 0 ?>">
                            <input type="hidden" name="cantidad" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-dark" title="Agregar"><i class="bi bi-cart-plus"></i></button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
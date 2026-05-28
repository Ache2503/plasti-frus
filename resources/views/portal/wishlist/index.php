<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-heart-fill text-danger"></i> Mis Favoritos</h1>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-secondary fs-6 wishlist-count-badge"><?= count($productos) ?> producto(s)</span>
        <?php if (!empty($productos)): ?>
        <form method="POST" action="<?= url('wishlist/agregar-todos') ?>" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" class="btn btn-sm btn-success">
                <i class="bi bi-cart-plus"></i> Agregar Todo al Carrito
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($productos)): ?>
<div class="empty-state py-5">
    <i class="bi bi-heart" style="font-size: 4rem; opacity: .3;"></i>
    <h5 class="mt-3 text-muted">No tienes favoritos aún</h5>
    <p class="text-muted">Explora nuestro catálogo y agrega productos a tu lista de favoritos.</p>
    <a href="<?= url('catalogo') ?>" class="btn btn-dark"><i class="bi bi-shop"></i> Ir a la Tienda</a>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($productos as $p): ?>
    <div class="col-md-4 col-lg-3 col-sm-6">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-secondary"><?= safe_string($p['familia'] ?? 'General') ?></span>
                    <form method="POST" action="<?= url('wishlist/remover/' . $p['id_producto']) ?>" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar de favoritos">
                            <i class="bi bi-heart-fill"></i>
                        </button>
                    </form>
                </div>
                <a href="<?= url('producto/' . $p['id_producto']) ?>" class="text-decoration-none">
                    <h6 class="card-title text-dark"><?= safe_string($p['nombre']) ?></h6>
                </a>
                <p class="card-text small text-muted flex-grow-1"><?= safe_string(truncate($p['descripcion_comercial'] ?? '', 80)) ?></p>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <span class="fs-5 fw-bold"><?= format_money($p['precio_venta']) ?></span>
                    <form method="POST" action="<?= url('carrito/agregar') ?>" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id_producto" value="<?= $p['id_producto'] ?>">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-cart-plus"></i> Al carrito</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

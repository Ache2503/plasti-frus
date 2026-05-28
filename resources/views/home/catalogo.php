<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-shop"></i> Catálogo de Productos</h1>
    <div class="d-flex gap-2">
        <a href="<?= url('carrito') ?>" class="btn btn-sm btn-outline-dark position-relative">
            <i class="bi bi-cart3"></i> Ver Carrito
            <?php if ($carrito_count > 0): ?>
            <span class="badge bg-danger ms-1"><?= $carrito_count ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<!-- Búsqueda y filtros -->
<div class="card shadow-sm mb-4">
    <div class="card-body" style="background: #f8f9fa;">
        <form method="GET" action="<?= url('catalogo') ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold mb-1">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Nombre, código, descripción..." value="<?= safe_string($search) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Familia</label>
                <select name="familia" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($familias as $f): ?>
                    <option value="<?= safe_string($f['familia']) ?>" <?= $familia_filtro === $f['familia'] ? 'selected' : '' ?>><?= safe_string($f['familia']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Línea</label>
                <select name="linea" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($lineas as $l): ?>
                    <option value="<?= safe_string($l['linea']) ?>" <?= $linea_filtro === $l['linea'] ? 'selected' : '' ?>><?= safe_string($l['linea']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Ordenar</label>
                <select name="sort" class="form-select" onchange="this.form.submit()">
                    <option value="nombre" <?= ($sort ?? 'nombre') === 'nombre' ? 'selected' : '' ?>>Nombre</option>
                    <option value="precio_venta" <?= ($sort ?? '') === 'precio_venta' ? 'selected' : '' ?>>Precio</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small fw-semibold mb-1">Dir.</label>
                <select name="order" class="form-select" onchange="this.form.submit()">
                    <option value="asc" <?= ($order ?? 'asc') === 'asc' ? 'selected' : '' ?>>↑</option>
                    <option value="desc" <?= ($order ?? '') === 'desc' ? 'selected' : '' ?>>↓</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark flex-fill"><i class="bi bi-funnel"></i> Filtrar</button>
                <?php if ($search || $familia_filtro || $linea_filtro): ?>
                <a href="<?= url('catalogo') ?>" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (empty($productos)): ?>
<div class="text-center py-5">
    <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted">No hay productos disponibles</h4>
    <?php if ($search || $familia_filtro || $linea_filtro): ?>
    <p class="text-muted">Intenta con otros filtros de búsqueda.</p>
    <a href="<?= url('catalogo') ?>" class="btn btn-dark mt-2"><i class="bi bi-arrow-left"></i> Limpiar filtros</a>
    <?php else: ?>
    <a href="<?= url() ?>" class="btn btn-dark mt-2"><i class="bi bi-arrow-left"></i> Volver al inicio</a>
    <?php endif; ?>
</div>
<?php else: ?>

<p class="text-muted small mb-3"><?= $total ?> producto(s) encontrados</p>

<div class="row g-3">
    <?php foreach ($productos as $prod): ?>
    <div class="col-md-4 col-lg-3 col-sm-6">
        <div class="card product-card h-100">
            <div class="product-card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="product-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <?php if (es_cliente()): ?>
                    <form method="POST" action="<?= url('wishlist/agregar/' . $prod['id_producto']) ?>" class="wishlist-form">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <button type="submit" class="btn btn-sm btn-link p-0 wishlist-btn" data-producto-id="<?= $prod['id_producto'] ?>" title="<?= in_array($prod['id_producto'], $wishlist_ids ?? []) ? 'Quitar de favoritos' : 'Agregar a favoritos' ?>">
                            <i class="bi bi-heart<?= in_array($prod['id_producto'], $wishlist_ids ?? []) ? '-fill text-danger' : '' ?> fs-5"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <a href="<?= url('producto/' . $prod['id_producto']) ?>" class="text-decoration-none">
                    <h6 class="product-name"><?= safe_string($prod['nombre']) ?></h6>
                </a>
                <?php if (!empty($prod['codigo'])): ?>
                <span class="product-code"><?= safe_string($prod['codigo']) ?></span>
                <?php endif; ?>
                <?php if (!empty($prod['descripcion_comercial'])): ?>
                <p class="product-desc"><?= safe_string(truncate($prod['descripcion_comercial'], 60)) ?></p>
                <?php endif; ?>
                <?php if (!empty($prod['familia']) || !empty($prod['linea'])): ?>
                <div class="d-flex gap-1 justify-content-center flex-wrap mb-2">
                    <?php if (!empty($prod['familia'])): ?>
                    <span class="badge bg-light text-dark small"><?= safe_string($prod['familia']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($prod['linea'])): ?>
                    <span class="badge bg-light text-dark small"><?= safe_string($prod['linea']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($prod['color'])): ?>
                    <span class="badge bg-light text-dark small"><?= safe_string($prod['color']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="product-price"><?= format_money($prod['precio_venta'] ?? 0) ?></div>
                <?php if (isset($prod['stock_actual']) && $prod['stock_actual'] > 0): ?>
                <span class="stock-badge bg-success text-white"><?= $prod['stock_actual'] ?> disp.</span>
                <?php elseif (isset($prod['stock_actual']) && $prod['stock_actual'] == 0): ?>
                <span class="stock-badge bg-secondary text-white">Agotado</span>
                <?php endif; ?>
            </div>
            <div class="product-card-footer">
                <?php if (!isset($prod['stock_actual']) || $prod['stock_actual'] > 0): ?>
                <form method="POST" action="<?= url('carrito/agregar') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id_producto" value="<?= $prod['id_producto'] ?>">
                    <input type="hidden" name="precio_unitario" value="<?= $prod['precio_venta'] ?? 0 ?>">
                    <div class="d-flex gap-1 mb-2">
                        <a href="<?= url('producto/' . $prod['id_producto']) ?>" class="btn btn-sm btn-outline-info flex-fill"><i class="bi bi-eye"></i> Detalle</a>
                    </div>
                    <div class="input-group input-group-sm">
                        <button type="button" class="btn btn-outline-secondary qty-btn" onclick="this.nextElementSibling.stepDown();this.nextElementSibling.dispatchEvent(new Event('change'))">−</button>
                        <input type="number" name="cantidad" class="form-control text-center qty-input" value="1" min="1" max="<?= $prod['stock_actual'] ?: 999 ?>" style="width: 50px;">
                        <button type="button" class="btn btn-outline-secondary qty-btn" onclick="this.previousElementSibling.stepUp();this.previousElementSibling.dispatchEvent(new Event('change'))">+</button>
                        <button type="submit" class="btn btn-dark"><i class="bi bi-cart-plus"></i> Comprar</button>
                    </div>
                </form>
                <?php else: ?>
                <div class="d-flex gap-1">
                    <a href="<?= url('producto/' . $prod['id_producto']) ?>" class="btn btn-sm btn-outline-info flex-fill"><i class="bi bi-eye"></i> Detalle</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Paginación -->
<?php if ($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= url('catalogo?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="<?= url('catalogo?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= url('catalogo?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>

<?php if (es_cliente()): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wishlist-form').forEach(function(form) {
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
                        icon.className = 'bi bi-heart-fill text-danger fs-5';
                        btn.title = 'Quitar de favoritos';
                    } else {
                        icon.className = 'bi bi-heart fs-5';
                        btn.title = 'Agregar a favoritos';
                    }
                    var badge = document.querySelector('.wishlist-count-badge');
                    if (badge) badge.textContent = data.count;
                }
            })
            .catch(function() {
                if (typeof showToast !== 'undefined') {
                    showToast('Error al actualizar favoritos', 'error');
                }
            });
        });
    });
});
</script>
<?php endif; ?>

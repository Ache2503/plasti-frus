<header class="navbar navbar-dark sticky-top shadow-sm" style="background: linear-gradient(135deg, #1a1a2e, #0f3460);">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="<?= url() ?>">
        <i class="bi bi-gear-fill"></i> <strong>Plasti Frus</strong>
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" id="sidebarToggle">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="w-100"></div>
    <div class="navbar-nav">
        <div class="nav-item text-nowrap d-flex align-items-center">
            <span class="text-white-50 me-2 small">
                <i class="bi bi-person-circle"></i>
                <?= safe_string($_SESSION['empleado_nombre'] ?? $_SESSION['user_name'] ?? '') ?>
            </span>
            <?php if (es_cliente()): ?>
            <?php $cartCountH = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad')); ?>
            <a class="nav-link px-2 text-white-50 position-relative" href="<?= url('carrito') ?>" title="Carrito">
                <i class="bi bi-cart3"></i>
                <?php if ($cartCountH > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: .6rem;"><?= $cartCountH ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <a class="nav-link px-2 text-white-50" href="<?= url('profile') ?>" title="Perfil">
                <i class="bi bi-gear"></i>
            </a>
            <a class="nav-link px-2 text-white-50" href="<?= url('logout') ?>" title="Cerrar Sesión">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</header>

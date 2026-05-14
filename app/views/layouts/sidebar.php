<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <span class="rol-badge"><i class="bi bi-shield-fill-check"></i> <?= safe_string(user_rol_nombre()) ?></span>

        <?php if (es_cliente()): ?>
        <?php $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad')); ?>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?= is_active('/home') ?: is_active('/') ?>" href="<?= url() ?>">
                    <i class="bi bi-speedometer2"></i> <span>Mi Panel</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/catalogo') ?: (strpos($_SERVER['REQUEST_URI'] ?? '', '/producto/') !== false ? 'active' : '') ?>" href="<?= url('catalogo') ?>">
                    <i class="bi bi-shop"></i> <span>Tienda</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/carrito') ?>" href="<?= url('carrito') ?>">
                    <i class="bi bi-cart3"></i> <span>Carrito</span>
                    <?php if ($cartCount > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mis-compras') ?>" href="<?= url('mis-compras') ?>">
                    <i class="bi bi-clock-history"></i> <span>Mis Compras</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/cartera') ?>" href="<?= url('cartera') ?>">
                    <i class="bi bi-wallet2"></i> <span>Mi Cartera</span>
                </a>
            </li>
            <li class="nav-item mt-2"><hr class="mx-3 my-1" style="border-color: rgba(255,255,255,.1);"></li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/profile') ?>" href="<?= url('profile') ?>">
                    <i class="bi bi-gear"></i> <span>Perfil</span>
                </a>
            </li>
        </ul>

        <?php else: ?>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?= is_active('/home') ?: is_active('/') ?>" href="<?= url() ?>">
                    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                </a>
            </li>
            <?php if (puede_acceder('materiales')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/materiales') ?>" href="<?= url('materiales') ?>">
                    <i class="bi bi-boxes"></i> <span>Materiales</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('productos')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/productos') ?>" href="<?= url('productos') ?>">
                    <i class="bi bi-cube"></i> <span>Productos</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('recetas')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/recetas') ?>" href="<?= url('recetas') ?>">
                    <i class="bi bi-journal-text"></i> <span>Recetas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('ordenes')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/ordenes') ?>" href="<?= url('ordenes') ?>">
                    <i class="bi bi-clipboard-check"></i> <span>Órdenes</span>
                </a>
            </li>
            <?php if (es_operador()): ?>
            <?php
                $uidOp = (int) $_SESSION['user_id'];
                $dbOp = \App\Core\Database::getInstance();
                $notifOpNoLeidas = (int) ($dbOp->fetchOne("SELECT COUNT(*) as c FROM notificaciones_operador WHERE id_operador = :id AND leida = 0", ['id' => $uidOp])['c'] ?? 0);
            ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mis-ordenes') ?>" href="<?= url('mis-ordenes') ?>">
                    <i class="bi bi-list-check"></i> <span>Mis Órdenes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url() ?>">
                    <i class="bi bi-bell"></i> <span>Notificaciones</span>
                    <?php if ($notifOpNoLeidas > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $notifOpNoLeidas ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>
            <?php if (puede_acceder('maquinas')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/maquinas') ?>" href="<?= url('maquinas') ?>">
                    <i class="bi bi-tools"></i> <span>Máquinas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('moldes')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/moldes') ?>" href="<?= url('moldes') ?>">
                    <i class="bi bi-bounding-box-circles"></i> <span>Moldes</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item mt-2"><hr class="mx-3 my-1" style="border-color: rgba(255,255,255,.1);"></li>

            <?php if (puede_acceder('clientes')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/clientes') && !is_active('/mis-clientes') ?>" href="<?= url('clientes') ?>">
                    <i class="bi bi-people"></i> <span>Clientes</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (es_vendedor()): ?>
            <?php
                $db = \App\Core\Database::getInstance();
                $uid = (int) $_SESSION['user_id'];
                $misClientesCount = (int) $db->fetchOne("SELECT COUNT(*) as c FROM clientes WHERE id_vendedor = :uid AND activo = 1", ['uid' => $uid])['c'];
                $notifNoLeidas = vendedor_notificaciones_no_leidas($uid);
            ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mis-clientes') ?>" href="<?= url('mis-clientes') ?>">
                    <i class="bi bi-person-check"></i> <span>Mis Clientes</span>
                    <?php if ($misClientesCount > 0): ?>
                    <span class="badge bg-success ms-auto"><?= $misClientesCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mis-comisiones') ?>" href="<?= url('mis-comisiones') ?>">
                    <i class="bi bi-cash-stack"></i> <span>Mis Comisiones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= url() ?>">
                    <i class="bi bi-bell"></i> <span>Notificaciones</span>
                    <?php if ($notifNoLeidas > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $notifNoLeidas ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if (es_supervisor()): ?>
            <?php
                $uidSup = (int) $_SESSION['user_id'];
                $notifSupNoLeidas = supervisor_notificaciones_no_leidas($uidSup);
            ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= url() ?>">
                    <i class="bi bi-bell"></i> <span>Notificaciones</span>
                    <?php if ($notifSupNoLeidas > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $notifSupNoLeidas ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('contabilidad')): ?>
            <li class="nav-item mt-2"><hr class="mx-3 my-1" style="border-color: rgba(255,255,255,.1);"></li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad') ?>" href="<?= url('contabilidad') ?>">
                    <i class="bi bi-calculator"></i> <span>Contabilidad</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (in_array(user_rol(), [1, 3])): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/comisiones') ?>" href="<?= url('comisiones') ?>">
                    <i class="bi bi-cash-stack"></i> <span>Comisiones</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('proveedores')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/proveedores') ?>" href="<?= url('proveedores') ?>">
                    <i class="bi bi-truck"></i> <span>Proveedores</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('ventas')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/ventas') ?>" href="<?= url('ventas') ?>">
                    <i class="bi bi-cash-coin"></i> <span>Ventas</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item mt-2"><hr class="mx-3 my-1" style="border-color: rgba(255,255,255,.1);"></li>

            <?php if (puede_acceder('calidad')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/calidad') ?>" href="<?= url('calidad/inspecciones') ?>">
                    <i class="bi bi-clipboard-check"></i> <span>Calidad</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('kardex')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/kardex') ?>" href="<?= url('kardex') ?>">
                    <i class="bi bi-journal-text"></i> <span>Kardex</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('incidencias')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/incidencias') ?>" href="<?= url('incidencias') ?>">
                    <i class="bi bi-exclamation-triangle"></i> <span>Incidencias</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('mantenimiento')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mantenimiento') ?>" href="<?= url('mantenimiento') ?>">
                    <i class="bi bi-tools"></i> <span>Mantenimiento</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('notificaciones')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/notificaciones') ?>" href="<?= url('notificaciones') ?>">
                    <i class="bi bi-bell"></i> <span>Alertas</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('facturas')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/facturas') ?>" href="<?= url('facturas') ?>">
                    <i class="bi bi-file-text"></i> <span>Facturas</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item mt-2"><hr class="mx-3 my-1" style="border-color: rgba(255,255,255,.1);"></li>

            <?php if (in_array(user_rol(), [1, 3])): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/admin/horarios') ?>" href="<?= url('admin/horarios') ?>">
                    <i class="bi bi-clock"></i> <span>Horarios</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('usuarios')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/usuarios') ?>" href="<?= url('usuarios') ?>">
                    <i class="bi bi-people-fill"></i> <span>Usuarios</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if (puede_acceder('reportes')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/reportes') ?>" href="<?= url('reportes/kpi') ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i> <span>Reportes</span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/profile') ?>" href="<?= url('profile') ?>">
                    <i class="bi bi-gear"></i> <span>Perfil</span>
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
</nav>

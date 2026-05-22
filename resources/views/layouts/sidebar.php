<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <span class="rol-badge"><i class="bi bi-shield-fill-check"></i> <?= safe_string(user_rol_nombre()) ?></span>

        <?php if (es_cliente()): ?>
        <?php $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad')); ?>
        <?php
            $dbSide = \App\Core\Database::getInstance();
            $uidSide = (int) $_SESSION['user_id'];
            $cidSide = user_id_cliente();
            $ticketsAbiertosSide = 0;
            $notifNoLeidasSide = 0;
            $wishlistCountSide = 0;
            if ($cidSide) {
                $ticketsAbiertosSide = (int) ($dbSide->fetchOne("SELECT COUNT(*) as c FROM tickets_soporte WHERE id_cliente = :id AND estatus IN ('abierto','respondido')", ['id' => $cidSide])['c'] ?? 0);
                $notifNoLeidasSide = (int) ($dbSide->fetchOne("SELECT COUNT(*) as c FROM notificaciones_cliente WHERE id_cliente = :id AND leida = 0", ['id' => $cidSide])['c'] ?? 0);
                $wishlistCountSide = (int) ($dbSide->fetchOne("SELECT COUNT(*) as c FROM wishlist WHERE id_cliente = :id", ['id' => $cidSide])['c'] ?? 0);
            }
        ?>
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
                <a class="nav-link <?= is_active('/mis-pedidos') ?>" href="<?= url('mis-pedidos') ?>">
                    <i class="bi bi-box-seam"></i> <span>Mis Pedidos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mis-compras') ?>" href="<?= url('mis-compras') ?>">
                    <i class="bi bi-clock-history"></i> <span>Mis Compras</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/tickets') ?>" href="<?= url('tickets') ?>">
                    <i class="bi bi-headset"></i> <span>Tickets</span>
                    <?php if ($ticketsAbiertosSide > 0): ?>
                    <span class="badge bg-warning ms-auto"><?= $ticketsAbiertosSide ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/wishlist') ?>" href="<?= url('wishlist') ?>">
                    <i class="bi bi-heart"></i> <span>Favoritos</span>
                    <?php if ($wishlistCountSide > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $wishlistCountSide ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/cartera') ?>" href="<?= url('cartera') ?>">
                    <i class="bi bi-wallet2"></i> <span>Mi Cartera</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/direcciones') ?>" href="<?= url('direcciones') ?>">
                    <i class="bi bi-geo-alt"></i> <span>Direcciones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/notificaciones-cliente') ?>" href="<?= url('notificaciones-cliente') ?>">
                    <i class="bi bi-bell"></i> <span>Notificaciones</span>
                    <?php if ($notifNoLeidasSide > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $notifNoLeidasSide ?></span>
                    <?php endif; ?>
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
                $inspeccionesPendSide = (int) ($dbOp->fetchOne("SELECT COUNT(*) as c FROM inspecciones_calidad WHERE resultado IS NULL OR resultado = ''")['c'] ?? 0);
                $incidenciasAbiertasSide = (int) ($dbOp->fetchOne("SELECT COUNT(*) as c FROM incidencias_produccion WHERE estatus IS NULL OR estatus != 'cerrada'")['c'] ?? 0);
            ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/maquinas/estado') ?>" href="<?= url('maquinas/estado') ?>">
                    <i class="bi bi-tools"></i> <span>Estado Máquinas</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mis-ordenes') ?>" href="<?= url('mis-ordenes') ?>">
                    <i class="bi bi-list-check"></i> <span>Mis Órdenes</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/calidad/pendientes') ?>" href="<?= url('calidad/pendientes') ?>">
                    <i class="bi bi-clipboard-check"></i> <span>Inspecciones</span>
                    <?php if ($inspeccionesPendSide > 0): ?>
                    <span class="badge bg-success ms-auto"><?= $inspeccionesPendSide ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/incidencias') ?>" href="<?= url('incidencias') ?>">
                    <i class="bi bi-exclamation-triangle"></i> <span>Incidencias</span>
                    <?php if ($incidenciasAbiertasSide > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $incidenciasAbiertasSide ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/bitacora') ?>" href="<?= url('bitacora') ?>">
                    <i class="bi bi-journal-text"></i> <span>Bitácora</span>
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
                $mensajesNoLeidos = (int) $db->fetchOne("SELECT COUNT(*) as c FROM mensajes WHERE para_user_id = :uid AND leido = 0", ['uid' => $uid])['c'];
                $actividadesPend = (int) $db->fetchOne("SELECT COUNT(*) as c FROM actividades WHERE id_vendedor = :uid AND estado = 'pendiente' AND fecha_hora >= NOW()", ['uid' => $uid])['c'];
            ?>
            <?php $oppAbiertas = contar_oportunidades_abiertas(); ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/pipeline') ?>" href="<?= url('pipeline') ?>">
                    <i class="bi bi-funnel"></i> <span>Pipeline</span>
                    <?php if ($oppAbiertas > 0): ?>
                    <span class="badge bg-primary ms-auto"><?= $oppAbiertas ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mis-clientes') ?>" href="<?= url('mis-clientes') ?>">
                    <i class="bi bi-person-check"></i> <span>Mis Clientes</span>
                    <?php if ($misClientesCount > 0): ?>
                    <span class="badge bg-success ms-auto"><?= $misClientesCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/agenda') ?>" href="<?= url('agenda') ?>">
                    <i class="bi bi-calendar"></i> <span>Agenda</span>
                    <?php if ($actividadesPend > 0): ?>
                    <span class="badge bg-warning ms-auto"><?= $actividadesPend ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mis-comisiones') ?>" href="<?= url('mis-comisiones') ?>">
                    <i class="bi bi-cash-stack"></i> <span>Mis Comisiones</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/mensajes') ?>" href="<?= url('mensajes') ?>">
                    <i class="bi bi-envelope"></i> <span>Mensajes</span>
                    <?php if ($mensajesNoLeidos > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $mensajesNoLeidos ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/reportes-vendedor') ?>" href="<?= url('reportes-vendedor') ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i> <span>Reportes</span>
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
            <?php if (contabilidad_permiso('ver')): ?>
            <li class="nav-item mt-2"><hr class="mx-3 my-1" style="border-color: rgba(255,255,255,.1);"></li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad') && !is_active('/contabilidad/') ?>" href="<?= url('contabilidad') ?>">
                    <i class="bi bi-calculator"></i> <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/plan-cuentas') ?>" href="<?= url('contabilidad/plan-cuentas') ?>">
                    <i class="bi bi-list-ul"></i> <span>Plan de Cuentas</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/polizas') ?>" href="<?= url('contabilidad/polizas') ?>">
                    <i class="bi bi-journal-text"></i> <span>Pólizas</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/periodos') ?>" href="<?= url('contabilidad/periodos') ?>">
                    <i class="bi bi-calendar-month"></i> <span>Periodos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/balance-general') ?>" href="<?= url('contabilidad/balance-general') ?>">
                    <i class="bi bi-file-earmark-text"></i> <span>Balance General</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/estado-resultados') ?>" href="<?= url('contabilidad/estado-resultados') ?>">
                    <i class="bi bi-bar-chart"></i> <span>Estado Resultados</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/balanza') ?>" href="<?= url('contabilidad/balanza') ?>">
                    <i class="bi bi-calculator-fill"></i> <span>Balanza</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/libro-diario') ?>" href="<?= url('contabilidad/libro-diario') ?>">
                    <i class="bi bi-book"></i> <span>Libro Diario</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/libro-mayor') ?>" href="<?= url('contabilidad/libro-mayor/1') ?>">
                    <i class="bi bi-bookmark"></i> <span>Libro Mayor</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/flujo-efectivo') ?>" href="<?= url('contabilidad/flujo-efectivo') ?>">
                    <i class="bi bi-cash-coin"></i> <span>Flujo de Efectivo</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/presupuestos') ?>" href="<?= url('contabilidad/presupuestos') ?>">
                    <i class="bi bi-graph-up"></i> <span>Presupuestos</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/cierres') ?>" href="<?= url('contabilidad/cierres') ?>">
                    <i class="bi bi-lock"></i> <span>Cierres Contables</span>
                </a>
            </li>
            <?php if (contabilidad_permiso('eliminar')): ?>
            <li class="nav-item">
                <a class="nav-link <?= is_active('/contabilidad/exportar') ?>" href="<?= url('contabilidad/exportar') ?>">
                    <i class="bi bi-download"></i> <span>Exportar</span>
                </a>
            </li>
            <?php endif; ?>
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
            <li class="nav-item">
                <a class="nav-link <?= is_active('/admin/logs') ?>" href="<?= url('admin/logs') ?>">
                    <i class="bi bi-clipboard-data"></i> <span>Auditor&iacute;a</span>
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

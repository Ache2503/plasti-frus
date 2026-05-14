<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Material;
use App\Models\Producto;
use App\Models\OrdenCabe;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\Maquina;
use App\Models\User;
use App\Models\Molde;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $pageTitle = es_cliente() ? 'Mi Panel' : 'Dashboard';

        $data = [
            'pageTitle' => $pageTitle,
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
        ];

        if (es_admin()) {
            $this->adminDashboard($data);
        } elseif (es_supervisor()) {
            $this->supervisorDashboard($data);
        } elseif (es_operador()) {
            $this->operadorDashboard($data);
        } elseif (es_vendedor()) {
            $this->vendedorDashboard($data);
        } elseif (es_cliente()) {
            $this->clienteDashboard($data);
        } elseif (es_contador()) {
            $this->contadorDashboard($data);
        } else {
            $this->operadorDashboard($data);
        }
    }

    private function adminDashboard(array $data): void
    {
        $materialModel = new Material();
        $productoModel = new Producto();
        $ordenModel = new OrdenCabe();
        $clienteModel = new Cliente();
        $proveedorModel = new Proveedor();
        $maquinaModel = new Maquina();
        $userModel = new User();

        $data += [
            'total_materiales' => $materialModel->count(),
            'total_productos' => $productoModel->count(),
            'total_ordenes' => $ordenModel->count(),
            'total_clientes' => $clienteModel->count(),
            'total_proveedores' => $proveedorModel->count(),
            'total_maquinas' => $maquinaModel->count(),
            'total_usuarios' => $userModel->count(),
            'ordenes_recientes' => $ordenModel->getWithRelations(),
            'materiales_bajos' => $materialModel->getLowStock(),
        ];

        $this->view('home/admin', $data);
    }

    private function supervisorDashboard(array $data): void
    {
        $materialModel = new Material();
        $productoModel = new Producto();
        $ordenModel = new OrdenCabe();
        $maquinaModel = new Maquina();
        $clienteModel = new Cliente();
        $proveedorModel = new Proveedor();
        $userModel = new User();
        $moldeModel = new Molde();
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');

        $ordenes_hoy = $ordenModel->getByDateRange($hoy, $hoy);
        $completadas_hoy = array_filter($ordenes_hoy, fn($o) => $o['estatus'] === 'completada');
        $en_progreso_hoy = array_filter($ordenes_hoy, fn($o) => $o['estatus'] === 'en_progreso');
        $pendientes_hoy = array_filter($ordenes_hoy, fn($o) => $o['estatus'] === 'pendiente' || $o['estatus'] === null);

        $total_producido_hoy = (int) ($db->fetchOne("
            SELECT COALESCE(SUM(cantidad_real_buenas), 0) as t FROM ordenes_cabecera WHERE fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $total_planificado_hoy = (int) ($db->fetchOne("
            SELECT COALESCE(SUM(cantidad_planificada), 0) as t FROM ordenes_cabecera WHERE fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $incidencias_activas = $db->fetchAll("
            SELECT i.*, p.nombre as producto_nombre
            FROM incidencias_produccion i
            LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE i.estatus != 'cerrada'
            ORDER BY i.fecha DESC LIMIT 5
        ");

        $paros_activos = $db->fetchAll("
            SELECT bp.*, m.nombre as maquina_nombre
            FROM bitacora_paros bp
            LEFT JOIN maquinas m ON bp.id_maquina = m.id_maquina
            WHERE bp.hora_fin IS NULL
            ORDER BY bp.hora_inicio DESC LIMIT 5
        ");

        $maquinas_con_estado = $db->fetchAll("
            SELECT m.*,
                   CASE WHEN bp.id_bitacora IS NOT NULL THEN 'paro' ELSE m.estatus END as estado_real,
                   bp.motivo_paro, bp.hora_inicio as paro_desde
            FROM maquinas m
            LEFT JOIN bitacora_paros bp ON bp.id_maquina = m.id_maquina AND bp.hora_fin IS NULL
            ORDER BY m.nombre
        ");

        // ---- Supervisor notifications (system-wide alerts) ----
        $userId = (int) $_SESSION['user_id'];
        $notificaciones = supervisor_notificaciones($userId, 5);
        $notif_no_leidas = supervisor_notificaciones_no_leidas($userId);

        // ---- Active operadores (with logged-in session in last 24h) ----
        $operadores_activos = $db->fetchAll("
            SELECT u.id_usuario, e.nombre, e.apellido_paterno
            FROM usuarios u
            JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE u.id_rol = 2 AND u.activo = 1
            ORDER BY e.nombre
        ");

        // ---- Scrap/merma del día ----
        $merma_hoy = (float) ($db->fetchOne("
            SELECT COALESCE(SUM(om.cantidad_kg), 0) as t FROM ordenes_merma om
            JOIN ordenes_cabecera oc ON om.id_orden_cabe = oc.id_orden_cabe
            WHERE oc.fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $data += [
            'total_materiales' => $materialModel->count(),
            'total_productos' => $productoModel->count(),
            'total_ordenes' => $ordenModel->count(),
            'total_clientes' => $clienteModel->count(),
            'total_proveedores' => $proveedorModel->count(),
            'total_maquinas' => $maquinaModel->count(),
            'total_usuarios' => $userModel->count(),
            'total_moldes' => $moldeModel->count(),
            'ordenes_recientes' => $ordenModel->getWithRelations(),
            'materiales_bajos' => $materialModel->getLowStock(),
            'ordenes_hoy' => $ordenes_hoy,
            'completadas_hoy' => $completadas_hoy,
            'en_progreso_hoy' => $en_progreso_hoy,
            'pendientes_hoy' => $pendientes_hoy,
            'total_producido_hoy' => $total_producido_hoy,
            'total_planificado_hoy' => $total_planificado_hoy,
            'incidencias_activas' => $incidencias_activas,
            'total_incidencias_activas' => count($incidencias_activas),
            'paros_activos' => $paros_activos,
            'total_paros_activos' => count($paros_activos),
            'maquinas_con_estado' => $maquinas_con_estado,
            'fecha_hoy' => $hoy,
            'notificaciones' => $notificaciones,
            'notificaciones_no_leidas' => $notif_no_leidas,
            'operadores_activos' => $operadores_activos,
            'merma_hoy' => $merma_hoy,
        ];

        $this->view('home/supervisor', $data);
    }

    private function operadorDashboard(array $data): void
    {
        $productoModel = new Producto();
        $ordenModel = new OrdenCabe();
        $maquinaModel = new Maquina();
        $moldeModel = new Molde();
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $turnos = ['matutino', 'vespertino', 'nocturno'];
        $turnoActual = $_SESSION['operador_turno_override'] ?? $turnos[(int) ((int) date('H') / 8)] ?? 'matutino';

        $ordenes_hoy = $ordenModel->getByDateRange($hoy, $hoy);
        $ordenes_mi_turno = array_filter($ordenes_hoy, fn($o) => $o['turno'] === $turnoActual);
        $maquinas_activas = $maquinaModel->getActiveMachines();

        $maquinas_con_estado = $db->fetchAll("
            SELECT m.*,
                   CASE WHEN bp.id_bitacora IS NOT NULL THEN 'paro' ELSE m.estatus END as estado_real,
                   bp.motivo_paro, bp.hora_inicio as paro_desde
            FROM maquinas m
            LEFT JOIN bitacora_paros bp ON bp.id_maquina = m.id_maquina AND bp.hora_fin IS NULL
            ORDER BY m.nombre
        ");

        $incidencias_hoy = $db->fetchAll("
            SELECT i.*, p.nombre as producto_nombre
            FROM incidencias_produccion i
            LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE DATE(i.fecha) = :fecha AND i.estatus != 'cerrada'
            ORDER BY i.fecha DESC LIMIT 5
        ", ['fecha' => $hoy]);

        $paros_activos = $db->fetchAll("
            SELECT bp.*, m.nombre as maquina_nombre
            FROM bitacora_paros bp
            LEFT JOIN maquinas m ON bp.id_maquina = m.id_maquina
            WHERE bp.hora_fin IS NULL AND bp.fecha = :fecha
            ORDER BY bp.hora_inicio DESC LIMIT 5
        ", ['fecha' => $hoy]);

        $pendientes_completar = $db->fetchAll("
            SELECT o.*, p.nombre as producto_nombre
            FROM ordenes_cabecera o
            LEFT JOIN productos p ON o.id_producto = p.id_producto
            WHERE o.fecha = :fecha AND o.turno = :turno
              AND (o.cantidad_real_buenas IS NULL OR o.cantidad_real_buenas = 0)
            ORDER BY o.id_orden_cabe DESC LIMIT 5
        ", ['fecha' => $hoy, 'turno' => $turnoActual]);

        $total_incidencias_hoy = count($incidencias_hoy);
        $total_paros_activos = count($paros_activos);

        $resumen_turno = $db->fetchOne("
            SELECT COUNT(*) as total_ordenes,
                   COALESCE(SUM(cantidad_real_buenas), 0) as total_producido,
                   COALESCE(SUM(cantidad_planificada), 0) as total_planificado
            FROM ordenes_cabecera
            WHERE fecha = :fecha AND turno = :turno
        ", ['fecha' => $hoy, 'turno' => $turnoActual]);

        $mis_completadas = array_filter($ordenes_mi_turno, fn($o) => $o['cantidad_real_buenas'] !== null && $o['cantidad_real_buenas'] > 0);
        $mi_producido = array_sum(array_column($mis_completadas, 'cantidad_real_buenas'));

        $userId = (int) $_SESSION['user_id'];
        $notificaciones_op = $db->fetchAll("
            SELECT * FROM notificaciones_operador WHERE id_operador = :id ORDER BY created_at DESC LIMIT 5
        ", ['id' => $userId]);
        $notificaciones_op_no_leidas = (int) ($db->fetchOne("
            SELECT COUNT(*) as c FROM notificaciones_operador WHERE id_operador = :id AND leida = 0
        ", ['id' => $userId])['c'] ?? 0);

        $data += [
            'total_ordenes' => $ordenModel->count(),
            'total_maquinas' => $maquinaModel->count(),
            'total_moldes' => $moldeModel->count(),
            'ordenes_hoy' => $ordenes_hoy,
            'ordenes_mi_turno' => $ordenes_mi_turno,
            'maquinas_activas' => $maquinas_activas,
            'maquinas_con_estado' => $maquinas_con_estado,
            'incidencias_hoy' => $incidencias_hoy,
            'paros_activos' => $paros_activos,
            'pendientes_completar' => $pendientes_completar,
            'total_incidencias_hoy' => $total_incidencias_hoy,
            'total_paros_activos' => $total_paros_activos,
            'turno_actual' => $turnoActual,
            'fecha_hoy' => $hoy,
            'resumen_turno' => $resumen_turno,
            'mis_completadas_count' => count($mis_completadas),
            'mi_producido' => $mi_producido,
            'notificaciones_op' => $notificaciones_op,
            'notificaciones_op_no_leidas' => $notificaciones_op_no_leidas,
        ];

        $this->view('home/operador', $data);
    }

    private function vendedorDashboard(array $data): void
    {
        $db = \App\Core\Database::getInstance();
        $productoModel = new Producto();
        $clienteModel = new Cliente();
        $userId = (int) $_SESSION['user_id'];

        $mis_clientes = $db->fetchAll("
            SELECT id_cliente FROM clientes WHERE id_vendedor = :uid AND activo = 1
        ", ['uid' => $userId]);
        $mis_ids = array_column($mis_clientes, 'id_cliente');

        $ventas_mes = $db->fetchAll("
            SELECT v.*, c.razon_social as cliente, p.nombre as producto_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
              AND MONTH(v.fecha_venta) = MONTH(CURRENT_DATE)
              AND YEAR(v.fecha_venta) = YEAR(CURRENT_DATE)
            ORDER BY v.fecha_venta DESC
        ", ['uid' => $userId, 'uid2' => $userId]);

        $top_clientes = $db->fetchAll("
            SELECT c.id_cliente, c.razon_social, COUNT(v.id_venta) as total_ventas,
                   SUM(v.cantidad_vendida * v.precio_unitario) as total_gastado
            FROM clientes c
            LEFT JOIN ventas v ON c.id_cliente = v.id_cliente
            WHERE c.id_vendedor = :uid
            GROUP BY c.id_cliente
            ORDER BY total_gastado DESC
            LIMIT 5
        ", ['uid' => $userId]);

        $total_ventas_mes = $db->fetchOne("
            SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
              AND MONTH(v.fecha_venta) = MONTH(CURRENT_DATE)
              AND YEAR(v.fecha_venta) = YEAR(CURRENT_DATE)
        ", ['uid' => $userId, 'uid2' => $userId]);

        $ventas_recientes = $db->fetchAll("
            SELECT v.*, c.razon_social as cliente, p.nombre as producto_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
            ORDER BY v.fecha_venta DESC
            LIMIT 10
        ", ['uid' => $userId, 'uid2' => $userId]);

        // --- Commission summary ---
        $comisiones_resumen = $db->fetchOne("
            SELECT COALESCE(SUM(CASE WHEN estatus = 'pendiente' THEN monto_comision ELSE 0 END), 0) as pendiente,
                   COALESCE(SUM(CASE WHEN estatus = 'pagada' THEN monto_comision ELSE 0 END), 0) as pagado
            FROM comisiones_vendedor
            WHERE id_vendedor = :uid
        ", ['uid' => $userId]);

        // --- Monthly comparison (last 6 months) ---
        $ventas_mensuales = $db->fetchAll("
            SELECT DATE_FORMAT(v.fecha_venta, '%Y-%m') as mes,
                   COUNT(*) as total_ventas,
                   COALESCE(SUM(v.cantidad_vendida * v.precio_unitario), 0) as monto
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
              AND v.fecha_venta >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
            GROUP BY mes
            ORDER BY mes DESC
        ", ['uid' => $userId, 'uid2' => $userId]);

        // --- Top products this vendedor sells ---
        $top_productos = $db->fetchAll("
            SELECT p.nombre, COUNT(v.id_venta) as veces_vendido,
                   SUM(v.cantidad_vendida) as unidades_vendidas,
                   COALESCE(SUM(v.cantidad_vendida * v.precio_unitario), 0) as total_generado
            FROM ventas v
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
            GROUP BY p.id_producto
            ORDER BY total_generado DESC
            LIMIT 5
        ", ['uid' => $userId, 'uid2' => $userId]);

        // --- New clients this month ---
        $nuevos_clientes_mes = $db->fetchOne("
            SELECT COUNT(*) as total
            FROM clientes
            WHERE id_vendedor = :uid
              AND activo = 1
              AND MONTH(created_at) = MONTH(CURRENT_DATE)
              AND YEAR(created_at) = YEAR(CURRENT_DATE)
        ", ['uid' => $userId])['total'] ?? 0;

        $data += [
            'total_productos' => $productoModel->count(),
            'total_clientes' => count($mis_ids),
            'ventas_mes' => $ventas_mes,
            'total_ventas_mes' => $total_ventas_mes,
            'top_clientes' => $top_clientes,
            'ventas_recientes' => $ventas_recientes,
            'comisiones_resumen' => $comisiones_resumen,
            'ventas_mensuales' => $ventas_mensuales,
            'top_productos' => $top_productos,
            'nuevos_clientes_mes' => $nuevos_clientes_mes,
            'notificaciones' => vendedor_notificaciones($userId, 5),
            'notificaciones_no_leidas' => vendedor_notificaciones_no_leidas($userId),
        ];

        $this->view('home/vendedor', $data);
    }

    private function clienteDashboard(array $data): void
    {
        $db = \App\Core\Database::getInstance();
        $idCliente = user_id_cliente();
        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));

        $cliente = null;
        $mis_ventas = [];
        $total_compras = ['total' => 0, 'monto' => 0];
        $vendedores = [];
        $vendedor_asignado = null;
        $solicitudes = [];
        $ids_con_solicitud = [];
        $productos = [];

        if ($idCliente) {
            $clienteModel = new Cliente();
            $cliente = $clienteModel->find($idCliente);

            $mis_ventas = $db->fetchAll("
                SELECT v.*, p.nombre as producto_nombre, t.folio_unico
                FROM ventas v
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                LEFT JOIN tickets t ON t.id_venta = v.id_venta
                WHERE v.id_cliente = :id
                ORDER BY v.fecha_venta DESC
                LIMIT 5
            ", ['id' => $idCliente]);

            $total_compras = $db->fetchOne("
                SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto
                FROM ventas
                WHERE id_cliente = :id
            ", ['id' => $idCliente]) ?: ['total' => 0, 'monto' => 0];

            $idRolVendedor = (int) $db->fetchOne("SELECT id_rol FROM roles WHERE nombre = 'Vendedor' LIMIT 1")['id_rol'] ?: ROL_VENDEDOR;
            $vendedores = $db->fetchAll("
                SELECT u.id_usuario, u.nombre_usuario, e.nombre, e.apellido_paterno
                FROM usuarios u
                LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
                WHERE u.id_rol = :rol AND u.activo = 1
                ORDER BY e.nombre
            ", ['rol' => $idRolVendedor]);

            if ($cliente && !empty($cliente['id_vendedor'])) {
                $vendedor_asignado = $db->fetchOne("
                    SELECT u.id_usuario, u.nombre_usuario, e.nombre, e.apellido_paterno
                    FROM usuarios u
                    LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
                    WHERE u.id_usuario = :id
                ", ['id' => $cliente['id_vendedor']]);
            }

            $solicitudes = $db->fetchAll("
                SELECT s.*, v.cantidad_vendida, v.precio_unitario, p.nombre as producto_nombre, v.fecha_venta
                FROM solicitudes_factura s
                LEFT JOIN ventas v ON s.id_venta = v.id_venta
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                WHERE s.id_cliente = :id
                ORDER BY s.fecha_solicitud DESC
            ", ['id' => $idCliente]);

            $ids_con_solicitud = array_column(
                $db->fetchAll("SELECT id_venta FROM solicitudes_factura WHERE id_cliente = :id", ['id' => $idCliente]),
                'id_venta'
            );
        }

        $db->query("SET SESSION group_concat_max_len = 100000");

        $mis_pedidos = $db->fetchAll("
            SELECT p.*,
                   (SELECT COUNT(*) FROM ventas v2 WHERE v2.id_pedido = p.id_pedido) as total_productos,
                   (SELECT GROUP_CONCAT(CONCAT(pr.nombre, ' x', v3.cantidad_vendida) SEPARATOR ', ')
                    FROM ventas v3
                    LEFT JOIN productos pr ON v3.id_producto = pr.id_producto
                    WHERE v3.id_pedido = p.id_pedido) as productos_resumen
            FROM pedidos p
            WHERE p.id_cliente = :id
            ORDER BY p.created_at DESC
            LIMIT 10
        ", ['id' => $idCliente]);

        $historialPedidos = [];
        if (!empty($mis_pedidos)) {
            $ids = array_column($mis_pedidos, 'id_pedido');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $historialRows = $db->fetchAll(
                "SELECT * FROM pedidos_historial WHERE id_pedido IN ({$placeholders}) ORDER BY id_pedido, created_at ASC",
                $ids
            );
            foreach ($historialRows as $h) {
                $historialPedidos[$h['id_pedido']][] = $h;
            }
        }

        $productos = $db->fetchAll("
            SELECT * FROM productos
            WHERE publicar_web = 1 OR publicar_web IS NULL
            ORDER BY nombre
            LIMIT 12
        ");

        $data += [
            'cliente' => $cliente,
            'mis_ventas' => $mis_ventas,
            'mis_pedidos' => $mis_pedidos,
            'historial_pedidos' => $historialPedidos,
            'total_compras' => $total_compras,
            'vendedores' => $vendedores,
            'vendedor_asignado' => $vendedor_asignado,
            'solicitudes' => $solicitudes,
            'ids_con_solicitud' => $ids_con_solicitud,
            'productos' => $productos,
            'carrito_count' => $carritoCount,
            'db' => $db,
        ];

        $this->view('home/cliente', $data);
    }

    private function contadorDashboard(array $data): void
    {
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $mes = date('m');
        $anio = date('Y');

        $total_cuentas = $db->fetchOne("SELECT COUNT(*) as t FROM plan_cuentas WHERE activo = 1")['t'] ?? 0;
        $total_polizas = $db->fetchOne("SELECT COUNT(*) as t FROM polizas WHERE estatus = 'activo'")['t'] ?? 0;
        $total_polizas_mes = $db->fetchOne("
            SELECT COUNT(*) as t FROM polizas
            WHERE MONTH(fecha) = :m AND YEAR(fecha) = :y AND estatus = 'activo'
        ", ['m' => $mes, 'y' => $anio])['t'] ?? 0;
        $total_facturas_pendientes = $db->fetchOne(
            "SELECT COUNT(*) as t FROM solicitudes_factura WHERE estatus = 'pendiente'"
        )['t'] ?? 0;

        $ultimas_polizas = $db->fetchAll("
            SELECT p.*, u.nombre_usuario
            FROM polizas p
            LEFT JOIN usuarios u ON p.created_by = u.id_usuario
            WHERE p.estatus = 'activo'
            ORDER BY p.fecha DESC, p.id_poliza DESC LIMIT 8
        ");

        $periodo_actual = $db->fetchOne("
            SELECT * FROM periodos_contables WHERE mes = :m AND anio = :y
        ", ['m' => $mes, 'y' => $anio]);

        $data += [
            'total_cuentas' => $total_cuentas,
            'total_polizas' => $total_polizas,
            'total_polizas_mes' => $total_polizas_mes,
            'total_facturas_pendientes' => $total_facturas_pendientes,
            'ultimas_polizas' => $ultimas_polizas,
            'periodo_actual' => $periodo_actual,
            'fecha_hoy' => $hoy,
        ];

        $this->view('home/contador', $data);
    }

    public function misCompras(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $db = \App\Core\Database::getInstance();
        $idCliente = user_id_cliente();
        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));

        $total_compras = ['total' => 0, 'monto' => 0];
        $mis_ventas = [];
        $solicitudes = [];
        $ids_con_solicitud = [];
        $cliente = null;

        if ($idCliente) {
            $clienteModel = new Cliente();
            $cliente = $clienteModel->find($idCliente);

            $total_compras = $db->fetchOne("
                SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto
                FROM ventas WHERE id_cliente = :id
            ", ['id' => $idCliente]) ?: ['total' => 0, 'monto' => 0];

            $mis_ventas = $db->fetchAll("
                SELECT v.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                       p.descripcion_comercial, p.familia, p.linea, t.folio_unico
                FROM ventas v
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                LEFT JOIN tickets t ON t.id_venta = v.id_venta
                WHERE v.id_cliente = :id
                ORDER BY v.fecha_venta DESC
            ", ['id' => $idCliente]);

            $solicitudes = $db->fetchAll("
                SELECT s.*, v.cantidad_vendida, v.precio_unitario, p.nombre as producto_nombre, v.fecha_venta
                FROM solicitudes_factura s
                LEFT JOIN ventas v ON s.id_venta = v.id_venta
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                WHERE s.id_cliente = :id
                ORDER BY s.fecha_solicitud DESC
            ", ['id' => $idCliente]);

            $ids_con_solicitud = array_column(
                $db->fetchAll("SELECT id_venta FROM solicitudes_factura WHERE id_cliente = :id", ['id' => $idCliente]),
                'id_venta'
            );
        }

        if ($idCliente) {
            $db->query("SET SESSION group_concat_max_len = 100000");
            $mis_pedidos = $db->fetchAll("
                SELECT p.*,
                       (SELECT COUNT(*) FROM ventas v2 WHERE v2.id_pedido = p.id_pedido) as total_productos,
                       (SELECT GROUP_CONCAT(CONCAT(pr.nombre, ' x', v3.cantidad_vendida) SEPARATOR ', ')
                        FROM ventas v3
                        LEFT JOIN productos pr ON v3.id_producto = pr.id_producto
                        WHERE v3.id_pedido = p.id_pedido) as productos_resumen
                FROM pedidos p
                WHERE p.id_cliente = :id
                ORDER BY p.created_at DESC
            ", ['id' => $idCliente]);
        } else {
            $mis_pedidos = [];
        }

        $data = [
            'pageTitle' => 'Mis Compras',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cliente' => $cliente,
            'mis_ventas' => $mis_ventas,
            'mis_pedidos' => $mis_pedidos,
            'total_compras' => $total_compras,
            'solicitudes' => $solicitudes,
            'ids_con_solicitud' => $ids_con_solicitud,
            'carrito_count' => $carritoCount,
            'db' => $db,
        ];

        $this->view('home/mis_compras', $data);
    }

    public function detalleCompra(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $idVenta = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();
        $db = \App\Core\Database::getInstance();

        $venta = $db->fetchOne("
            SELECT v.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                   p.descripcion_comercial, p.familia, p.linea, p.peso_unitario_grs,
                   p.dimensiones, p.color,
                   c.razon_social, c.rfc, c.ciudad, c.estado
            FROM ventas v
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE v.id_venta = :id AND v.id_cliente = :cliente
        ", ['id' => $idVenta, 'cliente' => $idCliente]);

        if (!$venta) {
            set_flash('error', 'Compra no encontrada');
            $this->redirect('/mis-compras');
        }

        $ticket = $db->fetchOne("
            SELECT * FROM tickets WHERE id_venta = :id
        ", ['id' => $idVenta]);

        $tieneSolicitud = (bool) $db->fetchOne(
            "SELECT id_solicitud FROM solicitudes_factura WHERE id_venta = :id AND id_cliente = :cliente",
            ['id' => $idVenta, 'cliente' => $idCliente]
        );

        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));

        $data = [
            'pageTitle' => 'Detalle de Compra #' . $idVenta,
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'venta' => $venta,
            'ticket' => $ticket,
            'tiene_solicitud' => $tieneSolicitud,
            'carrito_count' => $carritoCount,
        ];

        $this->view('home/detalle_compra', $data);
    }

    public function solicitarFactura(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Solo clientes pueden solicitar facturas');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $idCliente = user_id_cliente();
        $idVenta = $params['id'];
        $db = \App\Core\Database::getInstance();

        $venta = $db->fetchOne("SELECT * FROM ventas WHERE id_venta = :id AND id_cliente = :cliente", [
            'id' => $idVenta, 'cliente' => $idCliente
        ]);
        if (!$venta) {
            set_flash('error', 'Venta no encontrada o no te pertenece');
            $this->redirect('/');
        }

        if ($venta['estatus'] !== 'completado') {
            set_flash('error', 'Solo puedes solicitar factura para ventas completadas');
            $this->redirect('/');
        }

        $existe = $db->fetchOne("SELECT id_solicitud FROM solicitudes_factura WHERE id_venta = :id AND id_cliente = :cliente", [
            'id' => $idVenta, 'cliente' => $idCliente
        ]);
        if ($existe) {
            set_flash('error', 'Ya solicitaste factura para esta compra');
            $this->redirect('/');
        }

        $db->insert('solicitudes_factura', [
            'id_cliente' => $idCliente,
            'id_venta' => $idVenta,
            'estatus' => 'pendiente',
        ]);
        set_flash('success', 'Factura solicitada correctamente');
        $this->redirect('/');
    }

    public function cancelarFactura(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $idCliente = user_id_cliente();
        $db = \App\Core\Database::getInstance();
        $solicitud = $db->fetchOne(
            "SELECT id_solicitud FROM solicitudes_factura WHERE id_solicitud = :id AND id_cliente = :cliente",
            ['id' => $params['id'], 'cliente' => $idCliente]
        );
        if (!$solicitud) {
            set_flash('error', 'Solicitud no encontrada');
            $this->redirect('/');
        }
        $db->delete('solicitudes_factura', 'id_solicitud = :id', ['id' => $params['id']]);
        set_flash('success', 'Solicitud cancelada');
        $this->redirect('/');
    }

    public function reportarParo(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $db = \App\Core\Database::getInstance();
        $horaInicio = $this->postParam('hora_inicio') ?: date('H:i');
        $db->insert('bitacora_paros', [
            'id_maquina' => $this->postParam('id_maquina'),
            'fecha' => date('Y-m-d'),
            'hora_inicio' => $horaInicio,
            'hora_fin' => null,
            'duracion_paro' => 0,
            'motivo_paro' => $this->postParam('motivo_paro'),
            'operador' => (string) ($_SESSION['user_id'] ?? ''),
            'estatus' => 'activo',
        ]);
        registrar_log('reportar_paro', 'maquina', $this->postParam('id_maquina'), $this->postParam('motivo_paro'));

        $operadores = $db->fetchAll("SELECT id_usuario FROM usuarios WHERE id_rol = 2 AND activo = 1");
        foreach ($operadores as $op) {
            notificar_operador((int) $op['id_usuario'], 'paro', 'Paro reportado',
                "Máquina #{$this->postParam('id_maquina')}: {$this->postParam('motivo_paro')}", $this->postParam('id_maquina'));
        }

        $supervisores = $db->fetchAll("SELECT id_usuario FROM usuarios WHERE id_rol = 3 AND activo = 1");
        foreach ($supervisores as $sup) {
            notificar_supervisor((int) $sup['id_usuario'], 'paro', 'Paro reportado',
                "Máquina #{$this->postParam('id_maquina')}: {$this->postParam('motivo_paro')}", $this->postParam('id_maquina'));
        }

        set_flash('success', 'Paro reportado correctamente');
        $this->redirect('/');
    }

    public function setTurno(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $turno = $this->postParam('turno');
        if (in_array($turno, ['matutino', 'vespertino', 'nocturno', 'auto'])) {
            if ($turno === 'auto') {
                unset($_SESSION['operador_turno_override']);
            } else {
                $_SESSION['operador_turno_override'] = $turno;
            }
        }
        set_flash('success', 'Turno actualizado');
        $this->redirect('/');
    }

    public function marcarNotificacionesSupervisor(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $db = \App\Core\Database::getInstance();
        $db->query("UPDATE notificaciones_supervisor SET leida = 1 WHERE id_supervisor = :id", [
            'id' => (int) $_SESSION['user_id'],
        ]);
        set_flash('success', 'Notificaciones marcadas como leídas');
        $this->redirect('/');
    }

    public function marcarNotificacionesOperador(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $db = \App\Core\Database::getInstance();
        $db->query("UPDATE notificaciones_operador SET leida = 1 WHERE id_operador = :id", [
            'id' => (int) $_SESSION['user_id'],
        ]);
        set_flash('success', 'Notificaciones marcadas como leídas');
        $this->redirect('/');
    }

    public function asignarVendedor(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $idCliente = user_id_cliente();
        $idVendedor = $this->postParam('id_vendedor');
        if (!$idCliente || !$idVendedor) {
            set_flash('error', 'Datos inválidos');
            $this->redirect('/');
        }
        $db = \App\Core\Database::getInstance();
        $db->update('clientes', ['id_vendedor' => $idVendedor], 'id_cliente = :id', ['id' => $idCliente]);
        registrar_log('asignar_vendedor', 'cliente', $idCliente, "Vendedor asignado: {$idVendedor}");
        set_flash('success', 'Vendedor asignado correctamente');
        $this->redirect('/');
    }
}

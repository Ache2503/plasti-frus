<?php
namespace App\Http\Controllers\Dashboard;

use App\Core\Controller;
use App\Core\Database;
use App\Services\NotificacionService;
use App\Repositories\ClienteRepository;
use App\Repositories\UserRepository;

class HomeController extends Controller
{
    private Database $db;
    private ClienteRepository $clienteRepository;
    private UserRepository $userRepository;
    private NotificacionService $notificacionService;

    public function __construct(
        ?ClienteRepository $clienteRepository = null,
        ?UserRepository $userRepository = null,
        ?NotificacionService $notificacionService = null
    ) {
        $this->db = Database::getInstance();
        $this->clienteRepository = $clienteRepository ?? new ClienteRepository();
        $this->userRepository = $userRepository ?? new UserRepository();
        $this->notificacionService = $notificacionService ?? new NotificacionService();
    }

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
        $data += [
            'total_materiales' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM materiales")['t'] ?? 0),
            'total_productos' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM productos")['t'] ?? 0),
            'total_ordenes' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM ordenes_cabecera")['t'] ?? 0),
            'total_clientes' => $this->clienteRepository->count(),
            'total_proveedores' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM proveedores")['t'] ?? 0),
            'total_maquinas' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM maquinas")['t'] ?? 0),
            'total_usuarios' => $this->userRepository->count(),
            'ordenes_recientes' => $this->db->fetchAll("
                SELECT oc.*,
                       p.nombre as producto_nombre, p.codigo as producto_codigo,
                       m.nombre as maquina_nombre,
                       md.nombre_molde as molde_nombre,
                       rc.version as receta_version
                FROM ordenes_cabecera oc
                LEFT JOIN productos p ON oc.id_producto = p.id_producto
                LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
                LEFT JOIN moldes md ON oc.id_molde = md.id_molde
                LEFT JOIN recetas_cabecera rc ON oc.id_receta = rc.id_receta_cabe
                ORDER BY oc.id_orden_cabe DESC
            "),
            'materiales_bajos' => $this->db->fetchAll("
                SELECT m.*, p.razon_social as proveedor
                FROM materiales m
                LEFT JOIN proveedores p ON m.id_proveedor = p.id_proveedor
                WHERE m.stock_actual_kg <= m.punto_reorden_kg
                ORDER BY (m.stock_actual_kg / m.punto_reorden_kg) ASC
            "),
        ];

        $this->view('home/admin', $data);
    }

    private function supervisorDashboard(array $data): void
    {
        $hoy = date('Y-m-d');

        $ordenes_hoy = $this->db->fetchAll("
            SELECT oc.*, p.nombre as producto_nombre
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE oc.fecha BETWEEN :start AND :end
            ORDER BY oc.fecha DESC
        ", ['start' => $hoy, 'end' => $hoy]);

        $completadas_hoy = array_filter($ordenes_hoy, fn($o) => $o['estatus'] === 'completada');
        $en_progreso_hoy = array_filter($ordenes_hoy, fn($o) => $o['estatus'] === 'en_progreso');
        $pendientes_hoy = array_filter($ordenes_hoy, fn($o) => $o['estatus'] === 'pendiente' || $o['estatus'] === null);

        $total_producido_hoy = (int) ($this->db->fetchOne("
            SELECT COALESCE(SUM(cantidad_real_buenas), 0) as t FROM ordenes_cabecera WHERE fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $total_planificado_hoy = (int) ($this->db->fetchOne("
            SELECT COALESCE(SUM(cantidad_planificada), 0) as t FROM ordenes_cabecera WHERE fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $incidencias_activas = $this->db->fetchAll("
            SELECT i.*, p.nombre as producto_nombre
            FROM incidencias_produccion i
            LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE i.estatus != 'cerrada'
            ORDER BY i.fecha DESC LIMIT 5
        ");

        $paros_activos = $this->db->fetchAll("
            SELECT bp.*, m.nombre as maquina_nombre
            FROM bitacora_paros bp
            LEFT JOIN maquinas m ON bp.id_maquina = m.id_maquina
            WHERE bp.hora_fin IS NULL
            ORDER BY bp.hora_inicio DESC LIMIT 5
        ");

        $maquinas_con_estado = $this->db->fetchAll("
            SELECT m.*,
                   CASE WHEN bp.id_bitacora IS NOT NULL THEN 'paro' ELSE m.estatus END as estado_real,
                   bp.motivo_paro, bp.hora_inicio as paro_desde
            FROM maquinas m
            LEFT JOIN bitacora_paros bp ON bp.id_maquina = m.id_maquina AND bp.hora_fin IS NULL
            ORDER BY m.nombre
        ");

        $userId = (int) $_SESSION['user_id'];
        $notificaciones = $this->notificacionService->supervisorNotifications($userId, 5);
        $notif_no_leidas = $this->notificacionService->supervisorUnreadCount($userId);

        $operadores_activos = $this->db->fetchAll("
            SELECT u.id_usuario, e.nombre, e.apellido_paterno
            FROM usuarios u
            JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE u.id_rol = 2 AND u.activo = 1
            ORDER BY e.nombre
        ");

        $merma_hoy = (float) ($this->db->fetchOne("
            SELECT COALESCE(SUM(om.cantidad_kg), 0) as t FROM ordenes_merma om
            JOIN ordenes_cabecera oc ON om.id_orden_cabe = oc.id_orden_cabe
            WHERE oc.fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $data += [
            'total_materiales' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM materiales")['t'] ?? 0),
            'total_productos' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM productos")['t'] ?? 0),
            'total_ordenes' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM ordenes_cabecera")['t'] ?? 0),
            'total_clientes' => $this->clienteRepository->count(),
            'total_proveedores' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM proveedores")['t'] ?? 0),
            'total_maquinas' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM maquinas")['t'] ?? 0),
            'total_usuarios' => $this->userRepository->count(),
            'total_moldes' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM moldes")['t'] ?? 0),
            'ordenes_recientes' => $this->db->fetchAll("
                SELECT oc.*,
                       p.nombre as producto_nombre, p.codigo as producto_codigo,
                       m.nombre as maquina_nombre,
                       md.nombre_molde as molde_nombre,
                       rc.version as receta_version
                FROM ordenes_cabecera oc
                LEFT JOIN productos p ON oc.id_producto = p.id_producto
                LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
                LEFT JOIN moldes md ON oc.id_molde = md.id_molde
                LEFT JOIN recetas_cabecera rc ON oc.id_receta = rc.id_receta_cabe
                ORDER BY oc.id_orden_cabe DESC
            "),
            'materiales_bajos' => $this->db->fetchAll("
                SELECT m.*, p.razon_social as proveedor
                FROM materiales m
                LEFT JOIN proveedores p ON m.id_proveedor = p.id_proveedor
                WHERE m.stock_actual_kg <= m.punto_reorden_kg
                ORDER BY (m.stock_actual_kg / m.punto_reorden_kg) ASC
            "),
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
        $hoy = date('Y-m-d');
        $turnos = ['matutino', 'vespertino', 'nocturno'];
        $turnoActual = $_SESSION['operador_turno_override'] ?? $turnos[(int) ((int) date('H') / 8)] ?? 'matutino';
        $userId = (int) $_SESSION['user_id'];

        $ordenes_hoy = $this->db->fetchAll("
            SELECT oc.*, p.nombre as producto_nombre, m.nombre as maquina_nombre
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
            WHERE oc.fecha BETWEEN :start AND :end
            ORDER BY oc.fecha DESC
        ", ['start' => $hoy, 'end' => $hoy]);

        $ordenes_mi_turno = array_filter($ordenes_hoy, fn($o) => $o['turno'] === $turnoActual);
        $maquinas_activas = $this->db->fetchAll("SELECT * FROM maquinas WHERE estatus = 'activo' OR estatus IS NULL ORDER BY nombre");

        $maquinas_con_estado = $this->db->fetchAll("
            SELECT m.*,
                   CASE WHEN bp.id_bitacora IS NOT NULL THEN 'paro' ELSE COALESCE(m.estado, m.estatus, 'apagada') END as estado_real,
                   bp.motivo_paro, bp.hora_inicio as paro_desde
            FROM maquinas m
            LEFT JOIN bitacora_paros bp ON bp.id_maquina = m.id_maquina AND bp.hora_fin IS NULL
            ORDER BY m.nombre
        ");

        $incidencias_hoy = $this->db->fetchAll("
            SELECT i.*, p.nombre as producto_nombre
            FROM incidencias_produccion i
            LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE DATE(i.fecha) = :fecha AND (i.estatus IS NULL OR i.estatus != 'cerrada')
            ORDER BY i.fecha DESC LIMIT 5
        ", ['fecha' => $hoy]);

        $paros_activos = $this->db->fetchAll("
            SELECT bp.*, m.nombre as maquina_nombre
            FROM bitacora_paros bp
            LEFT JOIN maquinas m ON bp.id_maquina = m.id_maquina
            WHERE bp.hora_fin IS NULL AND bp.fecha = :fecha
            ORDER BY bp.hora_inicio DESC LIMIT 5
        ", ['fecha' => $hoy]);

        $pendientes_completar = $this->db->fetchAll("
            SELECT o.*, p.nombre as producto_nombre
            FROM ordenes_cabecera o
            LEFT JOIN productos p ON o.id_producto = p.id_producto
            WHERE o.fecha = :fecha AND o.turno = :turno
              AND (o.cantidad_real_buenas IS NULL OR o.cantidad_real_buenas = 0)
            ORDER BY o.id_orden_cabe DESC LIMIT 5
        ", ['fecha' => $hoy, 'turno' => $turnoActual]);

        $total_incidencias_hoy = count($incidencias_hoy);
        $total_paros_activos = count($paros_activos);

        $resumen_turno = $this->db->fetchOne("
            SELECT COUNT(*) as total_ordenes,
                   COALESCE(SUM(cantidad_real_buenas), 0) as total_producido,
                   COALESCE(SUM(cantidad_planificada), 0) as total_planificado
            FROM ordenes_cabecera
            WHERE fecha = :fecha AND turno = :turno
        ", ['fecha' => $hoy, 'turno' => $turnoActual]);

        $mis_completadas = array_filter($ordenes_mi_turno, fn($o) => $o['cantidad_real_buenas'] !== null && $o['cantidad_real_buenas'] > 0);
        $mi_producido = array_sum(array_column($mis_completadas, 'cantidad_real_buenas'));

        $piezas_hoy = (int) ($this->db->fetchOne("
            SELECT COALESCE(SUM(cantidad_real_buenas), 0) as t FROM ordenes_cabecera WHERE fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $total_planificado_hoy = (int) ($this->db->fetchOne("
            SELECT COALESCE(SUM(cantidad_planificada), 0) as t FROM ordenes_cabecera WHERE fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $paros_hoy = $this->db->fetchAll("SELECT * FROM bitacora_paros WHERE fecha = :fecha", ['fecha' => $hoy]);
        $minutos_paro = 0;
        foreach ($paros_hoy as $p) {
            if ($p['hora_fin'] && $p['hora_inicio']) {
                $inicio = strtotime($p['fecha'] . ' ' . $p['hora_inicio']);
                $fin = strtotime($p['fecha'] . ' ' . $p['hora_fin']);
                $minutos_paro += max(0, ($fin - $inicio) / 60);
            } elseif ($p['hora_inicio']) {
                $inicio = strtotime($p['fecha'] . ' ' . $p['hora_inicio']);
                $minutos_paro += max(0, (time() - $inicio) / 60);
            }
        }
        $minutos_disponibles = 480;
        $eficiencia = $minutos_disponibles > 0 ? round(max(0, ($minutos_disponibles - $minutos_paro) / $minutos_disponibles * 100), 1) : 0;

        $rechazos_hoy = (int) ($this->db->fetchOne("
            SELECT COALESCE(SUM(cantidad_rechazada), 0) as t FROM rechazos_calidad WHERE fecha = :fecha
        ", ['fecha' => $hoy])['t'] ?? 0);

        $tasa_defectos = $piezas_hoy > 0 ? round(($rechazos_hoy / $piezas_hoy) * 100, 2) : 0;
        $ordenes_activas = count(array_filter($ordenes_hoy, fn($o) => ($o['estatus'] ?? 'pendiente') === 'en_progreso'));

        $mantenimiento_proximo = $this->db->fetchAll("
            SELECT mm.*, m.nombre as maquina_nombre
            FROM mantenimientos_maquinas mm
            LEFT JOIN maquinas m ON mm.id_maquina = m.id_maquina
            WHERE mm.fecha_mantenimiento >= :fecha AND mm.fecha_mantenimiento <= DATE_ADD(:fecha2, INTERVAL 7 DAY)
            ORDER BY mm.fecha_mantenimiento ASC LIMIT 5
        ", ['fecha' => $hoy, 'fecha2' => $hoy]);

        $inspecciones_pendientes = $this->db->fetchAll("
            SELECT i.*, p.nombre as producto_nombre
            FROM inspecciones_calidad i
            LEFT JOIN productos p ON i.id_producto = p.id_producto
            WHERE i.resultado IS NULL OR i.resultado = ''
            ORDER BY i.fecha_inspeccion ASC LIMIT 5
        ");

        $incidencias_abiertas = $this->db->fetchAll("
            SELECT i.*, p.nombre as producto_nombre
            FROM incidencias_produccion i
            LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE i.estatus IS NULL OR i.estatus != 'cerrada'
            ORDER BY i.fecha DESC LIMIT 5
        ");

        $notificaciones_op = $this->notificacionService->operadorNotifications($userId, 5);
        $notificaciones_op_no_leidas = $this->notificacionService->operadorUnreadCount($userId);

        $ordenes_con_avance = [];
        foreach ($ordenes_mi_turno as $o) {
            $plan = (int) ($o['cantidad_planificada'] ?: 1);
            $real = (int) ($o['cantidad_real_buenas'] ?: 0);
            $o['porcentaje_avance'] = $plan > 0 ? round(($real / $plan) * 100, 0) : 0;
            $ordenes_con_avance[] = $o;
        }

        $produccion_semanal = $this->db->fetchAll("
            SELECT oc.fecha, COALESCE(SUM(oc.cantidad_real_buenas), 0) as producido,
                   COALESCE(SUM(oc.cantidad_planificada), 0) as planificado
            FROM ordenes_cabecera oc
            WHERE oc.fecha >= DATE_SUB(:fecha, INTERVAL 6 DAY) AND oc.fecha <= :fecha2
            GROUP BY oc.fecha ORDER BY oc.fecha ASC
        ", ['fecha' => $hoy, 'fecha2' => $hoy]);

        $alertas_operador = [];
        if (!empty($mantenimiento_proximo)) {
            $alertas_operador[] = [
                'tipo' => 'info',
                'icono' => 'bi-tools',
                'mensaje' => count($mantenimiento_proximo) . ' máquina(s) con mantenimiento programado en los próximos 7 días',
            ];
        }
        if (!empty($inspecciones_pendientes)) {
            $alertas_operador[] = [
                'tipo' => 'success',
                'icono' => 'bi-clipboard-check',
                'mensaje' => count($inspecciones_pendientes) . ' inspección(es) de calidad pendiente(s)',
            ];
        }
        if (!empty($incidencias_abiertas)) {
            $alertas_operador[] = [
                'tipo' => 'danger',
                'icono' => 'bi-exclamation-triangle',
                'mensaje' => count($incidencias_abiertas) . ' incidencia(s) abierta(s) sin resolver',
            ];
        }

        $data += [
            'total_ordenes' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM ordenes_cabecera")['t'] ?? 0),
            'total_maquinas' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM maquinas")['t'] ?? 0),
            'total_moldes' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM moldes")['t'] ?? 0),
            'ordenes_hoy' => $ordenes_hoy,
            'ordenes_mi_turno' => $ordenes_con_avance,
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
            'piezas_hoy' => $piezas_hoy,
            'total_planificado_hoy' => $total_planificado_hoy,
            'eficiencia' => $eficiencia,
            'tasa_defectos' => $tasa_defectos,
            'ordenes_activas' => $ordenes_activas,
            'mantenimiento_proximo' => $mantenimiento_proximo,
            'inspecciones_pendientes' => $inspecciones_pendientes,
            'incidencias_abiertas' => $incidencias_abiertas,
            'rechazos_hoy' => $rechazos_hoy,
            'minutos_paro' => round($minutos_paro),
            'alertas_operador' => $alertas_operador,
            'produccion_semanal' => $produccion_semanal,
        ];

        $this->view('home/operador', $data);
    }

    private function vendedorDashboard(array $data): void
    {
        $userId = (int) $_SESSION['user_id'];

        $mis_clientes = $this->clienteRepository->findByVendedor($userId);
        $mis_ids = array_column($mis_clientes, 'id_cliente');

        $ventas_mes = $this->db->fetchAll("
            SELECT v.*, c.razon_social as cliente, p.nombre as producto_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
              AND MONTH(v.fecha_venta) = MONTH(CURRENT_DATE)
              AND YEAR(v.fecha_venta) = YEAR(CURRENT_DATE)
            ORDER BY v.fecha_venta DESC
        ", ['uid' => $userId, 'uid2' => $userId]);

        $top_clientes = $this->db->fetchAll("
            SELECT c.id_cliente, c.razon_social, COUNT(v.id_venta) as total_ventas,
                   SUM(v.cantidad_vendida * v.precio_unitario) as total_gastado
            FROM clientes c
            LEFT JOIN ventas v ON c.id_cliente = v.id_cliente
            WHERE c.id_vendedor = :uid
            GROUP BY c.id_cliente
            ORDER BY total_gastado DESC
            LIMIT 5
        ", ['uid' => $userId]);

        $total_ventas_mes = $this->db->fetchOne("
            SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
              AND MONTH(v.fecha_venta) = MONTH(CURRENT_DATE)
              AND YEAR(v.fecha_venta) = YEAR(CURRENT_DATE)
        ", ['uid' => $userId, 'uid2' => $userId]);

        $ventas_recientes = $this->db->fetchAll("
            SELECT v.*, c.razon_social as cliente, p.nombre as producto_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
            ORDER BY v.fecha_venta DESC
            LIMIT 10
        ", ['uid' => $userId, 'uid2' => $userId]);

        $comisiones_resumen = $this->db->fetchOne("
            SELECT COALESCE(SUM(CASE WHEN estatus = 'pendiente' THEN monto_comision ELSE 0 END), 0) as pendiente,
                   COALESCE(SUM(CASE WHEN estatus = 'pagada' THEN monto_comision ELSE 0 END), 0) as pagado
            FROM comisiones_vendedor
            WHERE id_vendedor = :uid
        ", ['uid' => $userId]);

        $ventas_mensuales = $this->db->fetchAll("
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

        $top_productos = $this->db->fetchAll("
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

        $nuevos_clientes_mes = $this->db->fetchOne("
            SELECT COUNT(*) as total
            FROM clientes
            WHERE id_vendedor = :uid
              AND activo = 1
              AND MONTH(created_at) = MONTH(CURRENT_DATE)
              AND YEAR(created_at) = YEAR(CURRENT_DATE)
        ", ['uid' => $userId])['total'] ?? 0;

        $anio = (int) date('Y');
        $mes = (int) date('m');

        $oportunidadModel = new \App\Models\Oportunidad();
        $tasaConversion = $oportunidadModel->getTasaConversion($userId);
        $pipelineResumen = $oportunidadModel->getPipelineResumen($userId);
        $totalPipeline = $oportunidadModel->getTotalPipeline($userId);

        $metaVendedor = new \App\Models\MetaVendedor();
        $meta = $metaVendedor->getMetaMes($userId, $anio, $mes);
        $montoMeta = (float) ($meta['monto_objetivo'] ?? 0);
        $montoActual = (float) ($total_ventas_mes['monto'] ?? 0);
        $avanceMeta = $montoMeta > 0 ? round($montoActual / $montoMeta * 100, 1) : 0;
        $ticketPromedio = ($total_ventas_mes['total'] ?? 0) > 0 ? round($montoActual / $total_ventas_mes['total'], 2) : 0;

        $actividadModel = new \App\Models\Actividad();
        $proximasActividades = $actividadModel->getProximas($userId, 5);
        $actividadesPendientes = $actividadModel->contarPendientes($userId);

        $interaccionModel = new \App\Models\Interaccion();
        $clientesSinSeguimiento = $interaccionModel->getClientesSinSeguimiento($userId, 7);

        $alertas = [];
        if (!empty($clientesSinSeguimiento)) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'bi-exclamation-triangle',
                'mensaje' => count($clientesSinSeguimiento) . ' cliente(s) sin seguimiento en más de 7 días',
            ];
        }
        $leadsSinVendedor = $this->db->fetchOne("SELECT COUNT(*) as t FROM clientes WHERE id_vendedor IS NULL AND activo = 1")['t'] ?? 0;
        if ($leadsSinVendedor > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'icono' => 'bi-people',
                'mensaje' => "{$leadsSinVendedor} lead(s) sin vendedor asignado. ¿Los reclamas?",
            ];
        }
        $diaMes = (int) date('j');
        $diasMes = (int) date('t');
        if ($montoMeta > 0 && $avanceMeta < 40 && ($diaMes / $diasMes) > 0.5) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'bi-graph-down-arrow',
                'mensaje' => "Tu meta mensual está al {$avanceMeta}% y ya pasó la mitad del mes",
            ];
        }

        $data += [
            'total_productos' => (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM productos")['t'] ?? 0),
            'total_clientes' => count($mis_ids),
            'ventas_mes' => $ventas_mes,
            'total_ventas_mes' => $total_ventas_mes,
            'top_clientes' => $top_clientes,
            'ventas_recientes' => $ventas_recientes,
            'comisiones_resumen' => $comisiones_resumen,
            'ventas_mensuales' => $ventas_mensuales,
            'top_productos' => $top_productos,
            'nuevos_clientes_mes' => $nuevos_clientes_mes,
            'notificaciones' => $this->notificacionService->vendedorNotifications($userId, 5),
            'notificaciones_no_leidas' => $this->notificacionService->vendedorUnreadCount($userId),
            'tasa_conversion' => $tasaConversion,
            'pipeline_resumen' => $pipelineResumen,
            'total_pipeline' => $totalPipeline,
            'meta_mes' => $montoMeta,
            'avance_meta' => $avanceMeta,
            'ticket_promedio' => $ticketPromedio,
            'proximas_actividades' => $proximasActividades,
            'actividades_pendientes' => $actividadesPendientes,
            'alertas' => $alertas,
        ];

        $this->view('home/vendedor', $data);
    }

    private function clienteDashboard(array $data): void
    {
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
            $cliente = $this->clienteRepository->findWithVendedor($idCliente);

            $mis_ventas = $this->db->fetchAll("
                SELECT v.*, p.nombre as producto_nombre, t.folio_unico
                FROM ventas v
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                LEFT JOIN tickets t ON t.id_venta = v.id_venta
                WHERE v.id_cliente = :id
                ORDER BY v.fecha_venta DESC
                LIMIT 5
            ", ['id' => $idCliente]);

            $total_compras = $this->db->fetchOne("
                SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto
                FROM ventas
                WHERE id_cliente = :id
            ", ['id' => $idCliente]) ?: ['total' => 0, 'monto' => 0];

            $vendedores = $this->db->fetchAll("
                SELECT u.id_usuario, u.nombre_usuario, e.nombre, e.apellido_paterno
                FROM usuarios u
                LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
                WHERE u.id_rol = :rol AND u.activo = 1
                ORDER BY e.nombre
            ", ['rol' => ROL_VENDEDOR]);

            if ($cliente && !empty($cliente['id_vendedor'])) {
                $vendedor_asignado = $this->db->fetchOne("
                    SELECT u.id_usuario, u.nombre_usuario, e.nombre, e.apellido_paterno
                    FROM usuarios u
                    LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
                    WHERE u.id_usuario = :id
                ", ['id' => $cliente['id_vendedor']]);
            }

            $solicitudes = $this->db->fetchAll("
                SELECT s.*, v.cantidad_vendida, v.precio_unitario, p.nombre as producto_nombre, v.fecha_venta
                FROM solicitudes_factura s
                LEFT JOIN ventas v ON s.id_venta = v.id_venta
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                WHERE s.id_cliente = :id
                ORDER BY s.fecha_solicitud DESC
            ", ['id' => $idCliente]);

            $ids_con_solicitud = array_column(
                $this->db->fetchAll("SELECT id_venta FROM solicitudes_factura WHERE id_cliente = :id", ['id' => $idCliente]),
                'id_venta'
            );
        }

        $this->db->query("SET SESSION group_concat_max_len = 100000");

        $mis_pedidos = $this->db->fetchAll("
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
            $historialRows = $this->db->fetchAll(
                "SELECT * FROM pedidos_historial WHERE id_pedido IN ({$placeholders}) ORDER BY id_pedido, created_at ASC",
                $ids
            );
            foreach ($historialRows as $h) {
                $historialPedidos[$h['id_pedido']][] = $h;
            }
        }

        $productos = $this->db->fetchAll("
            SELECT * FROM productos
            WHERE publicar_web = 1 OR publicar_web IS NULL
            ORDER BY nombre
            LIMIT 12
        ");

        $wishlistCount = 0;
        $ticketsAbiertos = 0;
        $notificaciones = [];
        $notificacionesNoLeidas = 0;
        $alertas = [];

        if ($idCliente) {
            $wishlistCount = (int) ($this->db->fetchOne(
                "SELECT COUNT(*) as c FROM wishlist WHERE id_cliente = :id",
                ['id' => $idCliente]
            )['c'] ?? 0);

            $ticketsAbiertos = (int) ($this->db->fetchOne(
                "SELECT COUNT(*) as c FROM tickets_soporte WHERE id_cliente = :id AND estatus IN ('abierto', 'respondido')",
                ['id' => $idCliente]
            )['c'] ?? 0);

            $notificaciones = $this->db->fetchAll("
                SELECT * FROM notificaciones_cliente
                WHERE id_cliente = :id
                ORDER BY created_at DESC LIMIT 5
            ", ['id' => $idCliente]);

            $notificacionesNoLeidas = (int) ($this->db->fetchOne("
                SELECT COUNT(*) as c FROM notificaciones_cliente
                WHERE id_cliente = :id AND leida = 0
            ", ['id' => $idCliente])['c'] ?? 0);
        }

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
            'db' => $this->db,
            'wishlist_count' => $wishlistCount,
            'tickets_abiertos' => $ticketsAbiertos,
            'notificaciones' => $notificaciones,
            'notificaciones_no_leidas' => $notificacionesNoLeidas,
            'alertas' => $alertas,
        ];

        $this->view('home/cliente', $data);
    }

    private function contadorDashboard(array $data): void
    {
        $hoy = date('Y-m-d');
        $mes = date('m');
        $anio = date('Y');

        $total_cuentas = $this->db->fetchOne("SELECT COUNT(*) as t FROM plan_cuentas WHERE activo = 1")['t'] ?? 0;
        $total_polizas = $this->db->fetchOne("SELECT COUNT(*) as t FROM polizas WHERE estatus = 'activo'")['t'] ?? 0;
        $total_polizas_mes = $this->db->fetchOne("
            SELECT COUNT(*) as t FROM polizas
            WHERE MONTH(fecha) = :m AND YEAR(fecha) = :y AND estatus = 'activo'
        ", ['m' => $mes, 'y' => $anio])['t'] ?? 0;
        $total_facturas_pendientes = $this->db->fetchOne(
            "SELECT COUNT(*) as t FROM solicitudes_factura WHERE estatus = 'pendiente'"
        )['t'] ?? 0;

        $ultimas_polizas = $this->db->fetchAll("
            SELECT p.*, u.nombre_usuario
            FROM polizas p
            LEFT JOIN usuarios u ON p.created_by = u.id_usuario
            WHERE p.estatus = 'activo'
            ORDER BY p.fecha DESC, p.id_poliza DESC LIMIT 8
        ");

        $periodo_actual = $this->db->fetchOne("
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

        $idCliente = user_id_cliente();
        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));

        $total_compras = ['total' => 0, 'monto' => 0];
        $mis_ventas = [];
        $solicitudes = [];
        $ids_con_solicitud = [];
        $cliente = null;

        if ($idCliente) {
            $cliente = $this->clienteRepository->findWithVendedor($idCliente);

            $total_compras = $this->db->fetchOne("
                SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto
                FROM ventas WHERE id_cliente = :id
            ", ['id' => $idCliente]) ?: ['total' => 0, 'monto' => 0];

            $mis_ventas = $this->db->fetchAll("
                SELECT v.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                       p.descripcion_comercial, p.familia, p.linea, t.folio_unico
                FROM ventas v
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                LEFT JOIN tickets t ON t.id_venta = v.id_venta
                WHERE v.id_cliente = :id
                ORDER BY v.fecha_venta DESC
            ", ['id' => $idCliente]);

            $solicitudes = $this->db->fetchAll("
                SELECT s.*, v.cantidad_vendida, v.precio_unitario, p.nombre as producto_nombre, v.fecha_venta
                FROM solicitudes_factura s
                LEFT JOIN ventas v ON s.id_venta = v.id_venta
                LEFT JOIN productos p ON v.id_producto = p.id_producto
                WHERE s.id_cliente = :id
                ORDER BY s.fecha_solicitud DESC
            ", ['id' => $idCliente]);

            $ids_con_solicitud = array_column(
                $this->db->fetchAll("SELECT id_venta FROM solicitudes_factura WHERE id_cliente = :id", ['id' => $idCliente]),
                'id_venta'
            );
        }

        if ($idCliente) {
            $this->db->query("SET SESSION group_concat_max_len = 100000");
            $mis_pedidos = $this->db->fetchAll("
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
            'db' => $this->db,
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

        $venta = $this->db->fetchOne("
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

        $ticket = $this->db->fetchOne("
            SELECT * FROM tickets WHERE id_venta = :id
        ", ['id' => $idVenta]);

        $tieneSolicitud = (bool) $this->db->fetchOne(
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
        if (!verify_csrf($this->input('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $idCliente = user_id_cliente();
        $idVenta = $params['id'];

        $venta = $this->db->fetchOne("SELECT * FROM ventas WHERE id_venta = :id AND id_cliente = :cliente", [
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

        $existe = $this->db->fetchOne("SELECT id_solicitud FROM solicitudes_factura WHERE id_venta = :id AND id_cliente = :cliente", [
            'id' => $idVenta, 'cliente' => $idCliente
        ]);
        if ($existe) {
            set_flash('error', 'Ya solicitaste factura para esta compra');
            $this->redirect('/');
        }

        $this->db->insert('solicitudes_factura', [
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
        if (!verify_csrf($this->input('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $idCliente = user_id_cliente();
        $solicitud = $this->db->fetchOne(
            "SELECT id_solicitud FROM solicitudes_factura WHERE id_solicitud = :id AND id_cliente = :cliente",
            ['id' => $params['id'], 'cliente' => $idCliente]
        );
        if (!$solicitud) {
            set_flash('error', 'Solicitud no encontrada');
            $this->redirect('/');
        }
        $this->db->delete('solicitudes_factura', 'id_solicitud = :id', ['id' => $params['id']]);
        set_flash('success', 'Solicitud cancelada');
        $this->redirect('/');
    }

    public function reportarParo(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->input('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $horaInicio = $this->input('hora_inicio') ?: date('H:i');
        $this->db->insert('bitacora_paros', [
            'id_maquina' => $this->input('id_maquina'),
            'fecha' => date('Y-m-d'),
            'hora_inicio' => $horaInicio,
            'hora_fin' => null,
            'duracion_paro' => 0,
            'motivo_paro' => $this->input('motivo_paro'),
            'operador' => (string) ($_SESSION['user_id'] ?? ''),
            'estatus' => 'activo',
        ]);
        registrar_log('reportar_paro', 'maquina', $this->input('id_maquina'), $this->input('motivo_paro'));

        $operadores = $this->userRepository->findByRol(2);
        foreach ($operadores as $op) {
            $this->notificacionService->operadorNotify(
                (int) $op['id_usuario'], 'paro', 'Paro reportado',
                "Máquina #{$this->input('id_maquina')}: {$this->input('motivo_paro')}",
                (int) $this->input('id_maquina')
            );
        }

        $supervisores = $this->userRepository->findByRol(3);
        foreach ($supervisores as $sup) {
            $this->notificacionService->supervisorNotify(
                (int) $sup['id_usuario'], 'paro', 'Paro reportado',
                "Máquina #{$this->input('id_maquina')}: {$this->input('motivo_paro')}",
                (int) $this->input('id_maquina')
            );
        }

        set_flash('success', 'Paro reportado correctamente');
        $this->redirect('/');
    }

    public function setTurno(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->input('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $turno = $this->input('turno');
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
        if (!verify_csrf($this->input('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $this->notificacionService->markAsRead('supervisor', (int) $_SESSION['user_id']);
        set_flash('success', 'Notificaciones marcadas como leídas');
        $this->redirect('/');
    }

    public function marcarNotificacionesOperador(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->input('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $this->notificacionService->markAsRead('operador', (int) $_SESSION['user_id']);
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
        if (!verify_csrf($this->input('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $idCliente = user_id_cliente();
        $idVendedor = $this->input('id_vendedor');
        if (!$idCliente || !$idVendedor) {
            set_flash('error', 'Datos inválidos');
            $this->redirect('/');
        }
        $this->clienteRepository->update($idCliente, ['id_vendedor' => $idVendedor]);
        registrar_log('asignar_vendedor', 'cliente', $idCliente, "Vendedor asignado: {$idVendedor}");
        set_flash('success', 'Vendedor asignado correctamente');
        $this->redirect('/');
    }
}

<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class OperadorDashboardController extends Controller
{
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        requireAuth();
        requireRolMultiple([1, 2, 3]);
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
                   CASE WHEN bp.id_bitacora IS NOT NULL THEN 'paro' ELSE COALESCE(m.estado, m.estatus) END as estado_real,
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

        $paros_hoy = $this->db->fetchAll("
            SELECT bp.* FROM bitacora_paros bp WHERE bp.fecha = :fecha
        ", ['fecha' => $hoy]);

        $minutos_paro = 0;
        foreach ($paros_hoy as $p) {
            if ($p['hora_fin'] && $p['hora_inicio']) {
                $inicio = strtotime($p['fecha'] . ' ' . $p['hora_inicio']);
                $fin = strtotime($p['fecha'] . ' ' . $p['hora_fin']);
                $minutos_paro += ($fin - $inicio) / 60;
            } elseif ($p['hora_inicio']) {
                $inicio = strtotime($p['fecha'] . ' ' . $p['hora_inicio']);
                $minutos_paro += (time() - $inicio) / 60;
            }
        }

        $minutos_disponibles = 480;
        $eficiencia = max(0, $minutos_disponibles > 0 ? round((($minutos_disponibles - $minutos_paro) / $minutos_disponibles) * 100, 1) : 0);

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

        $notificaciones_op = $this->db->fetchAll("
            SELECT * FROM notificaciones_operador WHERE id_operador = :id ORDER BY created_at DESC LIMIT 5
        ", ['id' => $userId]);
        $notificaciones_op_no_leidas = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) as c FROM notificaciones_operador WHERE id_operador = :id AND leida = 0",
            ['id' => $userId]
        )['c'] ?? 0);

        $data = [
            'pageTitle' => 'Panel de Operador',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'ordenes_hoy' => $ordenes_hoy,
            'ordenes_mi_turno' => $ordenes_mi_turno,
            'maquinas_activas' => $maquinas_activas,
            'maquinas_con_estado' => $maquinas_con_estado,
            'incidencias_hoy' => $incidencias_hoy,
            'paros_activos' => $paros_activos,
            'pendientes_completar' => $pendientes_completar,
            'total_incidencias_hoy' => count($incidencias_hoy),
            'total_paros_activos' => count($paros_activos),
            'turno_actual' => $turnoActual,
            'fecha_hoy' => $hoy,
            'resumen_turno' => $resumen_turno,
            'mis_completadas_count' => count($mis_completadas),
            'mi_producido' => $mi_producido,
            'notificaciones_op' => $notificaciones_op,
            'notificaciones_op_no_leidas' => $notificaciones_op_no_leidas,
            'piezas_hoy' => $piezas_hoy,
            'eficiencia' => $eficiencia,
            'tasa_defectos' => $tasa_defectos,
            'ordenes_activas' => $ordenes_activas,
            'mantenimiento_proximo' => $mantenimiento_proximo,
            'inspecciones_pendientes' => $inspecciones_pendientes,
            'incidencias_abiertas' => $incidencias_abiertas,
            'rechazos_hoy' => $rechazos_hoy,
            'minutos_paro' => $minutos_paro,
        ];
        view('home/operador', $data);
    }
}

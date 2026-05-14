<?php
namespace App\Controllers;

use App\Core\Controller;

class ReportesController extends Controller
{
    public function kpi(): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        $db = \App\Core\Database::getInstance();

        $kpis = $db->fetchAll("SELECT * FROM indicadores_kpi ORDER BY indicador");
        $oee = $db->fetchAll("
            SELECT io.*, m.nombre as maquina_nombre 
            FROM indicadores_oee io
            LEFT JOIN maquinas m ON io.id_maquina = m.id_maquina
            ORDER BY io.fecha DESC LIMIT 20
        ");
        $eficiencia = $db->fetchAll("
            SELECT eo.*, m.nombre as maquina_nombre
            FROM eficiencia_operativa eo
            LEFT JOIN maquinas m ON eo.id_maquina = m.id_maquina
            ORDER BY eo.fecha DESC LIMIT 20
        ");

        $data = [
            'kpis' => $kpis,
            'oee' => $oee,
            'eficiencia' => $eficiencia,
            'pageTitle' => 'KPIs y Reportes',
        ];
        $this->view('reportes/kpi', $data);
    }

    public function produccion(): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        $db = \App\Core\Database::getInstance();

        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $params = [];
        $where = '';
        if (!empty($filters['fecha_desde'])) {
            $where .= " AND oc.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND oc.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        $produccion = $db->fetchAll("
            SELECT oc.*, p.nombre as producto_nombre, m.nombre as maquina_nombre,
                   md.nombre_molde as molde_nombre
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
            LEFT JOIN moldes md ON oc.id_molde = md.id_molde
            WHERE 1=1 {$where}
            ORDER BY oc.fecha DESC LIMIT 50
        ", $params);

        $consumos = $db->fetchAll("
            SELECT cm.*, mat.nombre as material_nombre
            FROM consumo_material_por_orden cm
            LEFT JOIN materiales mat ON cm.id_material = mat.id_material
            ORDER BY cm.fecha DESC LIMIT 30
        ");

        $incidencias = $db->fetchAll("
            SELECT ip.*, oc.id_producto
            FROM incidencias_produccion ip
            LEFT JOIN ordenes_cabecera oc ON ip.id_orden_cabe = oc.id_orden_cabe
            ORDER BY ip.fecha DESC LIMIT 20
        ");

        $scrap = $db->fetchAll("
            SELECT s.*, p.nombre as producto_nombre
            FROM scrap_reciclado s
            LEFT JOIN ordenes_cabecera oc ON s.id_orden = oc.id_orden_cabe
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            ORDER BY s.fecha DESC LIMIT 20
        ");

        $data = [
            'produccion' => $produccion,
            'consumos' => $consumos,
            'incidencias' => $incidencias,
            'scrap' => $scrap,
            'pageTitle' => 'Reporte de Producción',
            'filters' => $filters,
        ];
        $this->view('reportes/produccion', $data);
    }

}

<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class FlujoEfectivoController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance();

        if (!in_array(user_rol(), [1, 3, 6])) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $anio = (int) ($_GET['anio'] ?? date('Y'));
        $mes = (int) ($_GET['mes'] ?? 0);

        $cuentasEfectivo = $db->fetchAll(
            "SELECT id_cuenta, codigo, nombre FROM plan_cuentas WHERE codigo LIKE '1.1.1%' AND activo = 1 ORDER BY codigo"
        );

        $saldoInicial = 0;
        if (!empty($cuentasEfectivo)) {
            $fecha = $mes > 0 ? "{$anio}-" . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . "-01" : "{$anio}-01-01";
            $ids = array_column($cuentasEfectivo, 'id_cuenta');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $r = $db->fetchOne(
                "SELECT COALESCE(SUM(CASE WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono ELSE pd.abono - pd.cargo END), 0) as saldo
                 FROM plan_cuentas c
                 LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
                 LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo' AND p.fecha < :fecha
                 WHERE c.id_cuenta IN ({$placeholders})",
                array_merge(['fecha' => $fecha], $ids)
            );
            $saldoInicial = (float) ($r['saldo'] ?? 0);
        }

        $getFlujo = function(string $whereExtra, array $paramsExtra) use ($db, $anio, $mes) {
            $where = "YEAR(p.fecha) = :anio AND p.estatus = 'activo'";
            $params = ['anio' => $anio];
            if ($mes > 0) { $where .= " AND MONTH(p.fecha) = :mes"; $params['mes'] = $mes; }
            $where .= " AND {$whereExtra}";
            return $db->fetchAll("
                SELECT c.id_cuenta, c.codigo, c.nombre, c.naturaleza,
                       COALESCE(SUM(pd.cargo), 0) as total_cargo, COALESCE(SUM(pd.abono), 0) as total_abono
                FROM plan_cuentas c
                JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
                JOIN polizas p ON pd.id_poliza = p.id_poliza
                WHERE {$where}
                GROUP BY c.id_cuenta HAVING total_cargo > 0 OR total_abono > 0
                ORDER BY c.codigo
            ", array_merge($params, $paramsExtra));
        };

        $data = [
            'pageTitle' => 'Flujo de Efectivo',
            'anio' => $anio,
            'mes' => $mes,
            'operacion' => $getFlujo("c.tipo IN ('ingreso', 'gasto')", []),
            'inversion' => $getFlujo("c.codigo LIKE '1.2%'", []),
            'financiamiento' => $getFlujo("c.tipo IN ('pasivo', 'capital') AND c.codigo NOT LIKE '1.2%'", []),
            'saldo_inicial' => $saldoInicial,
            'efectivo_cuentas' => $cuentasEfectivo,
        ];
        $this->view('contabilidad/flujo_efectivo', $data);
    }
}

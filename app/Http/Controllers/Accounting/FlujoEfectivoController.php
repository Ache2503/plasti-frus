<?php
namespace App\Http\Controllers\Accounting;

use App\Core\Controller;
use App\Core\Database;

class FlujoEfectivoController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);

        $anio = (int) ($this->getParam('anio', date('Y')));
        $mes = (int) ($this->getParam('mes', 0));

        $data = [
            'pageTitle' => 'Flujo de Efectivo',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'anio' => $anio,
            'mes' => $mes,
            'operacion' => $this->getFlujoOperacion($anio, $mes),
            'inversion' => $this->getFlujoInversion($anio, $mes),
            'financiamiento' => $this->getFlujoFinanciamiento($anio, $mes),
            'saldo_inicial' => $this->getSaldoEfectivoInicial($anio, $mes),
            'efectivo_cuentas' => $this->getCuentasEfectivo(),
        ];
        $this->view('contabilidad.flujo_efectivo', $data);
    }

    private function getCuentasEfectivo(): array
    {
        return $this->db->fetchAll(
            "SELECT id_cuenta, codigo, nombre FROM plan_cuentas
             WHERE codigo LIKE '1.1.1%' AND activo = 1 ORDER BY codigo"
        );
    }

    private function getSaldoEfectivoInicial(int $anio, int $mes): float
    {
        $fecha = $mes > 0 ? "{$anio}-" . str_pad((string) $mes, 2, '0', STR_PAD_LEFT) . "-01" : "{$anio}-01-01";
        $cuentasEfectivo = $this->getCuentasEfectivo();
        if (empty($cuentasEfectivo)) return 0;
        $ids = array_column($cuentasEfectivo, 'id_cuenta');
        $placeholders = [];
        $params = ['fecha' => $fecha];
        foreach ($ids as $i => $id) {
            $key = "id_{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = $id;
        }
        $inClause = implode(',', $placeholders);
        $result = $this->db->fetchOne(
            "SELECT COALESCE(SUM(CASE WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono ELSE pd.abono - pd.cargo END), 0) as saldo
             FROM plan_cuentas c
             LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
             LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo' AND p.fecha < :fecha
             WHERE c.id_cuenta IN ({$inClause})",
            $params
        );
        return (float) ($result['saldo'] ?? 0);
    }

    private function getFlujoOperacion(int $anio, int $mes): array
    {
        $where = "YEAR(p.fecha) = :anio AND p.estatus = 'activo'";
        $params = ['anio' => $anio];
        if ($mes > 0) {
            $where .= " AND MONTH(p.fecha) = :mes";
            $params['mes'] = $mes;
        }
        return $this->db->fetchAll("
            SELECT c.id_cuenta, c.codigo, c.nombre, c.naturaleza,
                   COALESCE(SUM(pd.cargo), 0) as total_cargo,
                   COALESCE(SUM(pd.abono), 0) as total_abono
            FROM plan_cuentas c
            JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            JOIN polizas p ON pd.id_poliza = p.id_poliza
            WHERE c.tipo IN ('ingreso', 'gasto') AND {$where}
            GROUP BY c.id_cuenta
            HAVING total_cargo > 0 OR total_abono > 0
            ORDER BY c.codigo
        ", $params);
    }

    private function getFlujoInversion(int $anio, int $mes): array
    {
        $where = "YEAR(p.fecha) = :anio AND p.estatus = 'activo'";
        $params = ['anio' => $anio];
        if ($mes > 0) { $where .= " AND MONTH(p.fecha) = :mes"; $params['mes'] = $mes; }
        return $this->db->fetchAll("
            SELECT c.id_cuenta, c.codigo, c.nombre, c.naturaleza,
                   COALESCE(SUM(pd.cargo), 0) as total_cargo,
                   COALESCE(SUM(pd.abono), 0) as total_abono
            FROM plan_cuentas c
            JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            JOIN polizas p ON pd.id_poliza = p.id_poliza
            WHERE c.codigo LIKE '1.2%' AND {$where}
            GROUP BY c.id_cuenta
            HAVING total_cargo > 0 OR total_abono > 0
            ORDER BY c.codigo
        ", $params);
    }

    private function getFlujoFinanciamiento(int $anio, int $mes): array
    {
        $where = "YEAR(p.fecha) = :anio AND p.estatus = 'activo'";
        $params = ['anio' => $anio];
        if ($mes > 0) { $where .= " AND MONTH(p.fecha) = :mes"; $params['mes'] = $mes; }
        return $this->db->fetchAll("
            SELECT c.id_cuenta, c.codigo, c.nombre, c.naturaleza,
                   COALESCE(SUM(pd.cargo), 0) as total_cargo,
                   COALESCE(SUM(pd.abono), 0) as total_abono
            FROM plan_cuentas c
            JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            JOIN polizas p ON pd.id_poliza = p.id_poliza
            WHERE c.tipo IN ('pasivo', 'capital') AND c.codigo NOT LIKE '1.2%' AND {$where}
            GROUP BY c.id_cuenta
            HAVING total_cargo > 0 OR total_abono > 0
            ORDER BY c.codigo
        ", $params);
    }
}

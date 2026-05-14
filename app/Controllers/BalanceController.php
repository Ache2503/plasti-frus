<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class BalanceController extends Controller
{
    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);
    }

    public function balanza(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $fechaFin = $this->getParam('fecha') ?: date('Y-m-d');

        $cuentas = $db->fetchAll("
            SELECT c.id_cuenta, c.codigo, c.nombre, c.tipo, c.nivel, c.naturaleza,
                   COALESCE(SUM(CASE WHEN p.estatus = 'activo' THEN pd.cargo ELSE 0 END), 0) as total_cargo,
                   COALESCE(SUM(CASE WHEN p.estatus = 'activo' THEN pd.abono ELSE 0 END), 0) as total_abono
            FROM plan_cuentas c
            LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.fecha <= :fecha
            GROUP BY c.id_cuenta
            ORDER BY c.codigo
        ", ['fecha' => $fechaFin]);

        $data = [
            'pageTitle' => 'Balanza de Comprobación',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuentas' => $cuentas,
            'fecha' => $fechaFin,
        ];
        $this->view('balance/balanza', $data);
    }

    public function estadoResultados(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $anio = $this->getParam('anio') ?: date('Y');

        $ingresos = $db->fetchAll("
            SELECT c.id_cuenta, c.codigo, c.nombre, c.tipo, c.naturaleza,
                   COALESCE(SUM(CASE
                       WHEN c.naturaleza = 'acreedora' THEN pd.abono - pd.cargo
                       ELSE pd.cargo - pd.abono
                   END), 0) as saldo
            FROM plan_cuentas c
            JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
            WHERE c.tipo IN ('ingreso', 'gasto') AND YEAR(p.fecha) = :anio
            GROUP BY c.id_cuenta
            HAVING saldo != 0
            ORDER BY c.codigo
        ", ['anio' => $anio]);

        $total_ingresos = 0;
        $total_gastos = 0;
        foreach ($ingresos as $i => $cta) {
            if ($cta['tipo'] === 'ingreso') $total_ingresos += $cta['saldo'];
            else $total_gastos += $cta['saldo'];
        }
        $utilidad = $total_ingresos - $total_gastos;

        $data = [
            'pageTitle' => 'Estado de Resultados',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'ingresos' => $ingresos,
            'total_ingresos' => $total_ingresos,
            'total_gastos' => $total_gastos,
            'utilidad' => $utilidad,
            'anio' => $anio,
        ];
        $this->view('balance/estado_resultados', $data);
    }

    public function balanceGeneral(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $fecha = $this->getParam('fecha') ?: date('Y-m-d');

        $cuentas = $db->fetchAll("
            SELECT c.id_cuenta, c.codigo, c.nombre, c.tipo, c.nivel, c.naturaleza, c.id_padre,
                   COALESCE(SUM(CASE
                       WHEN p.estatus = 'activo' THEN
                           CASE WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono
                           ELSE pd.abono - pd.cargo END
                       ELSE 0 END
                   ), 0) as saldo
            FROM plan_cuentas c
            LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.fecha <= :fecha
            WHERE c.tipo IN ('activo', 'pasivo', 'capital')
            GROUP BY c.id_cuenta
            ORDER BY c.codigo
        ", ['fecha' => $fecha]);

        $data = [
            'pageTitle' => 'Balance General',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuentas' => $cuentas,
            'fecha' => $fecha,
        ];
        $this->view('balance/balance_general', $data);
    }

    public function libroDiario(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $fecha_desde = $this->getParam('fecha_desde') ?: date('Y-m-01');
        $fecha_hasta = $this->getParam('fecha_hasta') ?: date('Y-m-d');

        $movimientos = $db->fetchAll("
            SELECT p.id_poliza, p.folio, p.fecha, p.concepto as poliza_concepto, p.tipo,
                   pd.concepto as partida_concepto,
                   pc.codigo, pc.nombre as cuenta_nombre,
                   pd.cargo, pd.abono
            FROM polizas_detalle pd
            JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
            LEFT JOIN plan_cuentas pc ON pd.id_cuenta = pc.id_cuenta
            WHERE p.fecha BETWEEN :desde AND :hasta
            ORDER BY p.fecha, p.id_poliza, pd.id_detalle
        ", ['desde' => $fecha_desde, 'hasta' => $fecha_hasta]);

        $total_cargo = array_sum(array_column($movimientos, 'cargo'));
        $total_abono = array_sum(array_column($movimientos, 'abono'));

        $data = [
            'pageTitle' => 'Libro Diario',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'movimientos' => $movimientos,
            'total_cargo' => $total_cargo,
            'total_abono' => $total_abono,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
        ];
        $this->view('balance/libro_diario', $data);
    }

    public function libroMayor(array $params): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $idCuenta = (int) $params['id'];

        $cuenta = $db->fetchOne("SELECT * FROM plan_cuentas WHERE id_cuenta = :id", ['id' => $idCuenta]);
        if (!$cuenta) {
            set_flash('error', 'Cuenta no encontrada');
            $this->redirect('/contabilidad/balanza');
        }

        $fecha_desde = $this->getParam('fecha_desde') ?: date('Y-01-01');
        $fecha_hasta = $this->getParam('fecha_hasta') ?: date('Y-m-d');

        $movimientos = $db->fetchAll("
            SELECT p.id_poliza, p.folio, p.fecha, p.concepto as poliza_concepto,
                   pd.concepto, pd.cargo, pd.abono
            FROM polizas_detalle pd
            JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
            WHERE pd.id_cuenta = :cuenta AND p.fecha BETWEEN :desde AND :hasta
            ORDER BY p.fecha, p.id_poliza
        ", ['cuenta' => $idCuenta, 'desde' => $fecha_desde, 'hasta' => $fecha_hasta]);

        $saldo = 0;
        foreach ($movimientos as &$m) {
            if ($cuenta['naturaleza'] === 'deudora') {
                $saldo += $m['cargo'] - $m['abono'];
            } else {
                $saldo += $m['abono'] - $m['cargo'];
            }
            $m['saldo'] = $saldo;
        }

        $data = [
            'pageTitle' => "Libro Mayor: {$cuenta['codigo']} {$cuenta['nombre']}",
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuenta' => $cuenta,
            'movimientos' => $movimientos,
            'fecha_desde' => $fecha_desde,
            'fecha_hasta' => $fecha_hasta,
            'saldo_final' => $saldo,
        ];
        $this->view('balance/libro_mayor', $data);
    }
}

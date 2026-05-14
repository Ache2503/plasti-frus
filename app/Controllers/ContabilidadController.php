<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ContabilidadController extends Controller
{
    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $hoy = date('Y-m-d');
        $mes = date('m');
        $anio = date('Y');

        $total_cuentas = $db->fetchOne("SELECT COUNT(*) as t FROM plan_cuentas WHERE activo = 1")['t'] ?? 0;
        $total_polizas_mes = $db->fetchOne("
            SELECT COUNT(*) as t FROM polizas
            WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio AND estatus = 'activo'
        ", ['mes' => $mes, 'anio' => $anio])['t'] ?? 0;
        $total_polizas = $db->fetchOne("SELECT COUNT(*) as t FROM polizas WHERE estatus = 'activo'")['t'] ?? 0;

        $cargos_mes = (float) ($db->fetchOne("
            SELECT COALESCE(SUM(pd.cargo), 0) as t FROM polizas_detalle pd
            JOIN polizas p ON pd.id_poliza = p.id_poliza
            WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio AND p.estatus = 'activo'
        ", ['mes' => $mes, 'anio' => $anio])['t'] ?? 0);
        $abonos_mes = (float) ($db->fetchOne("
            SELECT COALESCE(SUM(pd.abono), 0) as t FROM polizas_detalle pd
            JOIN polizas p ON pd.id_poliza = p.id_poliza
            WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio AND p.estatus = 'activo'
        ", ['mes' => $mes, 'anio' => $anio])['t'] ?? 0);

        $ultimas_polizas = $db->fetchAll("
            SELECT p.*, u.nombre_usuario
            FROM polizas p
            LEFT JOIN usuarios u ON p.created_by = u.id_usuario
            WHERE p.estatus = 'activo'
            ORDER BY p.fecha DESC, p.id_poliza DESC LIMIT 10
        ");

        $periodo_actual = $db->fetchOne("
            SELECT * FROM periodos_contables WHERE mes = :mes AND anio = :anio
        ", ['mes' => $mes, 'anio' => $anio]);

        $data = [
            'pageTitle' => 'Contabilidad',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'total_cuentas' => $total_cuentas,
            'total_polizas_mes' => $total_polizas_mes,
            'total_polizas' => $total_polizas,
            'cargos_mes' => $cargos_mes,
            'abonos_mes' => $abonos_mes,
            'ultimas_polizas' => $ultimas_polizas,
            'periodo_actual' => $periodo_actual,
            'mes_actual' => $mes,
            'anio_actual' => $anio,
        ];
        $this->view('contabilidad/index', $data);
    }

    public function periodos(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $periodos = $db->fetchAll("
            SELECT p.*, u.nombre_usuario
            FROM periodos_contables p
            LEFT JOIN usuarios u ON p.cerrado_por = u.id_usuario
            ORDER BY p.anio DESC, p.mes DESC
        ");
        $data = [
            'pageTitle' => 'Periodos Contables',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'periodos' => $periodos,
        ];
        $this->view('contabilidad/periodos', $data);
    }

    public function cerrarPeriodo(array $params): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('cerrar_periodo')) {
            set_flash('error', 'No tienes permisos para cerrar periodos');
            $this->redirect('/contabilidad/periodos');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/periodos');
        }
        $db = Database::getInstance();
        $db->update('periodos_contables', [
            'cerrado' => 1,
            'fecha_cierre' => date('Y-m-d H:i:s'),
            'cerrado_por' => (int) $_SESSION['user_id'],
        ], 'id_periodo = :id', ['id' => $params['id']]);
        registrar_log('cerrar_periodo', 'periodo_contable', $params['id'], 'Periodo cerrado');
        set_flash('success', 'Periodo cerrado correctamente');
        $this->redirect('/contabilidad/periodos');
    }

    public function reabrirPeriodo(array $params): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('cerrar_periodo')) {
            set_flash('error', 'No tienes permisos para reabrir periodos');
            $this->redirect('/contabilidad/periodos');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/periodos');
        }
        $db = Database::getInstance();
        $db->update('periodos_contables', [
            'cerrado' => 0,
            'fecha_cierre' => null,
            'cerrado_por' => null,
        ], 'id_periodo = :id', ['id' => $params['id']]);
        registrar_log('reabrir_periodo', 'periodo_contable', $params['id'], 'Periodo reabierto');
        set_flash('success', 'Periodo reabierto correctamente');
        $this->redirect('/contabilidad/periodos');
    }
}

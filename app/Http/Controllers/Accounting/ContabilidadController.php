<?php
namespace App\Http\Controllers\Accounting;

use App\Core\Controller;
use App\Core\Database;

class ContabilidadController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $hoy = date('Y-m-d');
        $mes = date('m');
        $anio = date('Y');

        $alertas = [];
        if ($this->hayPolizasSinCuadrar($mes, $anio)) {
            $alertas[] = ['tipo' => 'danger', 'icono' => 'bi-exclamation-triangle', 'mensaje' => 'Hay pólizas del mes que no cuadran (debe ≠ haber)'];
        }
        if ($this->hayPeriodoAbierto($mes, $anio) && $this->getTotalPolizasMes($mes, $anio) === 0 && (int) date('j') > 5) {
            $alertas[] = ['tipo' => 'warning', 'icono' => 'bi-info-circle', 'mensaje' => 'El mes actual no tiene pólizas registradas'];
        }
        $activos = $this->getTotalPorTipo('activo', $hoy);
        $pasivos = $this->getTotalPorTipo('pasivo', $hoy);
        $patrimonio = $this->getTotalPorTipo('capital', $hoy);
        $ingresosMes = $this->getTotalResultadoMes('ingreso', $mes, $anio);
        $gastosMes = $this->getTotalResultadoMes('gasto', $mes, $anio);
        $utilidad = $ingresosMes - $gastosMes;
        $flujoEfectivo = $this->getFlujoEfectivoNeto($mes, $anio);

        $ultimasPolizas = $this->getUltimasPolizasConTotal();

        $data = [
            'pageTitle' => 'Dashboard Contabilidad',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'total_cuentas' => $this->getTotalCuentasActivas(),
            'total_polizas_mes' => $this->getTotalPolizasMes($mes, $anio),
            'total_polizas' => $this->getTotalPolizas(),
            'cargos_mes' => $this->getCargosMes($mes, $anio),
            'abonos_mes' => $this->getAbonosMes($mes, $anio),
            'ultimas_polizas' => $ultimasPolizas,
            'periodo_actual' => $this->getPeriodo($mes, $anio),
            'total_activos' => $activos,
            'total_pasivos' => $pasivos,
            'total_patrimonio' => $patrimonio,
            'ingresos_mes' => $ingresosMes,
            'gastos_mes' => $gastosMes,
            'utilidad_mes' => $utilidad,
            'flujo_efectivo' => $flujoEfectivo,
            'alertas' => $alertas,
            'total_facturas_pendientes' => $this->getTotalFacturasPendientes(),
        ];
        $this->view('home/contador', $data);
    }

    public function periodos(): void
    {
        $this->checkAccess();
        $data = [
            'pageTitle' => 'Periodos Contables',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'periodos' => $this->getAllPeriodos(),
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
        $this->db->update('periodos_contables', [
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
        $this->db->update('periodos_contables', [
            'cerrado' => 0,
            'fecha_cierre' => null,
            'cerrado_por' => null,
        ], 'id_periodo = :id', ['id' => $params['id']]);
        registrar_log('reabrir_periodo', 'periodo_contable', $params['id'], 'Periodo reabierto');
        set_flash('success', 'Periodo reabierto correctamente');
        $this->redirect('/contabilidad/periodos');
    }

    private function getTotalCuentasActivas(): int
    {
        return (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM plan_cuentas WHERE activo = 1")['t'] ?? 0);
    }

    private function getTotalPolizasMes(string $mes, string $anio): int
    {
        return (int) ($this->db->fetchOne(
            "SELECT COUNT(*) as t FROM polizas WHERE MONTH(fecha) = :mes AND YEAR(fecha) = :anio AND estatus = 'activo'",
            ['mes' => $mes, 'anio' => $anio]
        )['t'] ?? 0);
    }

    private function getTotalPolizas(): int
    {
        return (int) ($this->db->fetchOne("SELECT COUNT(*) as t FROM polizas WHERE estatus = 'activo'")['t'] ?? 0);
    }

    private function getCargosMes(string $mes, string $anio): float
    {
        return (float) ($this->db->fetchOne(
            "SELECT COALESCE(SUM(pd.cargo), 0) as t FROM polizas_detalle pd
             JOIN polizas p ON pd.id_poliza = p.id_poliza
             WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio AND p.estatus = 'activo'",
            ['mes' => $mes, 'anio' => $anio]
        )['t'] ?? 0);
    }

    private function getAbonosMes(string $mes, string $anio): float
    {
        return (float) ($this->db->fetchOne(
            "SELECT COALESCE(SUM(pd.abono), 0) as t FROM polizas_detalle pd
             JOIN polizas p ON pd.id_poliza = p.id_poliza
             WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio AND p.estatus = 'activo'",
            ['mes' => $mes, 'anio' => $anio]
        )['t'] ?? 0);
    }

    private function getUltimasPolizas(): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, u.nombre_usuario
             FROM polizas p
             LEFT JOIN usuarios u ON p.created_by = u.id_usuario
             WHERE p.estatus = 'activo'
             ORDER BY p.fecha DESC, p.id_poliza DESC LIMIT 10"
        );
    }

    private function getUltimasPolizasConTotal(): array
    {
        return $this->db->fetchAll("
            SELECT p.*, u.nombre_usuario,
                   (SELECT COALESCE(SUM(pd.cargo), 0) FROM polizas_detalle pd WHERE pd.id_poliza = p.id_poliza) as total_cargo
            FROM polizas p
            LEFT JOIN usuarios u ON p.created_by = u.id_usuario
            WHERE p.estatus = 'activo'
            ORDER BY p.fecha DESC, p.id_poliza DESC LIMIT 10
        ");
    }

    private function getPeriodo(string $mes, string $anio): ?array
    {
        $periodo = $this->db->fetchOne(
            "SELECT * FROM periodos_contables WHERE mes = :mes AND anio = :anio",
            ['mes' => $mes, 'anio' => $anio]
        );
        return $periodo ?: null;
    }

    private function getAllPeriodos(): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, u.nombre_usuario
             FROM periodos_contables p
             LEFT JOIN usuarios u ON p.cerrado_por = u.id_usuario
             ORDER BY p.anio DESC, p.mes DESC"
        );
    }

    private function getTotalPorTipo(string $tipo, string $fecha): float
    {
        return (float) ($this->db->fetchOne("
            SELECT COALESCE(SUM(CASE WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono ELSE pd.abono - pd.cargo END), 0) as saldo
            FROM plan_cuentas c
            LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo' AND p.fecha <= :fecha
            WHERE c.tipo = :tipo
        ", ['tipo' => $tipo, 'fecha' => $fecha])['saldo'] ?? 0);
    }

    private function getTotalResultadoMes(string $tipo, string $mes, string $anio): float
    {
        return (float) ($this->db->fetchOne("
            SELECT COALESCE(SUM(CASE WHEN c.naturaleza = 'acreedora' THEN pd.abono - pd.cargo ELSE pd.cargo - pd.abono END), 0) as total
            FROM plan_cuentas c
            JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
            WHERE c.tipo = :tipo AND MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio
        ", ['tipo' => $tipo, 'mes' => $mes, 'anio' => $anio])['total'] ?? 0);
    }

    private function getFlujoEfectivoNeto(string $mes, string $anio): float
    {
        $cuentasEfectivo = $this->db->fetchAll(
            "SELECT id_cuenta FROM plan_cuentas WHERE codigo LIKE '1.1.1%' AND activo = 1"
        );
        if (empty($cuentasEfectivo)) return 0;
        $ids = array_column($cuentasEfectivo, 'id_cuenta');
        $placeholders = [];
        $params = ['mes' => $mes, 'anio' => $anio];
        foreach ($ids as $i => $id) {
            $key = "id_{$i}";
            $placeholders[] = ":{$key}";
            $params[$key] = $id;
        }
        $inClause = implode(',', $placeholders);
        $result = $this->db->fetchOne("
            SELECT COALESCE(SUM(CASE WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono ELSE pd.abono - pd.cargo END), 0) as flujo
            FROM plan_cuentas c
            LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
                AND MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio
            WHERE c.id_cuenta IN ({$inClause})
        ", $params);
        return (float) ($result['flujo'] ?? 0);
    }

    private function getTotalFacturasPendientes(): int
    {
        return (int) ($this->db->fetchOne(
            "SELECT COUNT(*) as t FROM solicitudes_factura WHERE estatus = 'pendiente'"
        )['t'] ?? 0);
    }

    private function hayPolizasSinCuadrar(string $mes, string $anio): bool
    {
        $polizas = $this->db->fetchAll("
            SELECT p.id_poliza,
                   COALESCE(SUM(pd.cargo), 0) as total_cargo,
                   COALESCE(SUM(pd.abono), 0) as total_abono
            FROM polizas p
            JOIN polizas_detalle pd ON pd.id_poliza = p.id_poliza
            WHERE MONTH(p.fecha) = :mes AND YEAR(p.fecha) = :anio AND p.estatus = 'activo'
            GROUP BY p.id_poliza
            HAVING ABS(total_cargo - total_abono) > 0.01
        ", ['mes' => $mes, 'anio' => $anio]);
        return !empty($polizas);
    }

    private function hayPeriodoAbierto(string $mes, string $anio): bool
    {
        $p = $this->db->fetchOne(
            "SELECT cerrado FROM periodos_contables WHERE mes = :m AND anio = :a",
            ['m' => $mes, 'a' => $anio]
        );
        return $p ? !$p['cerrado'] : true;
    }
}

<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class PolizasController extends Controller
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
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
            'tipo' => $this->getParam('tipo'),
            'estatus' => $this->getParam('estatus'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $page = max(1, (int) $this->getParam('page', 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT p.*, u.nombre_usuario,
                   COALESCE(SUM(pd.cargo), 0) as total_cargos,
                   COALESCE(SUM(pd.abono), 0) as total_abonos,
                   COUNT(pd.id_detalle) as num_partidas
            FROM polizas p
            LEFT JOIN usuarios u ON p.created_by = u.id_usuario
            LEFT JOIN polizas_detalle pd ON p.id_poliza = pd.id_poliza
        ";
        $countSql = "SELECT COUNT(DISTINCT p.id_poliza) as total FROM polizas p";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "p.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "p.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'];
        }
        if (!empty($filters['tipo'])) {
            $where[] = "p.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }
        if (!empty($filters['estatus'])) {
            $where[] = "p.estatus = :estatus";
            $params['estatus'] = $filters['estatus'];
        }

        if (!empty($where)) {
            $whereSql = ' WHERE ' . implode(' AND ', $where);
            $sql .= $whereSql;
            $countSql .= $whereSql;
        }
        $sql .= ' GROUP BY p.id_poliza ORDER BY p.fecha DESC, p.id_poliza DESC';
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $total = (int) ($db->fetchOne($countSql, $params)['total'] ?? 0);
        $polizas = $db->fetchAll($sql, $params);
        $totalPages = max(1, (int) ceil($total / $perPage));

        $data = [
            'pageTitle' => 'Pólizas',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'polizas' => $polizas,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ];
        $this->view('polizas/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('crear')) {
            set_flash('error', 'No tienes permisos para crear pólizas');
            $this->redirect('/contabilidad/polizas');
        }
        $db = Database::getInstance();
        $cuentas = $db->fetchAll("
            SELECT id_cuenta, codigo, nombre, nivel, naturaleza, tipo
            FROM plan_cuentas WHERE activo = 1 AND nivel >= 3
            ORDER BY codigo
        ");

        $mes = date('m');
        $anio = date('Y');
        $periodo = $db->fetchOne("
            SELECT * FROM periodos_contables WHERE mes = :m AND anio = :y
        ", ['m' => $mes, 'y' => $anio]);

        $data = [
            'pageTitle' => 'Nueva Póliza',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuentas' => $cuentas,
            'periodo_cerrado' => ($periodo['cerrado'] ?? 0),
        ];
        $this->view('polizas/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('crear')) {
            set_flash('error', 'No tienes permisos para crear pólizas');
            $this->redirect('/contabilidad/polizas');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/polizas');
        }

        $tipo = $this->postParam('tipo');
        $concepto = trim($this->postParam('concepto'));
        $fecha = $this->postParam('fecha');
        $cuentas = $this->postParam('id_cuenta', []);
        $cargos = $this->postParam('cargo', []);
        $abonos = $this->postParam('abono', []);
        $conceptos = $this->postParam('concepto_partida', []);

        if (empty($concepto)) {
            set_flash('error', 'El concepto es obligatorio');
            $this->redirect('/contabilidad/polizas/create');
        }

        $fechaTs = strtotime($fecha);
        $mes = (int) date('m', $fechaTs);
        $anio = (int) date('Y', $fechaTs);

        $db = Database::getInstance();
        $periodo = $db->fetchOne("
            SELECT * FROM periodos_contables WHERE mes = :m AND anio = :y
        ", ['m' => $mes, 'y' => $anio]);
        if ($periodo && $periodo['cerrado']) {
            set_flash('error', "El periodo {$mes}/{$anio} está cerrado. No se pueden registrar pólizas.");
            $this->redirect('/contabilidad/polizas/create');
        }

        $partidasValidas = 0;
        $totalCargo = 0;
        $totalAbono = 0;
        foreach ($cuentas as $i => $idCuenta) {
            if (empty($idCuenta)) continue;
            $cargo = (float) ($cargos[$i] ?? 0);
            $abono = (float) ($abonos[$i] ?? 0);
            if ($cargo == 0 && $abono == 0) continue;
            $partidasValidas++;
            $totalCargo += $cargo;
            $totalAbono += $abono;
        }

        if ($partidasValidas === 0) {
            set_flash('error', 'Debe agregar al menos una partida con cargo o abono');
            $this->redirect('/contabilidad/polizas/create');
        }

        if (abs($totalCargo - $totalAbono) > 0.01) {
            set_flash('error', "La póliza no cuadra: Cargos \$" . number_format($totalCargo, 2) . " ≠ Abonos \$" . number_format($totalAbono, 2));
            $this->redirect('/contabilidad/polizas/create');
        }

        $folioBase = 'POL-' . date('Ym', $fechaTs) . '-';
        $ultimo = $db->fetchOne("
            SELECT folio FROM polizas WHERE folio LIKE :prefix ORDER BY id_poliza DESC LIMIT 1
        ", ['prefix' => "{$folioBase}%"]);
        if ($ultimo) {
            $num = ((int) substr($ultimo['folio'], -4)) + 1;
        } else {
            $num = 1;
        }
        $folio = $folioBase . str_pad((string) $num, 4, '0', STR_PAD_LEFT);

        $db->beginTransaction();
        try {
            $idPoliza = $db->insert('polizas', [
                'folio' => $folio,
                'tipo' => $tipo,
                'concepto' => $concepto,
                'fecha' => $fecha,
                'created_by' => (int) $_SESSION['user_id'],
            ]);

            foreach ($cuentas as $i => $idCuenta) {
                if (empty($idCuenta)) continue;
                $cargo = (float) ($cargos[$i] ?? 0);
                $abono = (float) ($abonos[$i] ?? 0);
                if ($cargo == 0 && $abono == 0) continue;

                $db->insert('polizas_detalle', [
                    'id_poliza' => $idPoliza,
                    'id_cuenta' => $idCuenta,
                    'concepto' => $conceptos[$i] ?? '',
                    'cargo' => $cargo,
                    'abono' => $abono,
                ]);
            }

            $db->commit();
            registrar_log('crear_poliza', 'poliza', $idPoliza, "Póliza {$folio}: {$concepto}");
            set_flash('success', "Póliza {$folio} creada correctamente");
            $this->redirect('/contabilidad/polizas');
        } catch (\Exception $e) {
            $db->rollback();
            set_flash('error', 'Error al crear póliza: ' . $e->getMessage());
            $this->redirect('/contabilidad/polizas/create');
        }
    }

    public function show(array $params): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $poliza = $db->fetchOne("
            SELECT p.*, u.nombre_usuario
            FROM polizas p
            LEFT JOIN usuarios u ON p.created_by = u.id_usuario
            WHERE p.id_poliza = :id
        ", ['id' => $params['id']]);
        if (!$poliza) {
            set_flash('error', 'Póliza no encontrada');
            $this->redirect('/contabilidad/polizas');
        }
        $detalles = $db->fetchAll("
            SELECT pd.*, pc.codigo, pc.nombre as cuenta_nombre, pc.naturaleza
            FROM polizas_detalle pd
            LEFT JOIN plan_cuentas pc ON pd.id_cuenta = pc.id_cuenta
            WHERE pd.id_poliza = :id
            ORDER BY pd.id_detalle
        ", ['id' => $params['id']]);
        $data = [
            'pageTitle' => "Póliza {$poliza['folio']}",
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'poliza' => $poliza,
            'detalles' => $detalles,
        ];
        $this->view('polizas/show', $data);
    }

    public function cancelar(array $params): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('cancelar')) {
            set_flash('error', 'No tienes permisos para cancelar pólizas');
            $this->redirect('/contabilidad/polizas');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/polizas');
        }
        $db = Database::getInstance();
        $poliza = $db->fetchOne("SELECT * FROM polizas WHERE id_poliza = :id", ['id' => $params['id']]);
        if (!$poliza) {
            set_flash('error', 'Póliza no encontrada');
            $this->redirect('/contabilidad/polizas');
        }
        if ($poliza['estatus'] === 'cancelado') {
            set_flash('error', 'La póliza ya está cancelada');
            $this->redirect('/contabilidad/polizas');
        }
        $fechaTs = strtotime($poliza['fecha']);
        $mes = (int) date('m', $fechaTs);
        $anio = (int) date('Y', $fechaTs);
        $periodo = $db->fetchOne("SELECT * FROM periodos_contables WHERE mes = :m AND anio = :y", ['m' => $mes, 'y' => $anio]);
        if ($periodo && $periodo['cerrado']) {
            set_flash('error', "El periodo del {$mes}/{$anio} está cerrado. No se puede cancelar.");
            $this->redirect('/contabilidad/polizas');
        }
        $db->update('polizas', ['estatus' => 'cancelado'], 'id_poliza = :id', ['id' => $params['id']]);
        registrar_log('cancelar_poliza', 'poliza', $params['id'], "Póliza {$poliza['folio']} cancelada");
        set_flash('success', 'Póliza cancelada correctamente');
        $this->redirect('/contabilidad/polizas');
    }
}

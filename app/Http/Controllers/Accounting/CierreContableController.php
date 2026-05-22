<?php
namespace App\Http\Controllers\Accounting;

use App\Core\Controller;
use App\Core\Database;
use App\Models\CierreContable;
use App\Models\PeriodoContable;

class CierreContableController extends Controller
{
    private Database $db;
    private CierreContable $cierre;
    private PeriodoContable $periodo;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->cierre = new CierreContable();
        $this->periodo = new PeriodoContable();
    }

    public function index(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);

        $anio = (int) ($this->getParam('anio', date('Y')));
        $cierres = $this->cierre->getAll();
        $mesesAbiertos = [];
        for ($m = 1; $m <= 12; $m++) {
            if (!$this->cierre->isClosed($anio, $m)) {
                $mesesAbiertos[] = $m;
            }
        }

        $data = [
            'pageTitle' => 'Cierres Contables',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cierres' => $cierres,
            'anio' => $anio,
            'meses_abiertos' => $mesesAbiertos,
        ];
        $this->view('contabilidad.cierres', $data);
    }

    public function cerrar(): void
    {
        $this->requireAuth();
        if (!contabilidad_permiso('cerrar_periodo')) {
            set_flash('error', 'No tienes permisos para cerrar periodos');
            $this->redirect('/contabilidad/cierres');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/cierres');
        }

        $mes = (int) $this->postParam('mes');
        $anio = (int) $this->postParam('anio');
        $observaciones = trim($this->postParam('observaciones', ''));

        if ($this->cierre->isClosed($anio, $mes)) {
            set_flash('error', "El periodo {$mes}/{$anio} ya está cerrado");
            $this->redirect('/contabilidad/cierres');
        }

        $this->cierre->closePeriod($anio, $mes, (int) $_SESSION['user_id'], $observaciones);

        $periodo = $this->db->fetchOne(
            "SELECT id_periodo FROM periodos_contables WHERE mes = :m AND anio = :a",
            ['m' => $mes, 'a' => $anio]
        );
        if ($periodo) {
            $this->db->update('periodos_contables', [
                'cerrado' => 1,
                'fecha_cierre' => date('Y-m-d H:i:s'),
                'cerrado_por' => (int) $_SESSION['user_id'],
            ], 'id_periodo = :id', ['id' => $periodo['id_periodo']]);
        }

        registrar_log('cierre_contable', 'cierres_contables', 0, "Cierre {$mes}/{$anio}");
        set_flash('success', "Periodo {$mes}/{$anio} cerrado correctamente");
        $this->redirect('/contabilidad/cierres');
    }

    public function reabrir(array $params): void
    {
        $this->requireAuth();
        if (!contabilidad_permiso('cerrar_periodo')) {
            set_flash('error', 'No tienes permisos para reabrir periodos');
            $this->redirect('/contabilidad/cierres');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/cierres');
        }

        $id = (int) ($params['id'] ?? 0);
        $cierre = $this->cierre->find($id);
        if (!$cierre) {
            set_flash('error', 'Cierre no encontrado');
            $this->redirect('/contabilidad/cierres');
        }

        $this->cierre->deleteById($id);

        $periodo = $this->db->fetchOne(
            "SELECT id_periodo FROM periodos_contables WHERE mes = :m AND anio = :a",
            ['m' => $cierre['mes'], 'a' => $cierre['anio']]
        );
        if ($periodo) {
            $this->db->update('periodos_contables', [
                'cerrado' => 0,
                'fecha_cierre' => null,
                'cerrado_por' => null,
            ], 'id_periodo = :id', ['id' => $periodo['id_periodo']]);
        }

        registrar_log('reabrir_cierre', 'cierres_contables', $id, "Reabierto {$cierre['mes']}/{$cierre['anio']}");
        set_flash('success', "Periodo {$cierre['mes']}/{$cierre['anio']} reabierto correctamente");
        $this->redirect('/contabilidad/cierres');
    }
}

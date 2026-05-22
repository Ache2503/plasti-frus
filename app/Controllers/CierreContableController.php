<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\CierreContable;

class CierreContableController extends Controller
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
        $cierreModel = new CierreContable();

        $cierres = $cierreModel->getAll();
        $mesesAbiertos = [];
        for ($m = 1; $m <= 12; $m++) {
            if (!$cierreModel->isClosed($anio, $m)) {
                $mesesAbiertos[] = $m;
            }
        }

        $data = [
            'pageTitle' => 'Cierres Contables',
            'cierres' => $cierres,
            'anio' => $anio,
            'meses_abiertos' => $mesesAbiertos,
        ];
        $this->view('contabilidad/cierres', $data);
    }

    public function cerrar(): void
    {
        $this->requireAuth();
        if (!in_array(user_rol(), [1])) {
            set_flash('error', 'No tienes permisos para cerrar periodos');
            $this->redirect('/contabilidad/cierres');
        }
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/cierres');
        }

        $mes = (int) ($_POST['mes'] ?? 0);
        $anio = (int) ($_POST['anio'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');

        $cierreModel = new CierreContable();
        if ($cierreModel->isClosed($anio, $mes)) {
            set_flash('error', "El periodo {$mes}/{$anio} ya está cerrado");
            $this->redirect('/contabilidad/cierres');
        }

        $cierreModel->closePeriod($anio, $mes, (int) $_SESSION['user_id'], $observaciones);

        $periodo = $db->fetchOne(
            "SELECT id_periodo FROM periodos_contables WHERE mes = :m AND anio = :a",
            ['m' => $mes, 'a' => $anio]
        );
        if ($periodo) {
            $db = \App\Core\Database::getInstance();
            $db->update('periodos_contables', [
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
        if (!in_array(user_rol(), [1])) {
            set_flash('error', 'No tienes permisos para reabrir periodos');
            $this->redirect('/contabilidad/cierres');
        }
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/cierres');
        }

        $id = (int) ($params['id'] ?? 0);
        $cierreModel = new CierreContable();
        $cierre = $cierreModel->find($id);
        if (!$cierre) {
            set_flash('error', 'Cierre no encontrado');
            $this->redirect('/contabilidad/cierres');
        }

        $cierreModel->deleteById($id);
        $db = \App\Core\Database::getInstance();
        $periodo = $db->fetchOne(
            "SELECT id_periodo FROM periodos_contables WHERE mes = :m AND anio = :a",
            ['m' => $cierre['mes'], 'a' => $cierre['anio']]
        );
        if ($periodo) {
            $db->update('periodos_contables', [
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

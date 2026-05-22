<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Presupuesto;
use App\Models\PlanCuenta;

class PresupuestoController extends Controller
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
        $presupuestoModel = new Presupuesto();

        $cuentasResultado = $db->fetchAll(
            "SELECT id_cuenta, codigo, CONCAT(codigo, ' - ', nombre) as label
             FROM plan_cuentas WHERE tipo IN ('ingreso', 'gasto') AND activo = 1 ORDER BY codigo"
        );

        $presupuestos = $presupuestoModel->getByPeriodo($anio);

        $data = [
            'pageTitle' => 'Presupuestos',
            'anio' => $anio,
            'cuentas' => $cuentasResultado,
            'presupuestos' => $presupuestos,
            'meses' => range(1, 12),
        ];
        $this->view('contabilidad/presupuestos', $data);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        if (!in_array(user_rol(), [1, 6])) {
            set_flash('error', 'No tienes permisos para editar presupuestos');
            $this->redirect('/contabilidad/presupuestos');
        }
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/presupuestos');
        }

        $anio = (int) ($_POST['anio'] ?? 0);
        $cuentas = $_POST['cuenta_id'] ?? [];
        $meses = $_POST['mes'] ?? [];
        $montos = $_POST['monto'] ?? [];

        $presupuestoModel = new Presupuesto();
        foreach ($cuentas as $i => $cuentaId) {
            $cuentaId = (int) $cuentaId;
            $mes = (int) ($meses[$i] ?? 0);
            $monto = (float) ($montos[$i] ?? 0);
            if ($cuentaId > 0 && $mes > 0) {
                $presupuestoModel->setPresupuesto($cuentaId, $anio, $mes, $monto);
            }
        }

        registrar_log('presupuesto_guardar', 'presupuestos', 0, "Presupuestos {$anio} actualizados");
        set_flash('success', 'Presupuestos guardados correctamente');
        $this->redirect('/contabilidad/presupuestos?anio=' . $anio);
    }

    public function comparar(): void
    {
        $this->requireAuth();
        if (!in_array(user_rol(), [1, 3, 6])) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $anio = (int) ($_GET['anio'] ?? date('Y'));
        $mes = (int) ($_GET['mes'] ?? (int) date('m'));

        $presupuestoModel = new Presupuesto();
        $comparacion = $presupuestoModel->getComparacionMensual($anio, $mes);

        $totalPresupuesto = 0;
        $totalReal = 0;
        foreach ($comparacion as $c) {
            if ($c['tipo'] === 'ingreso') {
                $totalPresupuesto += $c['presupuesto'];
                $totalReal += $c['real_mes'];
            }
        }

        $data = [
            'pageTitle' => "Comparación Presupuesto vs Real - {$mes}/{$anio}",
            'anio' => $anio,
            'mes' => $mes,
            'comparacion' => $comparacion,
            'total_presupuesto' => $totalPresupuesto,
            'total_real' => $totalReal,
        ];
        $this->view('contabilidad/presupuesto_comparar', $data);
    }
}

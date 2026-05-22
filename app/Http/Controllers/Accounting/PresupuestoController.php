<?php
namespace App\Http\Controllers\Accounting;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Presupuesto;
use App\Models\PlanCuenta;

class PresupuestoController extends Controller
{
    private Database $db;
    private Presupuesto $presupuesto;
    private PlanCuenta $planCuenta;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->presupuesto = new Presupuesto();
        $this->planCuenta = new PlanCuenta();
    }

    public function index(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);

        $anio = (int) ($this->getParam('anio', date('Y')));

        $cuentasResultado = $this->db->fetchAll(
            "SELECT id_cuenta, codigo, CONCAT(codigo, ' - ', nombre) as label
             FROM plan_cuentas WHERE tipo IN ('ingreso', 'gasto') AND activo = 1 ORDER BY codigo"
        );

        $presupuestos = $this->presupuesto->getByPeriodo($anio);

        $data = [
            'pageTitle' => 'Presupuestos',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'anio' => $anio,
            'cuentas' => $cuentasResultado,
            'presupuestos' => $presupuestos,
            'meses' => range(1, 12),
        ];
        $this->view('contabilidad.presupuestos', $data);
    }

    public function guardar(): void
    {
        $this->requireAuth();
        if (!es_contador_o_admin()) {
            set_flash('error', 'No tienes permisos para editar presupuestos');
            $this->redirect('/contabilidad/presupuestos');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/presupuestos');
        }

        $anio = (int) $this->postParam('anio');
        $cuentas = $this->postParam('cuenta_id', []);
        $meses = $this->postParam('mes', []);
        $montos = $this->postParam('monto', []);

        foreach ($cuentas as $i => $cuentaId) {
            $cuentaId = (int) $cuentaId;
            $mes = (int) ($meses[$i] ?? 0);
            $monto = (float) ($montos[$i] ?? 0);
            if ($cuentaId > 0 && $mes > 0) {
                $this->presupuesto->setPresupuesto($cuentaId, $anio, $mes, $monto);
            }
        }

        registrar_log('presupuesto_guardar', 'presupuestos', 0, "Presupuestos {$anio} actualizados");
        set_flash('success', 'Presupuestos guardados correctamente');
        $this->redirect('/contabilidad/presupuestos?anio=' . $anio);
    }

    public function comparar(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);

        $anio = (int) ($this->getParam('anio', date('Y')));
        $mes = (int) ($this->getParam('mes', (int) date('m')));
        $comparacion = $this->presupuesto->getComparacionMensual($anio, $mes);

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
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'anio' => $anio,
            'mes' => $mes,
            'comparacion' => $comparacion,
            'total_presupuesto' => $totalPresupuesto,
            'total_real' => $totalReal,
        ];
        $this->view('contabilidad.presupuesto_comparar', $data);
    }
}

<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Material;
use App\Models\MantenimientoMaquina;
use App\Models\IncidenciaProduccion;
use App\Models\OrdenCabe;
use App\Models\Maquina;

class NotificacionesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        $materialModel = new Material();
        $mantModel = new MantenimientoMaquina();
        $incModel = new IncidenciaProduccion();
        $ordenModel = new OrdenCabe();
        $maquinaModel = new Maquina();

        $data = [
            'materiales_bajos' => $materialModel->getLowStock(),
            'mantenimientos_pendientes' => $mantModel->getPendientes(),
            'incidencias_abiertas' => $incModel->fetchAll("SELECT i.*, oc.id_orden_cabe, p.nombre as producto_nombre FROM incidencias_produccion i LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe LEFT JOIN productos p ON oc.id_producto = p.id_producto WHERE i.estatus != 'cerrada' ORDER BY i.fecha DESC"),
            'total_ordenes_hoy' => count($ordenModel->getByDateRange(date('Y-m-d'), date('Y-m-d'))),
            'total_maquinas_activas' => count($maquinaModel->getActiveMachines()),
            'pageTitle' => 'Alertas del Sistema',
        ];

        if (es_supervisor()) {
            $userId = (int) $_SESSION['user_id'];
            $data['notificaciones_supervisor'] = supervisor_notificaciones($userId, 20);
            $data['notificaciones_supervisor_no_leidas'] = supervisor_notificaciones_no_leidas($userId);
        }

        $this->view('notificaciones/index', $data);
    }
}

<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Material;
use App\Models\MantenimientoMaquina;
use App\Models\IncidenciaProduccion;
use App\Models\OrdenCabe;
use App\Models\Maquina;
use App\Services\NotificacionService;

class NotificacionesController extends Controller
{
    private Database $db;
    private NotificacionService $notificacionService;
    private Material $materialModel;
    private MantenimientoMaquina $mantModel;
    private IncidenciaProduccion $incModel;
    private OrdenCabe $ordenModel;
    private Maquina $maquinaModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->notificacionService = new NotificacionService();
        $this->materialModel = new Material();
        $this->mantModel = new MantenimientoMaquina();
        $this->incModel = new IncidenciaProduccion();
        $this->ordenModel = new OrdenCabe();
        $this->maquinaModel = new Maquina();
    }

    public function index(): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);

        $data = [
            'materiales_bajos' => $this->materialModel->getLowStock(),
            'mantenimientos_pendientes' => $this->mantModel->getPendientes(),
            'incidencias_abiertas' => $this->incModel->fetchAll("SELECT i.*, oc.id_orden_cabe, p.nombre as producto_nombre FROM incidencias_produccion i LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe LEFT JOIN productos p ON oc.id_producto = p.id_producto WHERE i.estatus != 'cerrada' ORDER BY i.fecha DESC"),
            'total_ordenes_hoy' => count($this->ordenModel->getByDateRange(date('Y-m-d'), date('Y-m-d'))),
            'total_maquinas_activas' => count($this->maquinaModel->getActiveMachines()),
            'pageTitle' => 'Alertas del Sistema',
        ];

        if (es_supervisor()) {
            $userId = (int) $_SESSION['user_id'];
            $data['notificaciones_supervisor'] = $this->notificacionService->supervisorNotifications($userId, 20);
            $data['notificaciones_supervisor_no_leidas'] = $this->notificacionService->supervisorUnreadCount($userId);
        }

        $this->view('notificaciones/index', $data);
    }
}

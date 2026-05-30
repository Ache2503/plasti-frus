<?php
namespace App\Http\Controllers\Sales;

use App\Core\Controller;
use App\Services\ComisionService;
use App\Services\NotificacionService;

class VendedorController extends Controller
{
    private NotificacionService $notificacionService;
    private ComisionService $comisionService;

    public function __construct()
    {
        $this->notificacionService = new NotificacionService();
        $this->comisionService = new ComisionService();
    }

    public function comisiones(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $model = new \App\Models\Vendedor();
        $userId = (int) $_SESSION['user_id'];
        $data = [
            'comisiones' => $model->getComisiones($userId),
            'resumen' => $model->getResumenComisiones($userId),
            'pageTitle' => 'Mis Comisiones',
        ];
        $this->view('vendedor/comisiones', $data);
    }

    public function marcarNotificacionesLeidas(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $userId = (int) $_SESSION['user_id'];
        $this->notificacionService->markAsRead('vendedor', $userId);
        set_flash('success', 'Notificaciones marcadas como leídas');
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    public function adminComisiones(): void
    {
        $this->requireAuth();
        if (!in_array(user_rol(), [1, 3])) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $model = new \App\Models\Vendedor();

        $vendedorFiltro = $this->getParam('vendedor');
        $estatusFiltro = $this->getParam('estatus');

        $vendedores = $model->getVendedores();
        $comisiones = $model->getAllComisiones(
            $vendedorFiltro ? (int) $vendedorFiltro : null,
            $estatusFiltro ?: null
        );

        $data = [
            'comisiones' => $comisiones,
            'vendedores' => $vendedores,
            'total_pendiente' => $model->getTotalPendienteGlobal(),
            'total_pagado' => $model->getTotalPagadoGlobal(),
            'filtro_vendedor' => $vendedorFiltro,
            'filtro_estatus' => $estatusFiltro,
            'pageTitle' => 'Gestión de Comisiones',
        ];
        $this->view('vendedor/admin_comisiones', $data);
    }

    public function comisionesData(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            $this->json(['error' => 'Acceso denegado'], 403);
        }
        $userId = (int) $_SESSION['user_id'];
        $db = \App\Core\Database::getInstance();
        $data = $db->fetchAll("
            SELECT DATE_FORMAT(COALESCE(c.fecha_calculo, v.fecha_venta), '%Y-%m') as mes,
                   COALESCE(SUM(c.monto_comision), 0) as monto
            FROM comisiones_vendedor c
            LEFT JOIN ventas v ON c.id_venta = v.id_venta
            WHERE c.id_vendedor = :uid
              AND COALESCE(c.fecha_calculo, v.fecha_venta) >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
              AND c.estatus IN ('pendiente', 'pagada')
            GROUP BY mes ORDER BY mes ASC
        ", ['uid' => $userId]);
        $this->json($data);
    }

    public function comisionesFiltro(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            $this->json(['error' => 'Acceso denegado'], 403);
        }
        $userId = (int) $_SESSION['user_id'];
        $desde = $this->getParam('desde', date('Y-m-d', strtotime('-1 year')));
        $hasta = $this->getParam('hasta', date('Y-m-d'));
        $model = new \App\Models\Vendedor();
        $this->json([
            'comisiones' => $model->getComisiones($userId),
            'resumen' => $model->getResumenComisiones($userId),
            'ventas_producto' => $model->getVentasByProducto($userId, $desde, $hasta),
            'ventas_cliente' => $model->getVentasByCliente($userId, $desde, $hasta),
        ]);
    }

    public function pagarComision(array $params): void
    {
        $this->requireAuth();
        if (!in_array(user_rol(), [1, 3])) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/comisiones');
        }

        $idComision = (int) $params['id'];
        $comision = $this->comisionService->markAsPaid($idComision);
        if (!$comision) {
            set_flash('error', 'Comisión no encontrada o no está pendiente');
            $this->redirect('/comisiones');
        }
        if ($comision) {
            $this->notificacionService->vendedorNotify(
                (int) $comision['id_vendedor'],
                'comision_pagada',
                'Comisión pagada',
                "Comisión de {$comision['monto_comision']} por venta #{$comision['id_venta']} ha sido pagada",
                $idComision
            );
        }

        registrar_log('pagar_comision', 'comision', $idComision, 'Comisión marcada como pagada');
        set_flash('success', 'Comisión marcada como pagada');
        $this->redirect('/comisiones');
    }
}

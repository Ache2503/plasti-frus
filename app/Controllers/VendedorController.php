<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Vendedor;

class VendedorController extends Controller
{
    public function comisiones(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $model = new Vendedor();
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
        $db = \App\Core\Database::getInstance();
        $db->update('notificaciones_vendedor', ['leida' => 1], 'id_vendedor = :id AND leida = 0', ['id' => $userId]);
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

        $model = new Vendedor();
        $db = \App\Core\Database::getInstance();

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
        $db = \App\Core\Database::getInstance();
        $db->update('comisiones_vendedor', [
            'estatus' => 'pagada',
            'fecha_pago' => date('Y-m-d'),
        ], 'id_comision = :id', ['id' => $idComision]);

        $comision = $db->fetchOne("SELECT * FROM comisiones_vendedor WHERE id_comision = :id", ['id' => $idComision]);
        if ($comision) {
            notificar_vendedor((int) $comision['id_vendedor'], 'comision_pagada',
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

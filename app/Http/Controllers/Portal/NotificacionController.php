<?php
namespace App\Http\Controllers\Portal;

use App\Core\Controller;
use App\Core\Database;
use App\Models\NotificacionCliente;

class NotificacionController extends Controller
{
    private Database $db;
    private NotificacionCliente $notificacionModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->notificacionModel = new NotificacionCliente();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        $idCliente = user_id_cliente();
        $notificaciones = $idCliente ? $this->notificacionModel->getByCliente($idCliente, 50) : [];
        $noLeidas = $idCliente ? $this->notificacionModel->unreadCount($idCliente) : 0;

        $data = [
            'pageTitle' => 'Notificaciones',
            'notificaciones' => $notificaciones,
            'no_leidas' => $noLeidas,
        ];
        $this->view('portal.notificaciones.index', $data);
    }

    public function marcarLeidas(): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/notificaciones-cliente');
        }

        $idCliente = user_id_cliente();
        if ($idCliente) {
            $this->notificacionModel->markAsRead($idCliente);
        }

        set_flash('success', 'Notificaciones marcadas como leídas');
        $this->redirect('/notificaciones-cliente');
    }
}

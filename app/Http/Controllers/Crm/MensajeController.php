<?php
namespace App\Http\Controllers\Crm;

use App\Core\Controller;
use App\Models\Mensaje;

class MensajeController extends Controller
{
    private Mensaje $model;

    public function __construct()
    {
        $this->model = new Mensaje();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        if (!es_vendedor() && !in_array(user_rol(), [1, 3])) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
    }

    public function index(): void
    {
        $this->checkAccess();
        $userId = (int) $_SESSION['user_id'];
        $data = [
            'inbox' => $this->model->inbox($userId),
            'sent' => $this->model->sent($userId),
            'no_leidos' => $this->model->noLeidos($userId),
            'pageTitle' => 'Mensajes',
        ];
        $this->view('vendedor/mensajes', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/mensajes');
        }
        $userId = (int) $_SESSION['user_id'];
        $paraUserId = (int) $this->postParam('para_user_id');
        if (!$paraUserId) {
            set_flash('error', 'Debes seleccionar un destinatario');
            $this->redirect('/mensajes');
        }
        $this->model->create([
            'de_user_id' => $userId,
            'para_user_id' => $paraUserId,
            'asunto' => $this->postParam('asunto'),
            'mensaje' => $this->postParam('mensaje'),
        ]);
        notificar_vendedor($paraUserId, 'mensaje_recibido', 'Nuevo mensaje: ' . $this->postParam('asunto'), $this->postParam('mensaje'), $userId);
        set_flash('success', 'Mensaje enviado');
        $this->redirect('/mensajes');
    }

    public function marcarLeido(array $params): void
    {
        $this->checkAccess();
        $userId = (int) $_SESSION['user_id'];
        $this->model->marcarLeido((int) $params['id'], $userId);
        $this->json(['success' => true]);
    }

    public function show(array $params): void
    {
        $this->checkAccess();
        $userId = (int) $_SESSION['user_id'];
        $id = (int) $params['id'];
        $this->model->marcarLeido($id, $userId);
        $db = \App\Core\Database::getInstance();
        $mensaje = $db->fetchOne("
            SELECT m.*, CONCAT(e.nombre, ' ', e.apellido_paterno) as remitente_nombre
            FROM mensajes m
            LEFT JOIN usuarios u ON m.de_user_id = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE m.id_mensaje = :id AND m.para_user_id = :user
        ", ['id' => $id, 'user' => $userId]);
        if (!$mensaje) {
            set_flash('error', 'Mensaje no encontrado');
            $this->redirect('/mensajes');
        }
        $data = [
            'mensaje' => $mensaje,
            'pageTitle' => 'Mensaje',
        ];
        $this->view('vendedor/mensaje_ver', $data);
    }

    public function responder(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/mensajes');
        }
        $userId = (int) $_SESSION['user_id'];
        $idOriginal = (int) $params['id'];
        $db = \App\Core\Database::getInstance();
        $original = $db->fetchOne("SELECT * FROM mensajes WHERE id_mensaje = :id AND para_user_id = :user", ['id' => $idOriginal, 'user' => $userId]);
        if (!$original) {
            set_flash('error', 'Mensaje no encontrado');
            $this->redirect('/mensajes');
        }
        $this->model->create([
            'de_user_id' => $userId,
            'para_user_id' => (int) $original['de_user_id'],
            'asunto' => 'Re: ' . $original['asunto'],
            'mensaje' => $this->postParam('mensaje'),
        ]);
        notificar_vendedor((int) $original['de_user_id'], 'mensaje_recibido', 'Respuesta: ' . $original['asunto'], $this->postParam('mensaje'), $userId);
        set_flash('success', 'Respuesta enviada');
        $this->redirect('/mensajes');
    }

    public function noLeidos(): void
    {
        $this->checkAccess();
        $userId = (int) $_SESSION['user_id'];
        $this->json(['total' => $this->model->noLeidos($userId)]);
    }
}

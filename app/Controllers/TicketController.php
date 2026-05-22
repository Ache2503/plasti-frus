<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\TicketSoporte;

class TicketController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $db = Database::getInstance();

        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $idCliente = user_id_cliente();
        $ticketModel = new TicketSoporte();
        $tickets = $idCliente ? $ticketModel->getByCliente($idCliente) : [];

        $data = [
            'pageTitle' => 'Mis Tickets de Soporte',
            'tickets' => $tickets,
        ];
        $this->view('portal/tickets/index', $data);
    }

    public function create(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $data = [
            'pageTitle' => 'Nuevo Ticket de Soporte',
        ];
        $this->view('portal/tickets/create', $data);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/tickets/nuevo');
        }

        $idCliente = user_id_cliente();
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $prioridad = $_POST['prioridad'] ?? 'media';

        $errors = [];
        if (empty($titulo)) $errors[] = 'El título es obligatorio';
        if (empty($descripcion)) $errors[] = 'La descripción es obligatoria';
        if (!in_array($prioridad, ['baja', 'media', 'alta', 'urgente'])) $prioridad = 'media';

        if (!empty($errors)) {
            $_SESSION['_old'] = $_POST;
            set_flash('error', implode('<br>', $errors));
            $this->redirect('/tickets/nuevo');
        }

        $ticketModel = new TicketSoporte();
        $ticketModel->create([
            'id_cliente' => $idCliente,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'prioridad' => $prioridad,
        ]);

        registrar_log('ticket_crear', 'tickets_soporte', $idCliente, "Ticket: {$titulo}");
        set_flash('success', 'Ticket creado correctamente. Te responderemos pronto.');
        $this->redirect('/tickets');
    }

    public function show(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $idTicket = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        $ticketModel = new TicketSoporte();
        $ticket = $ticketModel->getWithRespuestas($idTicket, $idCliente);

        if (!$ticket) {
            set_flash('error', 'Ticket no encontrado');
            $this->redirect('/tickets');
        }

        $data = [
            'pageTitle' => 'Ticket: ' . $ticket['titulo'],
            'ticket' => $ticket,
        ];
        $this->view('portal/tickets/show', $data);
    }

    public function responder(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/tickets');
        }

        $idTicket = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();
        $mensaje = trim($_POST['mensaje'] ?? '');

        if (empty($mensaje)) {
            set_flash('error', 'El mensaje no puede estar vacío');
            $this->redirect('/tickets/' . $idTicket);
        }

        $archivo = null;
        if (!empty($_FILES['archivo']['name']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'zip'];
            if (!in_array(strtolower($ext), $allowed)) {
                set_flash('error', 'Tipo de archivo no permitido');
                $this->redirect('/tickets/' . $idTicket);
            }
            $nombre = 'ticket_' . $idTicket . '_' . time() . '.' . $ext;
            $ruta = STORAGE_PATH . '/uploads/tickets/' . $nombre;
            if (!is_dir(dirname($ruta))) {
                mkdir(dirname($ruta), 0755, true);
            }
            move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);
            $archivo = 'uploads/tickets/' . $nombre;
        }

        $ticketModel = new TicketSoporte();
        $result = $ticketModel->addRespuesta($idTicket, $idCliente, $mensaje, $archivo);

        if (!$result) {
            set_flash('error', 'No se pudo agregar la respuesta');
            $this->redirect('/tickets/' . $idTicket);
        }

        registrar_log('ticket_responder', 'tickets_soporte', $idTicket, 'Cliente respondió ticket');
        set_flash('success', 'Respuesta enviada correctamente');
        $this->redirect('/tickets/' . $idTicket);
    }

    public function cerrar(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/tickets');
        }

        $idTicket = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        $ticketModel = new TicketSoporte();
        if ($ticketModel->cerrar($idTicket, $idCliente)) {
            registrar_log('ticket_cerrar', 'tickets_soporte', $idTicket, 'Cliente cerró ticket');
            set_flash('success', 'Ticket cerrado correctamente');
        } else {
            set_flash('error', 'No se pudo cerrar el ticket');
        }
        $this->redirect('/tickets');
    }
}

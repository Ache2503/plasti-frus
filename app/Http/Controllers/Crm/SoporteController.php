<?php
namespace App\Http\Controllers\Crm;

use App\Core\Controller;
use App\Models\TicketSoporte;

class SoporteController extends Controller
{
    private TicketSoporte $ticketModel;

    public function __construct()
    {
        $this->ticketModel = new TicketSoporte();
    }

    public function index(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);

        $filters = [
            'estatus' => $this->getParam('estatus', ''),
            'prioridad' => $this->getParam('prioridad', ''),
            'search' => trim($this->getParam('search', '')),
            'id_usuario_asignado' => $this->getParam('asignado', ''),
        ];

        $tickets = $this->ticketModel->getAll($filters);
        $pendientes = $this->ticketModel->getPendientesCount();

        $data = [
            'pageTitle' => 'Soporte - Tickets de Clientes',
            'tickets' => $tickets,
            'pendientes' => $pendientes,
            'filters' => $filters,
        ];
        $this->view('crm.soporte.index', $data);
    }

    public function show(array $params): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);

        $idTicket = (int) ($params['id'] ?? 0);
        if (!$idTicket) {
            set_flash('error', 'Ticket no especificado');
            $this->redirect('/soporte');
        }

        $ticket = $this->ticketModel->getWithRespuestas($idTicket, 0);
        if (!$ticket) {
            set_flash('error', 'Ticket no encontrado');
            $this->redirect('/soporte');
        }

        $data = [
            'pageTitle' => 'Ticket: ' . $ticket['titulo'],
            'ticket' => $ticket,
        ];
        $this->view('crm.soporte.show', $data);
    }

    public function responder(array $params): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/soporte');
        }

        $idTicket = (int) ($params['id'] ?? 0);
        $mensaje = trim($this->postParam('mensaje', ''));

        if (empty($mensaje)) {
            set_flash('error', 'El mensaje no puede estar vacío');
            $this->redirect('/soporte/' . $idTicket);
        }

        $archivo = null;
        if (!empty($_FILES['archivo']['name']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'zip'];
            if (!in_array(strtolower($ext), $allowed)) {
                set_flash('error', 'Tipo de archivo no permitido');
                $this->redirect('/soporte/' . $idTicket);
            }
            $nombre = 'staff_ticket_' . $idTicket . '_' . time() . '.' . $ext;
            $ruta = STORAGE_PATH . '/uploads/tickets/' . $nombre;
            if (!is_dir(dirname($ruta))) {
                mkdir(dirname($ruta), 0755, true);
            }
            move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta);
            $archivo = 'uploads/tickets/' . $nombre;
        }

        $this->ticketModel->responderComoStaff(
            $idTicket,
            (int) $_SESSION['user_id'],
            $mensaje,
            $archivo
        );

        registrar_log('soporte_responder', 'tickets_soporte', $idTicket, 'Staff respondió ticket de soporte');
        set_flash('success', 'Respuesta enviada al cliente');
        $this->redirect('/soporte/' . $idTicket);
    }

    public function asignar(array $params): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/soporte');
        }

        $idTicket = (int) ($params['id'] ?? 0);
        $idUsuario = (int) $this->postParam('id_usuario_asignado', 0);

        if (!$idTicket || !$idUsuario) {
            set_flash('error', 'Datos inválidos');
            $this->redirect('/soporte/' . $idTicket);
        }

        $this->ticketModel->asignar($idTicket, $idUsuario);
        registrar_log('soporte_asignar', 'tickets_soporte', $idTicket, "Asignado a usuario #{$idUsuario}");
        set_flash('success', 'Ticket asignado correctamente');
        $this->redirect('/soporte/' . $idTicket);
    }

    public function cerrar(array $params): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/soporte');
        }

        $idTicket = (int) ($params['id'] ?? 0);

        if ($this->ticketModel->cerrar($idTicket, 0)) {
            registrar_log('soporte_cerrar', 'tickets_soporte', $idTicket, 'Staff cerró ticket de soporte');
            set_flash('success', 'Ticket cerrado correctamente');
        } else {
            set_flash('error', 'No se pudo cerrar el ticket');
        }
        $this->redirect('/soporte/' . $idTicket);
    }

    public function abrir(array $params): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/soporte');
        }

        $idTicket = (int) ($params['id'] ?? 0);

        $this->ticketModel->abrir($idTicket);
        registrar_log('soporte_abrir', 'tickets_soporte', $idTicket, 'Staff reabrió ticket de soporte');
        set_flash('success', 'Ticket reabierto');
        $this->redirect('/soporte/' . $idTicket);
    }
}

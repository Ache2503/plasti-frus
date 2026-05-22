<?php
namespace App\Http\Controllers\Crm;

use App\Core\Controller;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use App\Repositories\VentaRepository;
use App\Services\NotificacionService;
use App\Http\Requests\ClienteRequest;
use App\Exceptions\ValidationException;

class ClientesController extends Controller
{
    private Cliente $clienteModel;
    private ClienteRepository $clienteRepository;
    private VentaRepository $ventaRepository;
    private NotificacionService $notificacionService;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
        $this->clienteRepository = new ClienteRepository();
        $this->ventaRepository = new VentaRepository();
        $this->notificacionService = new NotificacionService();
    }

    private function checkClientesAccess(): void
    {
        $this->requireAuth();
        if (!in_array(user_rol(), [1, 3, ROL_VENDEDOR, 6])) {
            set_flash('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('/');
        }
    }

    private function puedeAsignarVendedor(): bool
    {
        return in_array(user_rol(), [1, 3]);
    }

    private function getVendedores(): array
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetchAll("
            SELECT u.id_usuario, u.nombre_usuario, e.nombre, e.apellido_paterno
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE u.id_rol = :rol AND u.activo = 1
            ORDER BY e.nombre
        ", ['rol' => ROL_VENDEDOR]);
    }

    public function index(): void
    {
        $this->checkClientesAccess();
        $page = max(1, (int) $this->getParam('page', 1));
        $search = trim($this->getParam('search', ''));
        $perPage = 15;
        $result = $this->clienteModel->search($search, $page, $perPage);
        $data = [
            'clientes' => $result['data'],
            'pageTitle' => 'Clientes',
            'currentPage' => $result['page'],
            'totalPages' => $result['totalPages'],
            'total' => $result['total'],
            'search' => $search,
        ];
        $this->view('clientes/index', $data);
    }

    public function create(): void
    {
        $this->checkClientesAccess();
        unset($_SESSION['_old']);
        $data = [
            'sectores' => $this->clienteModel->getSectores(),
            'pageTitle' => 'Nuevo Cliente',
        ];
        if ($this->puedeAsignarVendedor()) {
            $data['vendedores'] = $this->getVendedores();
        }
        $this->view('clientes/create', $data);
    }

    public function store(): void
    {
        $this->checkClientesAccess();

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/clientes');
        }

        try {
            (new ClienteRequest($_POST))->validate();
        } catch (ValidationException $e) {
            $_SESSION['_old'] = $_POST;
            set_flash('error', $e->getFirstError());
            $this->redirect('/clientes/create');
        }

        unset($_SESSION['_old']);
        $data = [
            'razon_social' => $this->postParam('razon_social'),
            'rfc' => $this->postParam('rfc'),
            'ciudad' => $this->postParam('ciudad'),
            'estado' => $this->postParam('estado'),
            'telefono' => $this->postParam('telefono'),
            'correo' => $this->postParam('correo'),
            'sector' => $this->postParam('sector'),
        ];
        if ($this->puedeAsignarVendedor()) {
            $data['id_vendedor'] = $this->postParam('id_vendedor') ?: null;
        } elseif (es_vendedor()) {
            $data['id_vendedor'] = $_SESSION['user_id'];
        }
        $id = $this->clienteRepository->create($data);
        registrar_log('crear', 'cliente', $id, $data['razon_social']);
        set_flash('success', 'Cliente creado correctamente');
        $this->redirect('/clientes');
    }

    public function edit(array $params): void
    {
        $this->checkClientesAccess();
        $cliente = $this->clienteRepository->findWithVendedor($params['id']);
        if (!$cliente) {
            set_flash('error', 'Cliente no encontrado');
            $this->redirect('/clientes');
        }
        unset($_SESSION['_old']);
        $data = [
            'cliente' => $cliente,
            'pageTitle' => 'Editar Cliente',
        ];
        if ($this->puedeAsignarVendedor()) {
            $data['vendedores'] = $this->getVendedores();
        }
        $this->view('clientes/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkClientesAccess();

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/clientes');
        }

        try {
            (new ClienteRequest($_POST))->validate();
        } catch (ValidationException $e) {
            $_SESSION['_old'] = $_POST;
            set_flash('error', $e->getFirstError());
            $this->redirect('/clientes/edit/' . $params['id']);
        }

        unset($_SESSION['_old']);
        $data = [
            'razon_social' => $this->postParam('razon_social'),
            'rfc' => $this->postParam('rfc'),
            'ciudad' => $this->postParam('ciudad'),
            'estado' => $this->postParam('estado'),
            'telefono' => $this->postParam('telefono'),
            'correo' => $this->postParam('correo'),
            'sector' => $this->postParam('sector'),
        ];
        if ($this->puedeAsignarVendedor()) {
            $data['id_vendedor'] = $this->postParam('id_vendedor') ?: null;
        }
        $this->clienteRepository->update($params['id'], $data);
        registrar_log('actualizar', 'cliente', $params['id'], $data['razon_social']);
        set_flash('success', 'Cliente actualizado correctamente');
        $this->redirect('/clientes');
    }

    public function show(array $params): void
    {
        $this->checkClientesAccess();
        $cliente = $this->clienteRepository->findWithVendedor($params['id']);
        if (!$cliente) {
            set_flash('error', 'Cliente no encontrado');
            $this->redirect('/clientes');
        }
        $ventas = $this->ventaRepository->findByCliente($params['id']);
        $data = [
            'cliente' => $cliente,
            'ventas' => $ventas,
            'pageTitle' => 'Detalle Cliente',
        ];
        $this->view('clientes/show', $data);
    }

    public function delete(array $params): void
    {
        $this->checkClientesAccess();

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/clientes');
        }

        $cliente = $this->clienteModel->find($params['id']);
        $razon = $cliente['razon_social'] ?? 'desconocido';
        $this->clienteModel->delete($params['id']);
        registrar_log('eliminar', 'cliente', $params['id'], $razon);
        set_flash('success', 'Cliente eliminado correctamente');
        $this->redirect('/clientes');
    }

    public function reclamarCliente(array $params): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            set_flash('error', 'Solo vendedores pueden reclamar clientes');
            $this->redirect('/clientes');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/clientes');
        }

        $idCliente = (int) $params['id'];
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            set_flash('error', 'Cliente no encontrado');
            $this->redirect('/clientes');
        }
        if (!empty($cliente['id_vendedor'])) {
            set_flash('error', 'Este cliente ya tiene un vendedor asignado');
            $this->redirect('/clientes');
        }

        $this->clienteRepository->update($idCliente, ['id_vendedor' => $_SESSION['user_id']]);
        registrar_log('reclamar_cliente', 'cliente', $idCliente, 'Vendedor reclamó cliente');
        $this->notificacionService->vendedorNotify(
            (int) $_SESSION['user_id'],
            'cliente_asignado',
            'Cliente asignado',
            "Se te ha asignado el cliente: {$cliente['razon_social']}",
            $idCliente
        );
        set_flash('success', 'Cliente reclamado correctamente');
        $this->redirect('/clientes');
    }

    public function historial(array $params): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            $this->json(['error' => 'Acceso denegado'], 403);
        }
        $clienteId = (int) $params['id'];
        $userId = (int) $_SESSION['user_id'];
        $cliente = $this->clienteModel->find($clienteId);
        if (!$cliente || (int) ($cliente['id_vendedor'] ?? 0) !== $userId) {
            $this->json(['error' => 'Cliente no válido'], 403);
        }
        $db = \App\Core\Database::getInstance();
        $interacciones = $db->fetchAll("
            SELECT i.*, CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre
            FROM interacciones i
            LEFT JOIN usuarios u ON i.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE i.id_cliente = :cliente
            ORDER BY i.fecha DESC
        ", ['cliente' => $clienteId]);
        $this->json($interacciones);
    }

    public function misClientes(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $page = max(1, (int) $this->getParam('page', 1));
        $search = trim($this->getParam('search', ''));
        $perPage = 15;
        $result = $this->clienteModel->search($search, $page, $perPage, (int) $_SESSION['user_id']);
        $userId = (int) $_SESSION['user_id'];
        $clientes = $result['data'];
        $db = \App\Core\Database::getInstance();
        foreach ($clientes as &$cliente) {
            $ultima = $db->fetchOne("
                SELECT fecha, tipo, descripcion FROM interacciones
                WHERE id_cliente = :cliente AND id_vendedor = :vendedor
                ORDER BY fecha DESC LIMIT 1
            ", ['cliente' => $cliente['id_cliente'], 'vendedor' => $userId]);
            $cliente['ultima_interaccion'] = $ultima;
        }
        $data = [
            'clientes' => $clientes,
            'pageTitle' => 'Mis Clientes',
            'currentPage' => $result['page'],
            'totalPages' => $result['totalPages'],
            'total' => $result['total'],
            'search' => $search,
        ];
        $this->view('clientes/index', $data);
    }
}

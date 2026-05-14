<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cliente;

class ClientesController extends Controller
{
    private $cliente;

    public function __construct()
    {
        $this->cliente = new Cliente();
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
        $result = $this->cliente->search($search, $page, $perPage);
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
            'sectores' => $this->cliente->getSectores(),
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

        $errors = validate($_POST, [
            'razon_social' => 'required|min:3',
            'rfc' => 'rfc',
            'correo' => 'email',
            'telefono' => 'phone',
        ]);

        if (!empty($errors)) {
            $_SESSION['_old'] = $_POST;
            $first = reset($errors);
            set_flash('error', is_array($first) ? $first[0] : $first);
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
        $id = $this->cliente->create($data);
        registrar_log('crear', 'cliente', $id, $data['razon_social']);
        set_flash('success', 'Cliente creado correctamente');
        $this->redirect('/clientes');
    }

    public function edit(array $params): void
    {
        $this->checkClientesAccess();
        $cliente = $this->cliente->find($params['id']);
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

        $errors = validate($_POST, [
            'razon_social' => 'required|min:3',
            'rfc' => 'rfc',
            'correo' => 'email',
            'telefono' => 'phone',
        ]);

        if (!empty($errors)) {
            $_SESSION['_old'] = $_POST;
            $first = reset($errors);
            set_flash('error', is_array($first) ? $first[0] : $first);
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
        $this->cliente->update($params['id'], $data);
        registrar_log('actualizar', 'cliente', $params['id'], $data['razon_social']);
        set_flash('success', 'Cliente actualizado correctamente');
        $this->redirect('/clientes');
    }

    public function show(array $params): void
    {
        $this->checkClientesAccess();
        $cliente = $this->cliente->find($params['id']);
        if (!$cliente) {
            set_flash('error', 'Cliente no encontrado');
            $this->redirect('/clientes');
        }
        $db = \App\Core\Database::getInstance();
        $ventas = $db->fetchAll("
            SELECT v.*, p.nombre as producto_nombre, t.folio_unico
            FROM ventas v
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            LEFT JOIN tickets t ON t.id_venta = v.id_venta
            WHERE v.id_cliente = :id
            ORDER BY v.fecha_venta DESC
            LIMIT 20
        ", ['id' => $params['id']]);
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

        $cliente = $this->cliente->find($params['id']);
        $razon = $cliente['razon_social'] ?? 'desconocido';
        $this->cliente->delete($params['id']);
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
        $cliente = $this->cliente->find($idCliente);
        if (!$cliente) {
            set_flash('error', 'Cliente no encontrado');
            $this->redirect('/clientes');
        }
        if (!empty($cliente['id_vendedor'])) {
            set_flash('error', 'Este cliente ya tiene un vendedor asignado');
            $this->redirect('/clientes');
        }

        $db = \App\Core\Database::getInstance();
        $db->update('clientes', ['id_vendedor' => $_SESSION['user_id']], 'id_cliente = :id', ['id' => $idCliente]);
        registrar_log('reclamar_cliente', 'cliente', $idCliente, 'Vendedor reclamó cliente');
        notificar_vendedor((int) $_SESSION['user_id'], 'cliente_asignado',
            'Cliente asignado',
            "Se te ha asignado el cliente: {$cliente['razon_social']}",
            $idCliente
        );
        set_flash('success', 'Cliente reclamado correctamente');
        $this->redirect('/clientes');
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
        $result = $this->cliente->search($search, $page, $perPage, (int) $_SESSION['user_id']);
        $data = [
            'clientes' => $result['data'],
            'pageTitle' => 'Mis Clientes',
            'currentPage' => $result['page'],
            'totalPages' => $result['totalPages'],
            'total' => $result['total'],
            'search' => $search,
        ];
        $this->view('clientes/index', $data);
    }
}

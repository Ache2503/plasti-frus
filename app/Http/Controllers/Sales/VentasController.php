<?php
namespace App\Http\Controllers\Sales;

use App\Core\Controller;
use App\Core\Database;
use App\Services\VentaService;
use App\Repositories\VentaRepository;
use App\Repositories\ClienteRepository;
use App\Repositories\ProductoRepository;
use App\Http\Requests\VentaRequest;

class VentasController extends Controller
{
    private VentaService $ventaService;
    private VentaRepository $ventaRepository;
    private ClienteRepository $clienteRepository;
    private ProductoRepository $productoRepository;

    public function __construct()
    {
        $this->ventaService = new VentaService();
        $this->ventaRepository = new VentaRepository();
        $this->clienteRepository = new ClienteRepository();
        $this->productoRepository = new ProductoRepository();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        if (!in_array(user_rol(), [1, 3, ROL_VENDEDOR, 6])) {
            set_flash('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('/');
        }
    }

    public function index(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $params = [];
        $where = '';
        if (es_vendedor()) {
            $uid = (int) $_SESSION['user_id'];
            $where = 'WHERE (v.id_vendedor = :vendedor OR c.id_vendedor = :vendedor2)';
            $params['vendedor'] = $uid;
            $params['vendedor2'] = $uid;
        }
        $query = "
            SELECT v.*, c.razon_social as cliente, p.nombre as producto_nombre,
                   t.folio_unico, t.id_ticket,
                   CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            LEFT JOIN tickets t ON t.id_venta = v.id_venta
            LEFT JOIN usuarios u ON v.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            {$where}
            ORDER BY v.id_venta DESC
        ";
        $pagination = paginate($db, $query, $params, 15);
        $this->view('ventas/index', [
            'ventas' => $pagination->items,
            'pageTitle' => 'Ventas',
            'pagination' => $pagination,
        ]);
    }

    public function create(): void
    {
        $this->checkAccess();
        $this->view('ventas/create', [
            'clientes' => $this->clienteRepository->all(),
            'productos' => $this->productoRepository->all(),
            'pageTitle' => 'Nueva Venta',
        ]);
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/ventas');
        }

        try {
            $request = new VentaRequest();
            $data = $request->validate();
            if (!$this->clienteRepository->find($data['id_cliente'])) {
                throw new \App\Exceptions\ValidationException(['id_cliente' => 'Cliente no válido']);
            }
            if (!$this->productoRepository->find($data['id_producto'])) {
                throw new \App\Exceptions\ValidationException(['id_producto' => 'Producto no válido']);
            }
            $data['fecha_venta'] = $this->postParam('fecha_venta') ?: date('Y-m-d');
            $data['moneda'] = $this->postParam('moneda') ?: 'MXN';
            $data['condiciones_pago'] = $this->postParam('condiciones_pago');
            $data['estatus'] = $this->postParam('estatus') ?: 'completado';
            if (es_vendedor()) {
                $data['id_vendedor'] = $_SESSION['user_id'];
            }

            $idVenta = $this->ventaService->create($data);
            registrar_log('crear_venta', 'venta', $idVenta, "Cliente #{$data['id_cliente']}, Producto #{$data['id_producto']}");
            set_flash('success', 'Venta registrada correctamente');
            $this->redirect('/ventas');
        } catch (\App\Exceptions\ValidationException $e) {
            set_flash('error', $e->getFirstError());
            $this->redirect('/ventas/create');
        }
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        $venta = $this->ventaRepository->find($params['id']);
        if (!$venta) {
            set_flash('error', 'Venta no encontrada');
            $this->redirect('/ventas');
        }
        $this->view('ventas/edit', [
            'venta' => $venta,
            'clientes' => $this->clienteRepository->all(),
            'productos' => $this->productoRepository->all(),
            'pageTitle' => 'Editar Venta',
        ]);
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/ventas');
        }

        try {
            $request = new VentaRequest();
            $data = $request->validate();
            if (!$this->ventaRepository->find($params['id'])) {
                set_flash('error', 'Venta no encontrada');
                $this->redirect('/ventas');
            }
            if (!$this->clienteRepository->find($data['id_cliente'])) {
                throw new \App\Exceptions\ValidationException(['id_cliente' => 'Cliente no válido']);
            }
            if (!$this->productoRepository->find($data['id_producto'])) {
                throw new \App\Exceptions\ValidationException(['id_producto' => 'Producto no válido']);
            }
            $data['fecha_venta'] = $this->postParam('fecha_venta') ?: date('Y-m-d');
            $data['moneda'] = $this->postParam('moneda') ?: 'MXN';
            $data['condiciones_pago'] = $this->postParam('condiciones_pago');
            $data['estatus'] = $this->postParam('estatus') ?: 'completado';

            $this->ventaService->update((int) $params['id'], $data);
            set_flash('success', 'Venta actualizada correctamente');
            $this->redirect('/ventas');
        } catch (\App\Exceptions\ValidationException $e) {
            set_flash('error', $e->getFirstError());
            $this->redirect('/ventas/edit/' . $params['id']);
        }
    }

    public function delete(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/ventas');
        }
        $this->ventaService->delete((int) $params['id']);
        set_flash('success', 'Venta eliminada correctamente');
        $this->redirect('/ventas');
    }
}

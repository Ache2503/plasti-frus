<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Ticket;

class VentasController extends Controller
{
    private function checkVentasAccess(): void
    {
        $this->requireAuth();
        if (!in_array(user_rol(), [1, 3, ROL_VENDEDOR, 6])) {
            set_flash('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('/');
        }
    }

    public function index(): void
    {
        $this->checkVentasAccess();
        $db = \App\Core\Database::getInstance();
        $params = [];
        $where = '';
        if (es_vendedor()) {
            $uid = (int) $_SESSION['user_id'];
            $where = 'WHERE (v.id_vendedor = :vendedor OR c.id_vendedor = :vendedor2)';
            $params['vendedor'] = $uid;
            $params['vendedor2'] = $uid;
        }
        $ventas = $db->fetchAll("
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
        ", $params);
        $data = [
            'ventas' => $ventas,
            'pageTitle' => 'Ventas',
        ];
        $this->view('ventas/index', $data);
    }

    public function create(): void
    {
        $this->checkVentasAccess();
        $clienteModel = new Cliente();
        $productoModel = new Producto();
        $data = [
            'clientes' => $clienteModel->all(),
            'productos' => $productoModel->all(),
            'pageTitle' => 'Nueva Venta',
        ];
        $this->view('ventas/create', $data);
    }

    public function store(): void
    {
        $this->checkVentasAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/ventas');
        }
        $errors = validate($_POST, [
            'id_cliente' => 'required',
            'id_producto' => 'required',
            'cantidad_vendida' => 'required|positive',
            'precio_unitario' => 'required|positive',
        ]);
        if (!empty($errors)) {
            $first = reset($errors);
            set_flash('error', is_array($first) ? $first[0] : $first);
            $this->redirect('/ventas/create');
        }
        $db = \App\Core\Database::getInstance();
        $data = [
            'id_cliente' => $this->postParam('id_cliente'),
            'id_producto' => $this->postParam('id_producto'),
            'cantidad_vendida' => $this->postParam('cantidad_vendida'),
            'fecha_venta' => $this->postParam('fecha_venta') ?: date('Y-m-d'),
            'precio_unitario' => $this->postParam('precio_unitario'),
            'moneda' => $this->postParam('moneda') ?: 'MXN',
            'condiciones_pago' => $this->postParam('condiciones_pago'),
            'estatus' => $this->postParam('estatus') ?: 'completado',
        ];
        if (es_vendedor()) {
            $data['id_vendedor'] = $_SESSION['user_id'];
        }
        $idVenta = $db->insert('ventas', $data);

        $idVendedorComision = $data['id_vendedor'] ?? null;
        if (!$idVendedorComision && !empty($data['id_cliente'])) {
            $clienteData = $db->fetchOne("SELECT id_vendedor FROM clientes WHERE id_cliente = :id", ['id' => $data['id_cliente']]);
            $idVendedorComision = $clienteData['id_vendedor'] ?? null;
        }
        if ($idVendedorComision) {
            $total = (float) $data['cantidad_vendida'] * (float) $data['precio_unitario'];
            $porcentaje = COMISION_PORCENTAJE;
            $monto = round($total * $porcentaje / 100, 2);
            $db->insert('comisiones_vendedor', [
                'id_vendedor' => $idVendedorComision,
                'id_venta' => $idVenta,
                'monto_comision' => $monto,
                'porcentaje_comision' => $porcentaje,
                'estatus' => 'pendiente',
                'fecha_calculo' => date('Y-m-d'),
            ]);
            notificar_vendedor($idVendedorComision, 'comision_calculada',
                'Comisión calculada',
                "Comisión de {$monto} ({$porcentaje}%) generada por venta #{$idVenta}",
                $idVenta
            );
        }

        $productoModel = new Producto();
        $producto = $productoModel->find($data['id_producto']);
        $ticketModel = new Ticket();
        $ventaCompleta = $data;
        $ventaCompleta['id_venta'] = $idVenta;
        $ticketModel->createFromVenta($ventaCompleta, $producto ?: []);

        registrar_log('crear_venta', 'venta', $idVenta, "Cliente #{$data['id_cliente']}, Producto #{$data['id_producto']}");
        set_flash('success', 'Venta registrada correctamente');
        $this->redirect('/ventas');
    }

    public function edit(array $params): void
    {
        $this->checkVentasAccess();
        $db = \App\Core\Database::getInstance();
        $venta = $db->fetchOne("SELECT * FROM ventas WHERE id_venta = :id", ['id' => $params['id']]);
        if (!$venta) {
            set_flash('error', 'Venta no encontrada');
            $this->redirect('/ventas');
        }
        $clienteModel = new Cliente();
        $productoModel = new Producto();
        $data = [
            'venta' => $venta,
            'clientes' => $clienteModel->all(),
            'productos' => $productoModel->all(),
            'pageTitle' => 'Editar Venta',
        ];
        $this->view('ventas/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkVentasAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/ventas');
        }
        $errors = validate($_POST, [
            'id_cliente' => 'required',
            'id_producto' => 'required',
            'cantidad_vendida' => 'required|positive',
            'precio_unitario' => 'required|positive',
        ]);
        if (!empty($errors)) {
            $first = reset($errors);
            set_flash('error', is_array($first) ? $first[0] : $first);
            $this->redirect('/ventas/edit/' . $params['id']);
        }
        $db = \App\Core\Database::getInstance();
        $data = [
            'id_cliente' => $this->postParam('id_cliente'),
            'id_producto' => $this->postParam('id_producto'),
            'cantidad_vendida' => $this->postParam('cantidad_vendida'),
            'fecha_venta' => $this->postParam('fecha_venta') ?: date('Y-m-d'),
            'precio_unitario' => $this->postParam('precio_unitario'),
            'moneda' => $this->postParam('moneda') ?: 'MXN',
            'condiciones_pago' => $this->postParam('condiciones_pago'),
            'estatus' => $this->postParam('estatus') ?: 'completado',
        ];
        $db->update('ventas', $data, 'id_venta = :id', ['id' => $params['id']]);
        set_flash('success', 'Venta actualizada correctamente');
        $this->redirect('/ventas');
    }

    public function delete(array $params): void
    {
        $this->checkVentasAccess();
        $db = \App\Core\Database::getInstance();
        $db->delete('ventas', 'id_venta = :id', ['id' => $params['id']]);
        set_flash('success', 'Venta eliminada correctamente');
        $this->redirect('/ventas');
    }
}

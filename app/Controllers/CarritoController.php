<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ticket;

class CarritoController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function index(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Solo clientes pueden acceder al carrito');
            $this->redirect('/');
        }
        $db = \App\Core\Database::getInstance();
        $items = [];
        $total = 0;
        foreach ($_SESSION['cart'] as $key => $item) {
            $producto = $db->fetchOne("SELECT * FROM productos WHERE id_producto = :id", ['id' => $item['id_producto']]);
            if ($producto) {
                $items[] = [
                    'key' => $key,
                    'producto' => $producto,
                    'cantidad' => $item['cantidad'],
                    'subtotal' => $item['cantidad'] * $item['precio_unitario'],
                ];
                $total += $item['cantidad'] * $item['precio_unitario'];
            }
        }
        $data = [
            'items' => $items,
            'total' => $total,
            'pageTitle' => 'Mi Carrito',
        ];
        $this->view('home/carrito', $data);
    }

    public function agregar(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Solo clientes pueden agregar productos');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $idProducto = (int) $this->postParam('id_producto');
        $cantidad = max(1, (int) $this->postParam('cantidad', 1));
        $precio = (float) $this->postParam('precio_unitario', 0);

        if (!$idProducto || $precio <= 0) {
            set_flash('error', 'Producto inválido');
            $this->redirect('/');
        }

        $db = \App\Core\Database::getInstance();
        $producto = $db->fetchOne("SELECT * FROM productos WHERE id_producto = :id", ['id' => $idProducto]);
        if (!$producto) {
            set_flash('error', 'Producto no encontrado');
            $this->redirect('/');
        }

        if ($producto['stock_actual'] > 0 && $cantidad > $producto['stock_actual']) {
            set_flash('error', "Solo hay {$producto['stock_actual']} unidad(es) disponible(s) de este producto");
            $this->redirect('/');
        }

        $existe = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id_producto'] === $idProducto) {
                $nuevaCantidad = $item['cantidad'] + $cantidad;
                if ($producto['stock_actual'] > 0 && $nuevaCantidad > $producto['stock_actual']) {
                    set_flash('error', "No hay suficiente stock. Tienes {$item['cantidad']} en tu carrito, disponible: {$producto['stock_actual']}");
                    $this->redirect('/');
                }
                $item['cantidad'] = $nuevaCantidad;
                $existe = true;
                break;
            }
        }
        unset($item);

        if (!$existe) {
            $_SESSION['cart'][] = [
                'id_producto' => $idProducto,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
            ];
        }

        set_flash('success', 'Producto agregado al carrito');
        $this->redirect('/catalogo');
    }

    public function eliminar(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/');
        }
        $key = $params['key'];
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            set_flash('success', 'Producto eliminado del carrito');
        }
        $this->redirect('/carrito');
    }

    public function actualizar(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/carrito');
        }
        $key = $params['key'];
        $cantidad = max(1, (int) $this->postParam('cantidad', 1));

        if (isset($_SESSION['cart'][$key])) {
            $idProducto = $_SESSION['cart'][$key]['id_producto'];
            $db = \App\Core\Database::getInstance();
            $producto = $db->fetchOne("SELECT * FROM productos WHERE id_producto = :id", ['id' => $idProducto]);
            if ($producto && $producto['stock_actual'] > 0 && $cantidad > $producto['stock_actual']) {
                set_flash('error', "Solo hay {$producto['stock_actual']} unidad(es) disponible(s)");
                $this->redirect('/carrito');
            }
            $_SESSION['cart'][$key]['cantidad'] = $cantidad;
            set_flash('success', 'Cantidad actualizada');
        }
        $this->redirect('/carrito');
    }

    public function checkout(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/carrito');
        }
        if (empty($_SESSION['cart'])) {
            set_flash('error', 'El carrito está vacío');
            $this->redirect('/carrito');
        }

        $idCliente = user_id_cliente();
        if (!$idCliente) {
            set_flash('error', 'No tienes un perfil de cliente vinculado');
            $this->redirect('/carrito');
        }

        $db = \App\Core\Database::getInstance();
        $productoModel = new \App\Models\Producto();
        $ticketModel = new Ticket();
        $ventasCreadas = 0;
        $totalPedido = 0;

        $db->beginTransaction();
        try {
            foreach ($_SESSION['cart'] as $item) {
                $producto = $productoModel->find($item['id_producto']);
                if (!$producto) continue;
                if ($producto['stock_actual'] > 0 && $item['cantidad'] > $producto['stock_actual']) {
                    throw new \Exception("Stock insuficiente para {$producto['nombre']}. Disponible: {$producto['stock_actual']}");
                }
                $totalPedido += $item['cantidad'] * $item['precio_unitario'];
            }

            $folio = 'PED-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $idPedido = $db->insert('pedidos', [
                'id_cliente' => $idCliente,
                'folio' => $folio,
                'total' => $totalPedido,
                'estatus' => 'pendiente',
            ]);

            $db->insert('pedidos_historial', [
                'id_pedido' => $idPedido,
                'estatus' => 'pendiente',
                'comentario' => 'Pedido creado',
            ]);

            foreach ($_SESSION['cart'] as $item) {
                $producto = $productoModel->find($item['id_producto']);
                if (!$producto) continue;

                if ($producto['stock_actual'] > 0) {
                    $db->update('productos', [
                        'stock_actual' => $producto['stock_actual'] - $item['cantidad']
                    ], 'id_producto = :id', ['id' => $item['id_producto']]);
                }

                $idVenta = $db->insert('ventas', [
                    'id_cliente' => $idCliente,
                    'id_pedido' => $idPedido,
                    'id_producto' => $item['id_producto'],
                    'cantidad_vendida' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'moneda' => APP_CURRENCY,
                    'fecha_venta' => date('Y-m-d'),
                    'estatus' => 'pendiente',
                ]);

                $ventaCompleta = [
                    'id_venta' => $idVenta,
                    'id_cliente' => $idCliente,
                    'id_producto' => $item['id_producto'],
                    'cantidad_vendida' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'moneda' => APP_CURRENCY,
                    'estatus' => 'pendiente',
                ];
                $ticketModel->createFromVenta($ventaCompleta, $producto);
                registrar_log('compra_cliente', 'venta', $idVenta, "Cliente #{$idCliente} compró {$item['cantidad']} x {$producto['nombre']} (Pedido #{$idPedido})");
                $ventasCreadas++;
            }

            $db->commit();
            $_SESSION['cart'] = [];
            set_flash('success', "Pedido #{$idPedido} realizado ({$ventasCreadas} producto(s)). Folio: {$folio}. Tus productos están en estatus 'pendiente' hasta que sean procesados.");
        } catch (\Exception $e) {
            $db->rollback();
            set_flash('error', 'Error al procesar la compra: ' . $e->getMessage());
        }
        $this->redirect('/mis-compras');
    }
}

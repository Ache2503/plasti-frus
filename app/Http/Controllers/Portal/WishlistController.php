<?php
namespace App\Http\Controllers\Portal;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    private Database $db;
    private Wishlist $wishlist;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->wishlist = new Wishlist();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        $idCliente = user_id_cliente();
        $productos = $idCliente ? $this->wishlist->getByCliente($idCliente) : [];

        $data = [
            'pageTitle' => 'Mis Favoritos',
            'productos' => $productos,
        ];
        $this->view('portal.wishlist.index', $data);
    }

    public function toggle(array $params): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token inválido']);
            exit;
        }

        $idCliente = user_id_cliente();
        $idProducto = (int) ($params['productoId'] ?? 0);

        if (!$idCliente || !$idProducto) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        $agregado = $this->wishlist->toggle($idCliente, $idProducto);
        $count = $this->wishlist->countByCliente($idCliente);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'agregado' => $agregado,
            'count' => $count,
        ]);
        exit;
    }

    public function remove(array $params): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/wishlist');
        }

        $idCliente = user_id_cliente();
        $idProducto = (int) ($params['productoId'] ?? 0);

        if ($idCliente && $idProducto) {
            $this->wishlist->remove($idCliente, $idProducto);
        }

        set_flash('success', 'Producto eliminado de favoritos');
        $this->redirect('/wishlist');
    }

    public function agregarTodos(): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/wishlist');
        }

        $idCliente = user_id_cliente();
        if (!$idCliente) {
            set_flash('error', 'Cliente no válido');
            $this->redirect('/wishlist');
        }

        $items = $this->wishlist->getAllByCliente($idCliente);
        if (empty($items)) {
            set_flash('error', 'No tienes productos en favoritos');
            $this->redirect('/wishlist');
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $count = 0;
        foreach ($items as $item) {
            $producto = $this->db->fetchOne(
                "SELECT * FROM productos WHERE id_producto = :id",
                ['id' => $item['id_producto']]
            );
            if (!$producto || empty($producto['precio_venta'])) continue;
            if ($producto['stock_actual'] !== null && $producto['stock_actual'] <= 0) continue;

            $existe = false;
            foreach ($_SESSION['cart'] as &$cartItem) {
                if ((int)$cartItem['id_producto'] === (int)$item['id_producto']) {
                    $cartItem['cantidad']++;
                    $existe = true;
                    break;
 }
            }
            unset($cartItem);

            if (!$existe) {
                $_SESSION['cart'][] = [
                    'id_producto' => (int)$item['id_producto'],
                    'cantidad' => 1,
                    'precio_unitario' => (float)$producto['precio_venta'],
                ];
            }
            $count++;
        }

        set_flash('success', "{$count} producto(s) agregados al carrito desde favoritos");
        $this->redirect('/carrito');
    }
}

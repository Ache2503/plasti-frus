<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $idCliente = user_id_cliente();
        $wishlistModel = new Wishlist();
        $productos = $idCliente ? $wishlistModel->getByCliente($idCliente) : [];

        $data = [
            'pageTitle' => 'Mis Favoritos',
            'productos' => $productos,
        ];
        $this->view('portal/wishlist/index', $data);
    }

    public function toggle(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
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

        $wishlistModel = new Wishlist();
        $agregado = $wishlistModel->toggle($idCliente, $idProducto);
        $count = $wishlistModel->countByCliente($idCliente);

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
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/wishlist');
        }

        $idCliente = user_id_cliente();
        $idProducto = (int) ($params['productoId'] ?? 0);

        if ($idCliente && $idProducto) {
            $wishlistModel = new Wishlist();
            $wishlistModel->remove($idCliente, $idProducto);
        }

        set_flash('success', 'Producto eliminado de favoritos');
        $this->redirect('/wishlist');
    }
}

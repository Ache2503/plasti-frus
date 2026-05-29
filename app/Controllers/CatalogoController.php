<?php
namespace App\Controllers;

use App\Core\Controller;

class CatalogoController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $db = \App\Core\Database::getInstance();

        $search = trim($this->getParam('q', ''));
        $familia = trim($this->getParam('familia', ''));
        $linea = trim($this->getParam('linea', ''));
        $sort = trim($this->getParam('sort', 'nombre'));
        $order = trim($this->getParam('order', 'asc'));
        $page = max(1, (int) $this->getParam('page', 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE (publicar_web = 1 OR publicar_web IS NULL)";
        $params = [];

        if ($search) {
            $where .= " AND (nombre LIKE :q OR codigo LIKE :q2 OR descripcion_comercial LIKE :q3 OR familia LIKE :q4)";
            $params['q'] = "%{$search}%";
            $params['q2'] = "%{$search}%";
            $params['q3'] = "%{$search}%";
            $params['q4'] = "%{$search}%";
        }
        if ($familia) {
            $where .= " AND familia = :familia";
            $params['familia'] = $familia;
        }
        if ($linea) {
            $where .= " AND linea = :linea";
            $params['linea'] = $linea;
        }

        $sortColumns = ['nombre', 'precio_venta'];
        $sort = in_array($sort, $sortColumns) ? $sort : 'nombre';
        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';

        $groupExpr = "COALESCE(NULLIF(TRIM(codigo), ''), CONCAT('ID-', id_producto))";
        $total = (int) $db->fetchOne("
            SELECT COUNT(*) as c
            FROM (
                SELECT MIN(id_producto)
                FROM productos {$where}
                GROUP BY {$groupExpr}
            ) productos_unicos
        ", $params)['c'];
        $totalPages = max(1, ceil($total / $perPage));

        $productos = $db->fetchAll(
            "SELECT p.*
             FROM productos p
             INNER JOIN (
                SELECT MIN(id_producto) as id_producto
                FROM productos {$where}
                GROUP BY {$groupExpr}
             ) productos_unicos ON productos_unicos.id_producto = p.id_producto
             ORDER BY p.{$sort} {$order}
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $familias = $db->fetchAll("SELECT DISTINCT familia FROM productos WHERE familia IS NOT NULL AND (publicar_web = 1 OR publicar_web IS NULL) ORDER BY familia");
        $lineas = $db->fetchAll("SELECT DISTINCT linea FROM productos WHERE linea IS NOT NULL AND (publicar_web = 1 OR publicar_web IS NULL) ORDER BY linea");

        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));
        $wishlistIds = [];
        if (es_cliente() && function_exists('user_id_cliente')) {
            $cid = user_id_cliente();
            if ($cid) {
                $wlRows = $db->fetchAll("SELECT id_producto FROM wishlist WHERE id_cliente = :id", ['id' => $cid]);
                $wishlistIds = array_column($wlRows, 'id_producto');
            }
        }

        $data = [
            'productos' => $productos,
            'carrito_count' => $carritoCount,
            'pageTitle' => 'Catálogo de Productos',
            'search' => $search,
            'familia_filtro' => $familia,
            'linea_filtro' => $linea,
            'sort' => $sort,
            'order' => $order,
            'familias' => $familias,
            'lineas' => $lineas,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'wishlist_ids' => $wishlistIds,
        ];
        $this->view('home/catalogo', $data);
    }

    public function show(array $params): void
    {
        $this->requireAuth();
        $id = (int) ($params['id'] ?? 0);
        if (!$id) {
            set_flash('error', 'Producto no encontrado');
            $this->redirect('/catalogo');
        }

        $db = \App\Core\Database::getInstance();
        $producto = $db->fetchOne("SELECT * FROM productos WHERE id_producto = :id", ['id' => $id]);

        if (!$producto) {
            set_flash('error', 'Producto no encontrado');
            $this->redirect('/catalogo');
        }

        $recomendados = $db->fetchAll("
            SELECT * FROM productos
            WHERE id_producto != :id
              AND (publicar_web = 1 OR publicar_web IS NULL)
              AND (
                   (familia IS NOT NULL AND familia = :familia)
                OR (linea IS NOT NULL AND linea = :linea)
              )
            ORDER BY RAND()
            LIMIT 4
        ", [
            'id' => $id,
            'familia' => $producto['familia'] ?? '',
            'linea' => $producto['linea'] ?? '',
        ]);

        if (empty($recomendados)) {
            $recomendados = $db->fetchAll("
                SELECT * FROM productos
                WHERE id_producto != :id
                  AND (publicar_web = 1 OR publicar_web IS NULL)
                ORDER BY RAND()
                LIMIT 4
            ", ['id' => $id]);
        }

        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));
        $data = [
            'producto' => $producto,
            'recomendados' => $recomendados,
            'carrito_count' => $carritoCount,
            'pageTitle' => $producto['nombre'],
        ];
        $this->view('home/producto', $data);
    }
}

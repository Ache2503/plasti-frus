<?php
namespace App\Http\Controllers\Portal;

use App\Core\Controller;
use App\Core\Database;

class PedidosController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        $idCliente = user_id_cliente();
        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));

        $total_compras = ['total' => 0, 'monto' => 0];
        $mis_pedidos = [];
        $historialPedidos = [];

        if ($idCliente) {
            $total_compras = $this->db->fetchOne("
                SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto
                FROM ventas WHERE id_cliente = :id
            ", ['id' => $idCliente]) ?: ['total' => 0, 'monto' => 0];

            $this->db->query("SET SESSION group_concat_max_len = 100000");

            $mis_pedidos = $this->db->fetchAll("
                SELECT p.*,
                       (SELECT COUNT(*) FROM ventas v2 WHERE v2.id_pedido = p.id_pedido) as total_productos,
                       (SELECT GROUP_CONCAT(CONCAT(pr.nombre, ' x', v3.cantidad_vendida) SEPARATOR ', ')
                        FROM ventas v3
                        LEFT JOIN productos pr ON v3.id_producto = pr.id_producto
                        WHERE v3.id_pedido = p.id_pedido) as productos_resumen
                FROM pedidos p
                WHERE p.id_cliente = :id
                ORDER BY p.created_at DESC
            ", ['id' => $idCliente]);

            if (!empty($mis_pedidos)) {
                $ids = array_column($mis_pedidos, 'id_pedido');
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $historialRows = $this->db->fetchAll(
                    "SELECT * FROM pedidos_historial WHERE id_pedido IN ({$placeholders}) ORDER BY id_pedido, created_at ASC",
                    $ids
                );
                foreach ($historialRows as $h) {
                    $historialPedidos[$h['id_pedido']][] = $h;
                }
            }
        }

        $data = [
            'pageTitle' => 'Mis Pedidos',
            'mis_pedidos' => $mis_pedidos,
            'historial_pedidos' => $historialPedidos,
            'total_compras' => $total_compras,
            'carrito_count' => $carritoCount,
        ];
        $this->view('portal.pedidos.index', $data);
    }

    public function detalle(array $params): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        $idPedido = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        $pedido = $this->db->fetchOne("
            SELECT p.*, c.razon_social, c.rfc, c.domicilio, c.ciudad, c.estado
            FROM pedidos p
            LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
            WHERE p.id_pedido = :id AND p.id_cliente = :cliente
        ", ['id' => $idPedido, 'cliente' => $idCliente]);

        if (!$pedido) {
            set_flash('error', 'Pedido no encontrado');
            $this->redirect('/mis-pedidos');
        }

        $ventas = $this->db->fetchAll("
            SELECT v.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                   p.descripcion_comercial, p.familia, p.linea, t.folio_unico
            FROM ventas v
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            LEFT JOIN tickets t ON t.id_venta = v.id_venta
            WHERE v.id_pedido = :id
            ORDER BY v.fecha_venta ASC
        ", ['id' => $idPedido]);

        $historial = $this->db->fetchAll("
            SELECT * FROM pedidos_historial
            WHERE id_pedido = :id
            ORDER BY created_at ASC
        ", ['id' => $idPedido]);

        $carritoCount = array_sum(array_column($_SESSION['cart'] ?? [], 'cantidad'));

        $data = [
            'pageTitle' => 'Pedido #' . $pedido['id_pedido'],
            'pedido' => $pedido,
            'ventas' => $ventas,
            'historial' => $historial,
            'carrito_count' => $carritoCount,
        ];
        $this->view('portal.pedidos.detalle', $data);
    }
}

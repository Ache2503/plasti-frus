<?php
namespace App\Services;

use App\Models\Producto;
use App\Models\Ticket;
use App\Core\Database;

class VentaService
{
    private Database $db;
    private ComisionService $comisionService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->comisionService = new ComisionService();
    }

    public function getAll(array $filters = []): array
    {
        $params = [];
        $where = '';

        if (es_vendedor()) {
            $uid = (int) $_SESSION['user_id'];
            $where = 'WHERE (v.id_vendedor = :vendedor OR c.id_vendedor = :vendedor2)';
            $params['vendedor'] = $uid;
            $params['vendedor2'] = $uid;
        }

        return $this->db->fetchAll("
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
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM ventas WHERE id_venta = :id", ['id' => $id]);
    }

    public function create(array $data): int
    {
        $idVenta = $this->db->insert('ventas', $data);
        $this->comisionService->syncForVenta($idVenta);
        $this->generateTicket($idVenta, $data);
        return $idVenta;
    }

    public function update(int $id, array $data): void
    {
        $this->db->update('ventas', $data, 'id_venta = :id', ['id' => $id]);
        $this->comisionService->syncForVenta($id);
    }

    public function delete(int $id): void
    {
        $this->comisionService->deleteByVenta($id);
        $this->db->delete('ventas', 'id_venta = :id', ['id' => $id]);
    }

    private function generateTicket(int $idVenta, array $data): void
    {
        $productoModel = new Producto();
        $producto = $productoModel->find($data['id_producto']);
        $ticketModel = new Ticket();
        $ventaCompleta = $data;
        $ventaCompleta['id_venta'] = $idVenta;
        $ticketModel->createFromVenta($ventaCompleta, $producto ?: []);
    }
}

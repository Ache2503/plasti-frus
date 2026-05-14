<?php
namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';

    public function all(): array
    {
        return $this->fetchAll("SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY {$this->primaryKey} DESC");
    }

    public function find($id)
    {
        return $this->fetchOne("
            SELECT c.*, CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre
            FROM {$this->table} c
            LEFT JOIN usuarios u ON c.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE c.{$this->primaryKey} = :id AND c.activo = 1
        ", ['id' => $id]);
    }

    public function delete($id): int
    {
        return $this->db->update(
            $this->table,
            ['activo' => 0],
            "{$this->primaryKey} = :primaryKey",
            ['primaryKey' => $id]
        );
    }

    public function count(): int
    {
        $result = $this->fetchOne("SELECT COUNT(*) as total FROM {$this->table} WHERE activo = 1");
        return (int) ($result['total'] ?? 0);
    }

    public function search(string $term, int $page = 1, int $perPage = 10, ?int $vendedorId = null): array
    {
        $offset = ($page - 1) * $perPage;
        $where = 'WHERE c.activo = 1';
        $params = [];
        if (!empty($term)) {
            $where .= " AND (c.razon_social LIKE :t1 OR c.rfc LIKE :t2 OR c.ciudad LIKE :t3 OR c.correo LIKE :t4 OR c.telefono LIKE :t5)";
            $params['t1'] = "%{$term}%";
            $params['t2'] = "%{$term}%";
            $params['t3'] = "%{$term}%";
            $params['t4'] = "%{$term}%";
            $params['t5'] = "%{$term}%";
        }
        if ($vendedorId !== null) {
            $where .= " AND c.id_vendedor = :vendedor";
            $params['vendedor'] = $vendedorId;
        }
        $data = $this->fetchAll("
            SELECT c.*, CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre
            FROM {$this->table} c
            LEFT JOIN usuarios u ON c.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            {$where}
            ORDER BY c.{$this->primaryKey} DESC
            LIMIT :lim OFFSET :off
        ", array_merge($params, ['lim' => $perPage, 'off' => $offset]));
        $countResult = $this->fetchOne(
            "SELECT COUNT(*) as total FROM {$this->table} c {$where}",
            $params
        );
        $total = (int) ($countResult['total'] ?? 0);
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $total ? (int) ceil($total / $perPage) : 1,
        ];
    }

    public function getVentasByCliente($id)
    {
        return $this->fetchAll("
            SELECT v.*, p.nombre as producto_nombre
            FROM ventas v
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            WHERE v.id_cliente = :id
            ORDER BY v.fecha_venta DESC
            LIMIT 20
        ", ['id' => $id]);
    }

    public function getDevolucionesByCliente($id)
    {
        return $this->fetchAll("
            SELECT d.*, p.nombre as producto_nombre
            FROM devoluciones_clientes d
            LEFT JOIN productos p ON d.id_producto = p.id_producto
            WHERE d.id_cliente = :id
            ORDER BY d.fecha_devolucion DESC
        ", ['id' => $id]);
    }

    public function getCotizacionesByCliente($id)
    {
        return $this->fetchAll("
            SELECT c.*, p.nombre as producto_nombre
            FROM cotizaciones_clientes c
            LEFT JOIN productos p ON c.id_producto = p.id_producto
            WHERE c.id_cliente = :id
            ORDER BY c.fecha_cotizacion DESC
        ", ['id' => $id]);
    }

    public function getSectores(): array
    {
        return $this->fetchAll("SELECT DISTINCT sector FROM clientes WHERE sector IS NOT NULL AND activo = 1 ORDER BY sector");
    }

    public function getTotalClientes(): int
    {
        return $this->count();
    }
}

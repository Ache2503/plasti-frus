<?php
namespace App\Repositories;

class VentaRepository extends BaseRepository
{
    protected string $table = 'ventas';
    protected string $primaryKey = 'id_venta';

    public function allWithRelations(): array
    {
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
            ORDER BY v.id_venta DESC
        ");
    }

    public function findByVendedor(int $idVendedor): array
    {
        return $this->db->fetchAll("
            SELECT v.*, c.razon_social as cliente, p.nombre as producto_nombre
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            WHERE v.id_vendedor = :id
            ORDER BY v.id_venta DESC
        ", ['id' => $idVendedor]);
    }

    public function findByCliente(int $idCliente): array
    {
        return $this->db->fetchAll("
            SELECT v.*, p.nombre as producto_nombre, t.folio_unico
            FROM ventas v
            LEFT JOIN productos p ON v.id_producto = p.id_producto
            LEFT JOIN tickets t ON t.id_venta = v.id_venta
            WHERE v.id_cliente = :id
            ORDER BY v.id_venta DESC
        ", ['id' => $idCliente]);
    }
}

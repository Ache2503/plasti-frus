<?php
namespace App\Models;

use App\Core\Model;

class Wishlist extends Model
{
    protected $table = 'wishlist';
    protected $primaryKey = 'id_wishlist';

    public function getByCliente(int $idCliente): array
    {
        return $this->fetchAll("
            SELECT w.*, p.nombre, p.codigo, p.precio_venta,
                   p.descripcion_comercial, p.familia, p.linea
            FROM {$this->table} w
            INNER JOIN productos p ON w.id_producto = p.id_producto
            WHERE w.id_cliente = :id AND (p.publicar_web = 1 OR p.publicar_web IS NULL)
            ORDER BY w.created_at DESC
        ", ['id' => $idCliente]);
    }

    public function toggle(int $idCliente, int $idProducto): bool
    {
        $existe = $this->fetchOne(
            "SELECT id_wishlist FROM {$this->table} WHERE id_cliente = :c AND id_producto = :p",
            ['c' => $idCliente, 'p' => $idProducto]
        );
        if ($existe) {
            $this->db->delete($this->table, 'id_wishlist = :id', ['id' => $existe['id_wishlist']]);
            return false;
        }
        $this->db->insert($this->table, [
            'id_cliente' => $idCliente,
            'id_producto' => $idProducto,
        ]);
        return true;
    }

    public function getAllByCliente(int $idCliente): array
    {
        return $this->fetchAll(
            "SELECT id_producto FROM {$this->table} WHERE id_cliente = :id",
            ['id' => $idCliente]
        );
    }

    public function remove(int $idCliente, int $idProducto): void
    {
        $this->db->delete($this->table, 'id_cliente = :c AND id_producto = :p', ['c' => $idCliente, 'p' => $idProducto]);
    }

    public function productoEnWishlist(int $idCliente, int $idProducto): bool
    {
        $r = $this->fetchOne(
            "SELECT id_wishlist FROM {$this->table} WHERE id_cliente = :c AND id_producto = :p",
            ['c' => $idCliente, 'p' => $idProducto]
        );
        return (bool) $r;
    }

    public function countByCliente(int $idCliente): int
    {
        return (int) ($this->fetchOne(
            "SELECT COUNT(*) as c FROM {$this->table} WHERE id_cliente = :id",
            ['id' => $idCliente]
        )['c'] ?? 0);
    }

    public function idsByCliente(int $idCliente): array
    {
        $rows = $this->fetchAll(
            "SELECT id_producto FROM {$this->table} WHERE id_cliente = :id",
            ['id' => $idCliente]
        );
        return array_column($rows, 'id_producto');
    }
}

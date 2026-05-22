<?php
namespace App\Repositories;

class ProductoRepository extends BaseRepository
{
    protected string $table = 'productos';
    protected string $primaryKey = 'id_producto';

    public function allWithRelations(): array
    {
        return $this->db->fetchAll("
            SELECT *
            FROM productos
            ORDER BY id_producto DESC
        ");
    }

    public function findWithRelations(int $id): ?array
    {
        return $this->db->fetchOne("
            SELECT *
            FROM productos
            WHERE id_producto = :id
        ", ['id' => $id]);
    }

    public function getCatalogo(): array
    {
        return $this->db->fetchAll("
            SELECT *
            FROM productos
            WHERE activo = 1 OR activo IS NULL
            ORDER BY nombre
        ");
    }
}

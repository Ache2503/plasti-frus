<?php
namespace App\Repositories;

class ClienteRepository extends BaseRepository
{
    protected string $table = 'clientes';
    protected string $primaryKey = 'id_cliente';

    public function findWithVendedor(int $id): ?array
    {
        return $this->db->fetchOne("
            SELECT c.*, CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre
            FROM clientes c
            LEFT JOIN usuarios u ON c.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE c.id_cliente = :id
        ", ['id' => $id]);
    }

    public function allWithVendedor(): array
    {
        return $this->db->fetchAll("
            SELECT c.*, CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre
            FROM clientes c
            LEFT JOIN usuarios u ON c.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            ORDER BY c.id_cliente DESC
        ");
    }

    public function findByVendedor(int $idVendedor): array
    {
        return $this->db->fetchAll(
            "SELECT c.* FROM clientes c
             WHERE c.id_vendedor = :id
             ORDER BY c.razon_social",
            ['id' => $idVendedor]
        );
    }

    public function search(string $term): array
    {
        return $this->db->fetchAll("
            SELECT * FROM clientes
            WHERE razon_social LIKE :term OR rfc LIKE :term2 OR email LIKE :term3
            ORDER BY razon_social LIMIT 20
        ", [
            'term' => "%{$term}%",
            'term2' => "%{$term}%",
            'term3' => "%{$term}%",
        ]);
    }
}

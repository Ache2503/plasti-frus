<?php
namespace App\Repositories;

class ProveedorRepository extends BaseRepository
{
    protected string $table = 'proveedores';
    protected string $primaryKey = 'id_proveedor';

    public function search(string $term): array
    {
        return $this->db->fetchAll("
            SELECT * FROM proveedores
            WHERE nombre_empresa LIKE :term OR rfc LIKE :term2 OR email LIKE :term3
            ORDER BY nombre_empresa LIMIT 20
        ", [
            'term' => "%{$term}%",
            'term2' => "%{$term}%",
            'term3' => "%{$term}%",
        ]);
    }
}

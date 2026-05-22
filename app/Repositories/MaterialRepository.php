<?php
namespace App\Repositories;

class MaterialRepository extends BaseRepository
{
    protected string $table = 'materiales';
    protected string $primaryKey = 'id_material';

    public function allWithStock(): array
    {
        return $this->db->fetchAll("
            SELECT m.*,
                   (SELECT SUM(cantidad) FROM kardex WHERE id_material = m.id_material) as total_movimientos
            FROM materiales m
            ORDER BY m.id_material DESC
        ");
    }

    public function findLowStock(float $threshold = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM materiales WHERE stock_actual <= :threshold ORDER BY stock_actual ASC",
            ['threshold' => $threshold]
        );
    }
}

<?php
namespace App\Services;

use App\Core\Database;

class KardexService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        return $this->db->fetchAll("
            SELECT k.*, m.nombre as material_nombre
            FROM kardex_materiales k
            LEFT JOIN materiales m ON k.id_material = m.id_material
            ORDER BY k.id_kardex DESC
        ");
    }

    public function getByMaterial(int $idMaterial): array
    {
        return $this->db->fetchAll("
            SELECT * FROM kardex_materiales WHERE id_material = :id ORDER BY created_at ASC
        ", ['id' => $idMaterial]);
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->db->insert('kardex_materiales', $data);

            $material = $this->db->fetchOne(
                "SELECT * FROM materiales WHERE id_material = :id",
                ['id' => $data['id_material']]
            );

            $cantidad = $data['cantidad'];
            if (($data['movimiento'] ?? '') === 'salida') {
                $cantidad = -$cantidad;
            }

            $stockColumn = $this->db->columnExists('materiales', 'stock_actual_kg') ? 'stock_actual_kg' : 'stock_actual';
            $nuevoStock = (float) ($material[$stockColumn] ?? 0) + $cantidad;
            $this->db->update('materiales', [$stockColumn => $nuevoStock], 'id_material = :id', ['id' => $data['id_material']]);

            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        $this->db->delete('kardex_materiales', 'id_kardex = :id', ['id' => $id]);
    }
}

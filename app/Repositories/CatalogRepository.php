<?php
namespace App\Repositories;

use App\Core\Database;

class CatalogRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function tiposMantenimiento(): array
    {
        return $this->activeRows('tipos_mantenimiento', 'id_tipo_mantenimiento');
    }

    public function motivosParo(): array
    {
        return $this->activeRows('motivos_paro', 'id_motivo_paro');
    }

    public function motivosRechazo(): array
    {
        return $this->activeRows('motivos_rechazo', 'id_motivo_rechazo');
    }

    public function findTipoMantenimiento(int $id): ?array
    {
        return $this->findActive('tipos_mantenimiento', 'id_tipo_mantenimiento', $id);
    }

    public function findMotivoParo(int $id): ?array
    {
        return $this->findActive('motivos_paro', 'id_motivo_paro', $id);
    }

    public function findMotivoRechazo(int $id): ?array
    {
        return $this->findActive('motivos_rechazo', 'id_motivo_rechazo', $id);
    }

    private function activeRows(string $table, string $primaryKey): array
    {
        if (!$this->db->tableExists($table)) {
            return [];
        }

        return $this->db->fetchAll("
            SELECT *
            FROM {$table}
            WHERE activo = 1
            ORDER BY nombre
        ");
    }

    private function findActive(string $table, string $primaryKey, int $id): ?array
    {
        if ($id <= 0 || !$this->db->tableExists($table)) {
            return null;
        }

        return $this->db->fetchOne("
            SELECT *
            FROM {$table}
            WHERE {$primaryKey} = :id AND activo = 1
        ", ['id' => $id]) ?: null;
    }
}

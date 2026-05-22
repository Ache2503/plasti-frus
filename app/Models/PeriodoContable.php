<?php
namespace App\Models;

use App\Core\Model;

class PeriodoContable extends Model
{
    protected $table = 'periodos_contables';
    protected $primaryKey = 'id_periodo';

    public function getAll(): array
    {
        return $this->fetchAll("
            SELECT p.*, u.nombre_usuario
            FROM periodos_contables p
            LEFT JOIN usuarios u ON p.cerrado_por = u.id_usuario
            ORDER BY p.anio DESC, p.mes DESC
        ");
    }

    public function getCurrent(): ?array
    {
        $r = $this->fetchOne(
            "SELECT * FROM periodos_contables WHERE mes = :m AND anio = :a",
            ['m' => date('m'), 'a' => date('Y')]
        );
        return $r ?: null;
    }

    public function isClosed(int $mes, int $anio): bool
    {
        $r = $this->fetchOne(
            "SELECT cerrado FROM periodos_contables WHERE mes = :m AND anio = :a",
            ['m' => $mes, 'a' => $anio]
        );
        return $r ? (bool) $r['cerrado'] : false;
    }

    public function close(int $id, int $userId): void
    {
        $this->db->update($this->table, [
            'cerrado' => 1,
            'fecha_cierre' => date('Y-m-d H:i:s'),
            'cerrado_por' => $userId,
        ], 'id_periodo = :id', ['id' => $id]);
    }

    public function reopen(int $id): void
    {
        $this->db->update($this->table, [
            'cerrado' => 0,
            'fecha_cierre' => null,
            'cerrado_por' => null,
        ], 'id_periodo = :id', ['id' => $id]);
    }
}

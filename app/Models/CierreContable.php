<?php
namespace App\Models;

use App\Core\Model;

class CierreContable extends Model
{
    protected $table = 'cierres_contables';
    protected $primaryKey = 'id';

    public function getAll(): array
    {
        return $this->fetchAll("
            SELECT c.*, u.nombre_usuario
            FROM cierres_contables c
            LEFT JOIN usuarios u ON c.cerrado_por = u.id_usuario
            ORDER BY c.anio DESC, c.mes DESC
        ");
    }

    public function isClosed(int $anio, int $mes): bool
    {
        return (bool) $this->fetchOne(
            "SELECT id FROM cierres_contables WHERE anio = :a AND mes = :m AND tipo = 'mensual'",
            ['a' => $anio, 'm' => $mes]
        );
    }

    public function closePeriod(int $anio, int $mes, int $userId, string $observaciones = null): int
    {
        return $this->db->insert($this->table, [
            'anio' => $anio,
            'mes' => $mes,
            'tipo' => 'mensual',
            'cerrado_por' => $userId,
            'observaciones' => $observaciones,
        ]);
    }

    public function reopenPeriod(int $anio, int $mes): void
    {
        $this->db->delete($this->table,
            'anio = :a AND mes = :m AND tipo = :t',
            ['a' => $anio, 'm' => $mes, 't' => 'mensual']
        );
    }

    public function deleteById(int $id): void
    {
        $this->db->delete($this->table, 'id = :id', ['id' => $id]);
    }
}

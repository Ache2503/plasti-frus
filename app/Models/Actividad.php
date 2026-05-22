<?php
namespace App\Models;

use App\Core\Model;

class Actividad extends Model
{
    protected $table = 'actividades';
    protected $primaryKey = 'id_actividad';

    public const TIPOS = ['cita', 'llamada', 'tarea', 'recordatorio'];

    public function findByVendedor(int $vendedorId, ?string $fecha = null, string $estado = 'pendiente'): array
    {
        $where = 'WHERE id_vendedor = :vendedor';
        $params = ['vendedor' => $vendedorId];
        if ($fecha) {
            $where .= ' AND DATE(fecha_hora) = :fecha';
            $params['fecha'] = $fecha;
        }
        if ($estado) {
            $where .= ' AND estado = :estado';
            $params['estado'] = $estado;
        }
        return $this->fetchAll("
            SELECT * FROM {$this->table} {$where}
            ORDER BY fecha_hora ASC
        ", $params);
    }

    public function getProximas(int $vendedorId, int $limite = 5): array
    {
        return $this->fetchAll("
            SELECT * FROM {$this->table}
            WHERE id_vendedor = :vendedor AND estado = 'pendiente' AND fecha_hora >= NOW()
            ORDER BY fecha_hora ASC
            LIMIT :limite
        ", ['vendedor' => $vendedorId, 'limite' => $limite]);
    }

    public function getByMonth(int $vendedorId, string $yearMonth): array
    {
        return $this->fetchAll("
            SELECT * FROM {$this->table}
            WHERE id_vendedor = :vendedor
              AND DATE_FORMAT(fecha_hora, '%Y-%m') = :ym
            ORDER BY fecha_hora ASC
        ", ['vendedor' => $vendedorId, 'ym' => $yearMonth]);
    }

    public function contarPendientes(int $vendedorId): int
    {
        $r = $this->fetchOne("
            SELECT COUNT(*) as total FROM {$this->table}
            WHERE id_vendedor = :vendedor AND estado = 'pendiente' AND fecha_hora >= NOW()
        ", ['vendedor' => $vendedorId]);
        return (int) ($r['total'] ?? 0);
    }
}

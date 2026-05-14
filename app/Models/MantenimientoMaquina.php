<?php
namespace App\Models;

use App\Core\Model;

class MantenimientoMaquina extends Model
{
    protected $table = 'mantenimientos_maquinas';
    protected $primaryKey = 'id_mantenimiento';

    public function getWithMaquina(array $filters = [])
    {
        $sql = "
            SELECT m.*, maq.nombre as maquina_nombre, maq.modelo as maquina_modelo
            FROM mantenimientos_maquinas m
            LEFT JOIN maquinas maq ON m.id_maquina = maq.id_maquina
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "m.fecha_mantenimiento >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "m.fecha_mantenimiento <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['tipo_mantenimiento'])) {
            $where[] = "m.tipo_mantenimiento = :tipo";
            $params['tipo'] = $filters['tipo_mantenimiento'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY m.fecha_mantenimiento DESC';

        return $this->fetchAll($sql, $params);
    }

    public function getPendientes()
    {
        return $this->fetchAll("
            SELECT m.*, maq.nombre as maquina_nombre
            FROM plan_mantenimiento m
            LEFT JOIN maquinas maq ON m.id_maquina = maq.id_maquina
            WHERE m.estatus = 'pendiente' OR m.estatus IS NULL
            ORDER BY m.fecha_programada ASC
        ");
    }
}

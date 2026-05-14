<?php
namespace App\Models;

use App\Core\Model;

class IncidenciaProduccion extends Model
{
    protected $table = 'incidencias_produccion';
    protected $primaryKey = 'id_incidencia';

    public function getWithOrden(array $filters = [])
    {
        $sql = "
            SELECT i.*, oc.id_orden_cabe, p.nombre as producto_nombre
            FROM incidencias_produccion i
            LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "i.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "i.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['estatus'])) {
            $where[] = "i.estatus = :estatus";
            $params['estatus'] = $filters['estatus'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY i.fecha DESC';

        return $this->fetchAll($sql, $params);
    }
}

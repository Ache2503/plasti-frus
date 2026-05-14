<?php
namespace App\Models;

use App\Core\Model;

class InspeccionCalidad extends Model
{
    protected $table = 'inspecciones_calidad';
    protected $primaryKey = 'id_inspeccion';

    public function getWithRelations(array $filters = [])
    {
        $sql = "
            SELECT i.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                   o.id_orden_cabe, o.cantidad_planificada
            FROM inspecciones_calidad i
            LEFT JOIN productos p ON i.id_producto = p.id_producto
            LEFT JOIN ordenes_cabecera o ON i.id_orden = o.id_orden_cabe
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "i.fecha_inspeccion >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "i.fecha_inspeccion <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY i.fecha_inspeccion DESC';

        return $this->fetchAll($sql, $params);
    }

    public function getWithRelationsById($id)
    {
        return $this->fetchOne("
            SELECT i.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                   o.id_orden_cabe, o.cantidad_planificada, o.turno, o.fecha as orden_fecha
            FROM inspecciones_calidad i
            LEFT JOIN productos p ON i.id_producto = p.id_producto
            LEFT JOIN ordenes_cabecera o ON i.id_orden = o.id_orden_cabe
            WHERE i.id_inspeccion = :id
        ", ['id' => $id]);
    }
}

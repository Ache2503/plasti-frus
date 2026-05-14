<?php
namespace App\Models;

use App\Core\Model;

class RechazoCalidad extends Model
{
    protected $table = 'rechazos_calidad';
    protected $primaryKey = 'id_rechazo';

    public function getWithProducto(array $filters = [])
    {
        $sql = "
            SELECT r.*, p.nombre as producto_nombre, p.codigo as producto_codigo
            FROM rechazos_calidad r
            LEFT JOIN productos p ON r.id_producto = p.id_producto
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "r.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "r.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY r.fecha DESC';

        return $this->fetchAll($sql, $params);
    }
}

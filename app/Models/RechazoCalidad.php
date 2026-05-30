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
            SELECT r.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, r.inspector) as inspector_nombre,
                   COALESCE(mr.nombre, r.motivo_rechazo) as motivo_rechazo_nombre
            FROM rechazos_calidad r
            LEFT JOIN productos p ON r.id_producto = p.id_producto
            LEFT JOIN usuarios u ON r.id_inspector = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            LEFT JOIN motivos_rechazo mr ON r.id_motivo_rechazo = mr.id_motivo_rechazo
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

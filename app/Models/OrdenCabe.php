<?php
namespace App\Models;

use App\Core\Model;

class OrdenCabe extends Model
{
    protected $table = 'ordenes_cabecera';
    protected $primaryKey = 'id_orden_cabe';

    public function getWithRelations(array $filters = [])
    {
        $sql = "
            SELECT oc.*, 
                   p.nombre as producto_nombre, p.codigo as producto_codigo,
                   m.nombre as maquina_nombre,
                   md.nombre_molde as molde_nombre,
                   rc.version as receta_version
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
            LEFT JOIN moldes md ON oc.id_molde = md.id_molde
            LEFT JOIN recetas_cabecera rc ON oc.id_receta = rc.id_receta_cabe
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "oc.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "oc.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['turno'])) {
            $where[] = "oc.turno = :turno";
            $params['turno'] = $filters['turno'];
        }
        if (!empty($filters['id_producto'])) {
            $where[] = "oc.id_producto = :id_producto";
            $params['id_producto'] = $filters['id_producto'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY oc.id_orden_cabe DESC';

        return $this->fetchAll($sql, $params);
    }

    public function getByIdWithRelations($id)
    {
        return $this->fetchOne("
            SELECT oc.*, 
                   p.nombre as producto_nombre, p.codigo as producto_codigo, p.descripcion_comercial,
                   m.nombre as maquina_nombre, m.modelo as maquina_modelo,
                   md.nombre_molde as molde_nombre, md.numero_cavidades,
                   rc.version as receta_version, rc.temperatura_inyeccion_C, rc.presion_inyeccion_bar, rc.tiempo_enfriamiento_s
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
            LEFT JOIN moldes md ON oc.id_molde = md.id_molde
            LEFT JOIN recetas_cabecera rc ON oc.id_receta = rc.id_receta_cabe
            WHERE oc.id_orden_cabe = :id
        ", ['id' => $id]);
    }

    public function getMermasByOrden($id)
    {
        return $this->fetchAll("
            SELECT * FROM ordenes_merma WHERE id_orden_cabe = :id
        ", ['id' => $id]);
    }

    public function getSeguimientoByOrden($id)
    {
        return $this->fetchAll("
            SELECT * FROM seguimiento_ordenes WHERE id_orden_cabe = :id ORDER BY fecha DESC
        ", ['id' => $id]);
    }

    public function getByDateRange(string $start, string $end)
    {
        return $this->fetchAll("
            SELECT oc.*, p.nombre as producto_nombre
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE oc.fecha BETWEEN :start AND :end
            ORDER BY oc.fecha DESC
        ", ['start' => $start, 'end' => $end]);
    }

    public function getPending()
    {
        return $this->fetchAll("
            SELECT oc.*, p.nombre as producto_nombre
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE oc.cantidad_real_buenas IS NULL OR oc.cantidad_real_buenas = 0
            ORDER BY oc.fecha DESC
        ");
    }

    public function getStats()
    {
        $total = $this->count();
        $producidas = $this->fetchOne("SELECT SUM(cantidad_real_buenas) as total FROM ordenes_cabecera");
        $planificadas = $this->fetchOne("SELECT SUM(cantidad_planificada) as total FROM ordenes_cabecera");
        return [
            'total' => $total,
            'total_producidas' => (int) ($producidas['total'] ?? 0),
            'total_planificadas' => (int) ($planificadas['total'] ?? 0),
        ];
    }
}

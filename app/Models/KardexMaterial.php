<?php
namespace App\Models;

use App\Core\Model;

class KardexMaterial extends Model
{
    protected $table = 'kardex_materiales';
    protected $primaryKey = 'id_kardex';

    public function getWithMaterial(array $filters = [])
    {
        $sql = "
            SELECT k.*, m.nombre as material_nombre, m.tipo as material_tipo, m.unidad_medida,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, k.operador) as operador_nombre
            FROM kardex_materiales k
            LEFT JOIN materiales m ON k.id_material = m.id_material
            LEFT JOIN usuarios u ON k.id_operador = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "k.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "k.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['id_material'])) {
            $where[] = "k.id_material = :id_material";
            $params['id_material'] = $filters['id_material'];
        }
        if (!empty($filters['movimiento'])) {
            $where[] = "k.movimiento = :movimiento";
            $params['movimiento'] = $filters['movimiento'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY k.fecha DESC, k.id_kardex DESC';

        return $this->fetchAll($sql, $params);
    }

    public function getByMaterial($idMaterial)
    {
        return $this->fetchAll("
            SELECT k.*, m.nombre as material_nombre, m.unidad_medida,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, k.operador) as operador_nombre
            FROM kardex_materiales k
            LEFT JOIN materiales m ON k.id_material = m.id_material
            LEFT JOIN usuarios u ON k.id_operador = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE k.id_material = :id
            ORDER BY k.fecha ASC, k.id_kardex ASC
        ", ['id' => $idMaterial]);
    }
}

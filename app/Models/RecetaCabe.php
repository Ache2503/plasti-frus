<?php
namespace App\Models;

use App\Core\Model;

class RecetaCabe extends Model
{
    protected $table = 'recetas_cabecera';
    protected $primaryKey = 'id_receta_cabe';

    public function getWithRelations()
    {
        return $this->fetchAll("
            SELECT rc.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                   m.nombre as maquina_nombre
            FROM recetas_cabecera rc
            LEFT JOIN productos p ON rc.id_producto = p.id_producto
            LEFT JOIN maquinas m ON rc.id_maquina = m.id_maquina
            ORDER BY rc.id_receta_cabe DESC
        ");
    }

    public function getByIdWithRelations($id)
    {
        return $this->fetchOne("
            SELECT rc.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                   m.nombre as maquina_nombre
            FROM recetas_cabecera rc
            LEFT JOIN productos p ON rc.id_producto = p.id_producto
            LEFT JOIN maquinas m ON rc.id_maquina = m.id_maquina
            WHERE rc.id_receta_cabe = :id
        ", ['id' => $id]);
    }

    public function getDetallesByReceta($id)
    {
        return $this->fetchAll("
            SELECT rd.*, mat.nombre as material_nombre, mat.tipo as material_tipo,
                   mat.unidad_medida
            FROM recetas_detalle rd
            LEFT JOIN materiales mat ON rd.id_material = mat.id_material
            WHERE rd.id_receta_cabe = :id
            ORDER BY rd.id_receta_detalle ASC
        ", ['id' => $id]);
    }

    public function getHistorialCambios($id)
    {
        return $this->fetchAll("
            SELECT * FROM historial_cambios_recetas 
            WHERE id_receta_cabe = :id 
            ORDER BY fecha DESC
        ", ['id' => $id]);
    }

    public function addDetalle(array $data): int
    {
        $db = \App\Core\Database::getInstance();
        return $db->insert('recetas_detalle', $data);
    }

    public function removeDetalle(int $idDetalle): int
    {
        $db = \App\Core\Database::getInstance();
        return $db->delete('recetas_detalle', 'id_receta_detalle = :id', ['id' => $idDetalle]);
    }
}

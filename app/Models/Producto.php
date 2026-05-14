<?php
namespace App\Models;

use App\Core\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    public function getWithRelations()
    {
        return $this->fetchAll("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM recetas_cabecera rc WHERE rc.id_producto = p.id_producto) as total_recetas
            FROM productos p
            ORDER BY p.id_producto DESC
        ");
    }

    public function getByIdWithRelations($id)
    {
        return $this->fetchOne("
            SELECT p.* 
            FROM productos p
            WHERE p.id_producto = :id
        ", ['id' => $id]);
    }

    public function getFamilias(): array
    {
        return $this->fetchAll("SELECT DISTINCT familia FROM productos WHERE familia IS NOT NULL ORDER BY familia");
    }

    public function getLineas(): array
    {
        return $this->fetchAll("SELECT DISTINCT linea FROM productos WHERE linea IS NOT NULL ORDER BY linea");
    }

    public function getColores(): array
    {
        return $this->fetchAll("SELECT DISTINCT color FROM productos WHERE color IS NOT NULL ORDER BY color");
    }

    public function getRecetasByProducto($id)
    {
        return $this->fetchAll("
            SELECT rc.*, m.nombre as maquina_nombre
            FROM recetas_cabecera rc
            LEFT JOIN maquinas m ON rc.id_maquina = m.id_maquina
            WHERE rc.id_producto = :id
            ORDER BY rc.version DESC
        ", ['id' => $id]);
    }

    public function getOrdenesByProducto($id)
    {
        return $this->fetchAll("
            SELECT oc.*, m.nombre as maquina_nombre
            FROM ordenes_cabecera oc
            LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
            WHERE oc.id_producto = :id
            ORDER BY oc.fecha DESC
            LIMIT 20
        ", ['id' => $id]);
    }
}

<?php
namespace App\Models;

use App\Core\Model;

class Material extends Model
{
    protected $table = 'materiales';
    protected $primaryKey = 'id_material';

    public function getWithProveedor()
    {
        return $this->fetchAll("
            SELECT m.*, p.razon_social as proveedor
            FROM materiales m
            LEFT JOIN proveedores p ON m.id_proveedor = p.id_proveedor
            ORDER BY m.id_material DESC
        ");
    }

    public function getWithProveedorById($id)
    {
        return $this->fetchOne("
            SELECT m.*, p.razon_social as proveedor, p.telefono as proveedor_telefono, p.correo as proveedor_correo
            FROM materiales m
            LEFT JOIN proveedores p ON m.id_proveedor = p.id_proveedor
            WHERE m.id_material = :id
        ", ['id' => $id]);
    }

    public function getLowStock()
    {
        return $this->fetchAll("
            SELECT m.*, p.razon_social as proveedor
            FROM materiales m
            LEFT JOIN proveedores p ON m.id_proveedor = p.id_proveedor
            WHERE m.stock_actual_kg <= m.punto_reorden_kg
            ORDER BY (m.stock_actual_kg / m.punto_reorden_kg) ASC
        ");
    }

    public function getByTipo(string $tipo)
    {
        return $this->fetchAll("
            SELECT * FROM materiales WHERE tipo = :tipo ORDER BY nombre ASC
        ", ['tipo' => $tipo]);
    }

    public function updateStock(int $id, float $cantidad): int
    {
        $material = $this->find($id);
        if (!$material) return 0;
        $nuevoStock = $material['stock_actual_kg'] + $cantidad;
        return $this->update($id, ['stock_actual_kg' => $nuevoStock]);
    }

    public function getTipos(): array
    {
        return $this->fetchAll("SELECT DISTINCT tipo FROM materiales WHERE tipo IS NOT NULL ORDER BY tipo");
    }
}

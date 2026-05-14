<?php
namespace App\Models;

use App\Core\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';

    public function getMaterialesByProveedor($id)
    {
        return $this->fetchAll("
            SELECT * FROM materiales WHERE id_proveedor = :id ORDER BY nombre ASC
        ", ['id' => $id]);
    }

    public function getEvaluacionesByProveedor($id)
    {
        return $this->fetchAll("
            SELECT * FROM evaluacion_proveedores WHERE id_proveedor = :id ORDER BY fecha DESC
        ", ['id' => $id]);
    }

    public function getSectores(): array
    {
        return $this->fetchAll("SELECT DISTINCT sector FROM proveedores WHERE sector IS NOT NULL ORDER BY sector");
    }

    public function getEstatusList(): array
    {
        return $this->fetchAll("SELECT DISTINCT estatus FROM proveedores WHERE estatus IS NOT NULL ORDER BY estatus");
    }

    public function getTotalProveedores(): int
    {
        return $this->count();
    }
}

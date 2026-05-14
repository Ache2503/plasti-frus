<?php
namespace App\Models;

use App\Core\Model;

class Empleado extends Model
{
    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';

    public function getSinUsuario(): array
    {
        return $this->fetchAll("
            SELECT e.* FROM empleados e
            LEFT JOIN usuarios u ON e.id_empleado = u.id_empleado
            WHERE u.id_usuario IS NULL AND e.estatus = 'activo'
            ORDER BY e.nombre ASC
        ");
    }
}

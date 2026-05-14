<?php
namespace App\Models;

use App\Core\Model;

class Molde extends Model
{
    protected $table = 'moldes';
    protected $primaryKey = 'id_molde';

    public function getWithCede()
    {
        return $this->fetchAll("
            SELECT md.*, c.nombre_cede as cede_nombre, c.ubicacion as cede_ubicacion
            FROM moldes md
            LEFT JOIN cedes c ON md.id_cedes = c.id_cedes
            ORDER BY md.id_molde DESC
        ");
    }

    public function getByIdWithCede($id)
    {
        return $this->fetchOne("
            SELECT md.*, c.nombre_cede as cede_nombre
            FROM moldes md
            LEFT JOIN cedes c ON md.id_cedes = c.id_cedes
            WHERE md.id_molde = :id
        ", ['id' => $id]);
    }

    public function getMantenimientos($id)
    {
        return $this->fetchAll("
            SELECT * FROM mantenimientos_moldes WHERE id_molde = :id ORDER BY fecha DESC
        ", ['id' => $id]);
    }

    public function updateCiclos(int $id, int $ciclos): int
    {
        $molde = $this->find($id);
        if (!$molde) return 0;
        $nuevosCiclos = ($molde['ciclos_acumulados'] ?? 0) + $ciclos;
        return $this->update($id, ['ciclos_acumulados' => $nuevosCiclos]);
    }

    public function getAvailableCedes(): array
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetchAll("SELECT * FROM cedes ORDER BY nombre_cede ASC");
    }
}

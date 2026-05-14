<?php
namespace App\Models;

use App\Core\Model;

class Maquina extends Model
{
    protected $table = 'maquinas';
    protected $primaryKey = 'id_maquina';

    public function getMantenimientos($id)
    {
        return $this->fetchAll("
            SELECT * FROM mantenimientos_maquinas WHERE id_maquina = :id ORDER BY fecha_mantenimiento DESC
        ", ['id' => $id]);
    }

    public function getPlanMantenimiento($id)
    {
        return $this->fetchAll("
            SELECT * FROM plan_mantenimiento WHERE id_maquina = :id ORDER BY fecha_programada DESC
        ", ['id' => $id]);
    }

    public function getCalibraciones($id)
    {
        return $this->fetchAll("
            SELECT * FROM calibraciones_maquinas WHERE id_maquina = :id ORDER BY fecha_calibracion DESC
        ", ['id' => $id]);
    }

    public function getConsumosEnergia($id)
    {
        return $this->fetchAll("
            SELECT * FROM energia_consumo WHERE id_maquina = :id ORDER BY fecha DESC
        ", ['id' => $id]);
    }

    public function getIndicadoresOEE($id)
    {
        return $this->fetchAll("
            SELECT * FROM indicadores_oee WHERE id_maquina = :id ORDER BY fecha DESC LIMIT 30
        ", ['id' => $id]);
    }

    public function getBitacoraParos($id)
    {
        return $this->fetchAll("
            SELECT * FROM bitacora_paros WHERE id_maquina = :id ORDER BY fecha DESC, hora_inicio DESC
        ", ['id' => $id]);
    }

    public function getActiveMachines()
    {
        return $this->fetchAll("SELECT * FROM maquinas WHERE estatus = 'activo' OR estatus IS NULL ORDER BY nombre");
    }
}

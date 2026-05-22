<?php
namespace Database\Seeds;

class ProductionOrderSeeder extends Seeder
{
    public function run(): void
    {
        $productos = $this->db->fetchAll("SELECT id_producto, familia FROM productos");
        $prodIds = array_column($productos, 'id_producto');
        $maquinas = $this->db->fetchAll("SELECT id_maquina FROM maquinas");
        $maqIds = array_column($maquinas, 'id_maquina');
        $moldes = $this->db->fetchAll("SELECT id_molde FROM moldes");
        $molIds = array_column($moldes, 'id_molde');

        if (empty($prodIds)) $prodIds = [1];
        if (empty($maqIds)) $maqIds = [1];
        if (empty($molIds)) $molIds = [1];

        $turnos = ['Matutino', 'Vespertino', 'Nocturno'];
        $estados = ['pendiente', 'en_proceso', 'completada', 'cancelada'];

        $today = new \DateTime();

        for ($i = 1; $i <= 28; $i++) {
            $prodId = $prodIds[array_rand($prodIds)];
            $maqId = $maqIds[array_rand($maqIds)];
            $molId = $molIds[array_rand($molIds)];

            $diasAtras = rand(0, 29);
            $fecha = (clone $today)->modify("-{$diasAtras} days")->format('Y-m-d');
            $turno = $turnos[array_rand($turnos)];

            if ($diasAtras <= 2) {
                $estado = (rand(0, 1) === 0) ? 'en_proceso' : 'pendiente';
            } elseif ($diasAtras <= 7) {
                $estado = (rand(0, 3) === 0) ? 'en_proceso' : 'completada';
            } else {
                $r = rand(0, 10);
                if ($r === 0) $estado = 'cancelada';
                elseif ($r <= 2) $estado = 'pendiente';
                else $estado = 'completada';
            }

            $cantPlan = rand(3, 15) * 1000;

            if ($estado === 'completada') {
                $desviacion = rand(-5, 5);
                $cantReal = $cantPlan + (int)($cantPlan * $desviacion / 100);
                if ($cantReal < 0) $cantReal = 0;
            } else {
                $cantReal = null;
            }

            $this->insert('ordenes_cabecera', [
                'id_producto' => $prodId,
                'id_receta' => null,
                'id_molde' => $molId,
                'id_maquina' => $maqId,
                'cantidad_planificada' => $cantPlan,
                'cantidad_real_buenas' => $cantReal,
                'fecha' => $fecha,
                'turno' => $turno,
                'estatus' => $estado,
            ]);
        }
    }
}

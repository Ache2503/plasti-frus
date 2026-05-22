<?php
namespace Database\Seeds;

class InventoryMovementSeeder extends Seeder
{
    public function run(): void
    {
        $materiales = $this->db->fetchAll("SELECT id_material, nombre, stock_actual_kg, punto_reorden_kg FROM materiales");
        $materialIds = array_column($materiales, 'id_material');

        if (empty($materialIds)) return;

        $operadores = ['operador1', 'operador2', 'operador3', 'almacen1', 'almacen2', 'supervisor'];
        $today = new \DateTime();

        for ($i = 0; $i < 35; $i++) {
            $matId = $materialIds[array_rand($materialIds)];
            $matInfo = null;
            foreach ($materiales as $m) {
                if ($m['id_material'] == $matId) {
                    $matInfo = $m;
                    break;
                }
            }

            $diasAtras = rand(0, 29);
            $fecha = (clone $today)->modify("-{$diasAtras} days")->format('Y-m-d');
            $tipoMov = (rand(0, 1) === 0) ? 'Entrada' : 'Salida';

            if ($tipoMov === 'Entrada') {
                $cantidad = round(rand(1, 20) * 25, 2);
            } else {
                $cantidad = round(rand(1, 10) * 10, 2);
            }

            $stockBase = $matInfo ? (float)$matInfo['stock_actual_kg'] : 1000;
            $stockFinal = $stockBase + ($tipoMov === 'Entrada' ? $cantidad : -$cantidad);
            if ($stockFinal < 0) $stockFinal = 0;

            $op = $operadores[array_rand($operadores)];
            $estatus = (rand(0, 5) === 0) ? 'pendiente' : 'completado';

            $this->insert('kardex_materiales', [
                'id_material' => $matId,
                'fecha' => $fecha,
                'movimiento' => $tipoMov,
                'cantidad' => $cantidad,
                'stock_final' => $stockFinal,
                'operador' => $op,
                'estatus' => $estatus,
            ]);
        }
    }
}

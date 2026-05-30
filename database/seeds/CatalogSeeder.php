<?php
namespace Database\Seeds;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed('tipos_mantenimiento', [
            ['Preventivo', 'preventivo'],
            ['Correctivo', 'correctivo'],
            ['Predictivo', 'predictivo'],
            ['Calibracion', 'calibracion'],
        ]);
        $this->seed('motivos_paro', [
            ['Falla mecanica', 'falla_mecanica'],
            ['Falla electrica', 'falla_electrica'],
            ['Cambio de molde', 'cambio_molde'],
            ['Falta de material', 'falta_material'],
            ['Mantenimiento programado', 'mantenimiento_programado'],
            ['Otro', 'otro'],
        ]);
        $this->seed('motivos_rechazo', [
            ['Dimensiones fuera de especificacion', 'dimensiones_fuera_especificacion'],
            ['Defecto visual', 'defecto_visual'],
            ['Contaminacion de material', 'contaminacion_material'],
            ['Color incorrecto', 'color_incorrecto'],
            ['Rechazado en inspeccion', 'rechazado_en_inspeccion'],
            ['Otro', 'otro'],
        ]);
    }

    private function seed(string $table, array $rows): void
    {
        if (!$this->db->tableExists($table)) {
            return;
        }

        foreach ($rows as [$nombre, $slug]) {
            $this->db->query("
                INSERT INTO {$table} (nombre, slug, activo)
                SELECT :nombre, :slug, 1
                WHERE NOT EXISTS (SELECT 1 FROM {$table} WHERE slug = :slug_check)
            ", ['nombre' => $nombre, 'slug' => $slug, 'slug_check' => $slug]);
        }
    }
}

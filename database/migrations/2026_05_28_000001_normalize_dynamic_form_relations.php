<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class NormalizeDynamicFormRelations extends Migration
{
    public function up(): void
    {
        $this->createCatalogs();
        $this->addColumnIfMissing('mantenimientos_maquinas', 'id_tipo_mantenimiento', 'INT NULL AFTER tipo_mantenimiento');
        $this->addColumnIfMissing('mantenimientos_maquinas', 'id_tecnico_responsable', 'INT NULL AFTER tecnico_responsable');
        $this->addColumnIfMissing('plan_mantenimiento', 'id_tipo_mantenimiento', 'INT NULL AFTER tipo_mantenimiento');
        $this->addColumnIfMissing('plan_mantenimiento', 'id_tecnico_responsable', 'INT NULL AFTER tecnico_responsable');
        $this->addColumnIfMissing('bitacora_paros', 'id_motivo_paro', 'INT NULL AFTER motivo_paro');
        $this->addColumnIfMissing('bitacora_paros', 'id_operador', 'INT NULL AFTER operador');
        $this->addColumnIfMissing('kardex_materiales', 'id_operador', 'INT NULL AFTER operador');
        $this->addColumnIfMissing('inspecciones_calidad', 'id_inspector', 'INT NULL AFTER inspector');
        $this->addColumnIfMissing('rechazos_calidad', 'id_inspector', 'INT NULL AFTER inspector');
        $this->addColumnIfMissing('rechazos_calidad', 'id_motivo_rechazo', 'INT NULL AFTER motivo_rechazo');

        $this->backfillCatalogIds();
        $this->addIndexes();
        $this->addForeignKeys();
    }

    public function down(): void
    {
        // Se conservan columnas y catálogos para no romper datos ya capturados.
    }

    private function createCatalogs(): void
    {
        $this->schema("
            CREATE TABLE IF NOT EXISTS tipos_mantenimiento (
                id_tipo_mantenimiento INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->schema("
            CREATE TABLE IF NOT EXISTS motivos_paro (
                id_motivo_paro INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL UNIQUE,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->schema("
            CREATE TABLE IF NOT EXISTS motivos_rechazo (
                id_motivo_rechazo INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL UNIQUE,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->seedCatalog('tipos_mantenimiento', [
            ['Preventivo', 'preventivo'],
            ['Correctivo', 'correctivo'],
            ['Predictivo', 'predictivo'],
            ['Calibracion', 'calibracion'],
        ]);
        $this->seedCatalog('motivos_paro', [
            ['Falla mecanica', 'falla_mecanica'],
            ['Falla electrica', 'falla_electrica'],
            ['Cambio de molde', 'cambio_molde'],
            ['Falta de material', 'falta_material'],
            ['Mantenimiento programado', 'mantenimiento_programado'],
            ['Otro', 'otro'],
        ]);
        $this->seedCatalog('motivos_rechazo', [
            ['Dimensiones fuera de especificacion', 'dimensiones_fuera_especificacion'],
            ['Defecto visual', 'defecto_visual'],
            ['Contaminacion de material', 'contaminacion_material'],
            ['Color incorrecto', 'color_incorrecto'],
            ['Rechazado en inspeccion', 'rechazado_en_inspeccion'],
            ['Otro', 'otro'],
        ]);
    }

    private function seedCatalog(string $table, array $rows): void
    {
        foreach ($rows as [$nombre, $slug]) {
            $this->db->query("
                INSERT INTO {$table} (nombre, slug, activo)
                SELECT :nombre, :slug, 1
                WHERE NOT EXISTS (SELECT 1 FROM {$table} WHERE slug = :slug_check)
            ", ['nombre' => $nombre, 'slug' => $slug, 'slug_check' => $slug]);
        }
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        if ($this->db->tableExists($table) && !$this->db->columnExists($table, $column)) {
            $this->schema("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        }
    }

    private function backfillCatalogIds(): void
    {
        if ($this->db->columnExists('mantenimientos_maquinas', 'id_tipo_mantenimiento')) {
            $this->schema("
                UPDATE mantenimientos_maquinas m
                JOIN tipos_mantenimiento t ON LOWER(t.slug) = LOWER(REPLACE(m.tipo_mantenimiento, ' ', '_'))
                SET m.id_tipo_mantenimiento = t.id_tipo_mantenimiento
                WHERE m.id_tipo_mantenimiento IS NULL AND m.tipo_mantenimiento IS NOT NULL
            ");
        }
        if ($this->db->columnExists('plan_mantenimiento', 'id_tipo_mantenimiento')) {
            $this->schema("
                UPDATE plan_mantenimiento p
                JOIN tipos_mantenimiento t ON LOWER(t.slug) = LOWER(REPLACE(p.tipo_mantenimiento, ' ', '_'))
                SET p.id_tipo_mantenimiento = t.id_tipo_mantenimiento
                WHERE p.id_tipo_mantenimiento IS NULL AND p.tipo_mantenimiento IS NOT NULL
            ");
        }
        if ($this->db->columnExists('bitacora_paros', 'id_motivo_paro')) {
            $this->schema("
                UPDATE bitacora_paros b
                JOIN motivos_paro mp ON mp.slug = 'otro'
                SET b.id_motivo_paro = mp.id_motivo_paro
                WHERE b.id_motivo_paro IS NULL AND b.motivo_paro IS NOT NULL
            ");
        }
        if ($this->db->columnExists('rechazos_calidad', 'id_motivo_rechazo')) {
            $this->schema("
                UPDATE rechazos_calidad r
                JOIN motivos_rechazo mr ON mr.slug = 'otro'
                SET r.id_motivo_rechazo = mr.id_motivo_rechazo
                WHERE r.id_motivo_rechazo IS NULL AND r.motivo_rechazo IS NOT NULL
            ");
        }
    }

    private function addIndexes(): void
    {
        foreach ([
            ['mantenimientos_maquinas', 'id_tipo_mantenimiento'],
            ['mantenimientos_maquinas', 'id_tecnico_responsable'],
            ['plan_mantenimiento', 'id_tipo_mantenimiento'],
            ['plan_mantenimiento', 'id_tecnico_responsable'],
            ['bitacora_paros', 'id_motivo_paro'],
            ['bitacora_paros', 'id_operador'],
            ['kardex_materiales', 'id_operador'],
            ['inspecciones_calidad', 'id_inspector'],
            ['rechazos_calidad', 'id_inspector'],
            ['rechazos_calidad', 'id_motivo_rechazo'],
        ] as [$table, $column]) {
            if ($this->db->tableExists($table) && $this->db->columnExists($table, $column)) {
                $this->addIndexIfMissing($table, $column, "idx_{$table}_{$column}");
            }
        }
    }

    private function addForeignKeys(): void
    {
        $this->addForeignKeyIfMissing('mantenimientos_maquinas', 'fk_mant_tipo', 'id_tipo_mantenimiento', 'tipos_mantenimiento', 'id_tipo_mantenimiento', 'SET NULL');
        $this->addForeignKeyIfMissing('mantenimientos_maquinas', 'fk_mant_tecnico', 'id_tecnico_responsable', 'usuarios', 'id_usuario', 'SET NULL');
        $this->addForeignKeyIfMissing('plan_mantenimiento', 'fk_plan_tipo', 'id_tipo_mantenimiento', 'tipos_mantenimiento', 'id_tipo_mantenimiento', 'SET NULL');
        $this->addForeignKeyIfMissing('plan_mantenimiento', 'fk_plan_tecnico', 'id_tecnico_responsable', 'usuarios', 'id_usuario', 'SET NULL');
        $this->addForeignKeyIfMissing('bitacora_paros', 'fk_paros_motivo', 'id_motivo_paro', 'motivos_paro', 'id_motivo_paro', 'SET NULL');
        $this->addForeignKeyIfMissing('bitacora_paros', 'fk_paros_operador', 'id_operador', 'usuarios', 'id_usuario', 'SET NULL');
        $this->addForeignKeyIfMissing('kardex_materiales', 'fk_kardex_operador', 'id_operador', 'usuarios', 'id_usuario', 'SET NULL');
        $this->addForeignKeyIfMissing('inspecciones_calidad', 'fk_inspecciones_inspector', 'id_inspector', 'usuarios', 'id_usuario', 'SET NULL');
        $this->addForeignKeyIfMissing('rechazos_calidad', 'fk_rechazos_inspector', 'id_inspector', 'usuarios', 'id_usuario', 'SET NULL');
        $this->addForeignKeyIfMissing('rechazos_calidad', 'fk_rechazos_motivo', 'id_motivo_rechazo', 'motivos_rechazo', 'id_motivo_rechazo', 'SET NULL');
    }

    private function addIndexIfMissing(string $table, string $column, string $index): void
    {
        $exists = $this->db->fetchOne("
            SELECT COUNT(*) as total
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND INDEX_NAME = :index
        ", ['table' => $table, 'index' => $index]);
        if ((int) ($exists['total'] ?? 0) === 0) {
            $this->schema("ALTER TABLE {$table} ADD INDEX {$index} ({$column})");
        }
    }

    private function addForeignKeyIfMissing(string $table, string $constraint, string $column, string $refTable, string $refColumn, string $onDelete): void
    {
        if (!$this->db->tableExists($table) || !$this->db->columnExists($table, $column)) {
            return;
        }

        $exists = $this->db->fetchOne("
            SELECT COUNT(*) as total
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND CONSTRAINT_NAME = :constraint
        ", ['table' => $table, 'constraint' => $constraint]);
        if ((int) ($exists['total'] ?? 0) > 0) {
            return;
        }

        try {
            $this->schema("
                ALTER TABLE {$table}
                ADD CONSTRAINT {$constraint}
                FOREIGN KEY ({$column}) REFERENCES {$refTable}({$refColumn})
                ON DELETE {$onDelete}
            ");
        } catch (\Throwable $e) {
            // Datos históricos inválidos no deben bloquear la migración de columnas/catálogos.
        }
    }
}

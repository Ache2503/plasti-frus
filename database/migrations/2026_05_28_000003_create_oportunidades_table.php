<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class CreateOportunidadesTable extends Migration
{
    public function up(): void
    {
        $this->schema("
            CREATE TABLE IF NOT EXISTS oportunidades (
                id_oportunidad INT AUTO_INCREMENT PRIMARY KEY,
                id_vendedor INT NOT NULL,
                id_cliente INT DEFAULT NULL,
                titulo VARCHAR(255) NOT NULL,
                valor DECIMAL(12,2) DEFAULT 0,
                etapa VARCHAR(50) NOT NULL DEFAULT 'prospeccion',
                probabilidad INT DEFAULT 0,
                fecha_cierre_estimada DATE DEFAULT NULL,
                notas TEXT DEFAULT NULL,
                activo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_vendedor (id_vendedor),
                INDEX idx_etapa (etapa),
                INDEX idx_cliente (id_cliente)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->addForeignKeyIfMissing('oportunidades', 'fk_oportunidades_vendedor', 'id_vendedor', 'usuarios', 'id_usuario', 'CASCADE');
        $this->addForeignKeyIfMissing('oportunidades', 'fk_oportunidades_cliente', 'id_cliente', 'clientes', 'id_cliente', 'SET NULL');
    }

    public function down(): void
    {
        // Se conserva el pipeline para no perder oportunidades comerciales.
    }

    private function addForeignKeyIfMissing(string $table, string $constraint, string $column, string $refTable, string $refColumn, string $onDelete): void
    {
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
            // Datos históricos pueden requerir limpieza antes de activar la FK.
        }
    }
}

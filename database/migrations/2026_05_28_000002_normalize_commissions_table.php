<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class NormalizeCommissionsTable extends Migration
{
    public function up(): void
    {
        $this->addColumnIfMissing('ventas', 'id_vendedor', 'INT NULL AFTER id_cliente');
        $this->schema("
            CREATE TABLE IF NOT EXISTS comisiones_vendedor (
                id_comision INT AUTO_INCREMENT PRIMARY KEY,
                id_vendedor INT NOT NULL,
                id_venta INT NOT NULL,
                monto_comision DECIMAL(10,2) NOT NULL DEFAULT 0,
                porcentaje_comision DECIMAL(5,2) NOT NULL DEFAULT 0,
                estatus VARCHAR(20) DEFAULT 'pendiente',
                fecha_calculo DATE,
                fecha_pago DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_id_vendedor (id_vendedor),
                INDEX idx_id_venta (id_venta),
                INDEX idx_estatus (estatus)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->addIndexIfMissing('ventas', 'id_vendedor', 'idx_ventas_id_vendedor');
        $this->addForeignKeyIfMissing('ventas', 'fk_ventas_vendedor', 'id_vendedor', 'usuarios', 'id_usuario', 'SET NULL');
        $this->addForeignKeyIfMissing('comisiones_vendedor', 'fk_comisiones_vendedor', 'id_vendedor', 'usuarios', 'id_usuario', 'CASCADE');
        $this->addForeignKeyIfMissing('comisiones_vendedor', 'fk_comisiones_venta', 'id_venta', 'ventas', 'id_venta', 'CASCADE');

        $this->backfillMissingCommissions();
    }

    public function down(): void
    {
        // Se conserva el histórico de comisiones.
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        if ($this->db->tableExists($table) && !$this->db->columnExists($table, $column)) {
            $this->schema("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        }
    }

    private function addIndexIfMissing(string $table, string $column, string $index): void
    {
        if (!$this->db->tableExists($table) || !$this->db->columnExists($table, $column)) {
            return;
        }
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
            // Datos históricos pueden requerir limpieza antes de activar la FK.
        }
    }

    private function backfillMissingCommissions(): void
    {
        $porcentaje = (float) COMISION_PORCENTAJE;
        $this->db->query("
            INSERT INTO comisiones_vendedor (id_vendedor, id_venta, monto_comision, porcentaje_comision, estatus, fecha_calculo)
            SELECT COALESCE(v.id_vendedor, c.id_vendedor) as id_vendedor,
                   v.id_venta,
                   ROUND(COALESCE(v.cantidad_vendida, 0) * COALESCE(v.precio_unitario, 0) * :porcentaje / 100, 2) as monto_comision,
                   :porcentaje2,
                   CASE WHEN COALESCE(v.estatus, v.estado, '') = 'cancelado' THEN 'cancelada' ELSE 'pendiente' END,
                   COALESCE(v.fecha_venta, CURRENT_DATE)
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN comisiones_vendedor cv ON cv.id_venta = v.id_venta
            WHERE cv.id_comision IS NULL
              AND COALESCE(v.id_vendedor, c.id_vendedor) IS NOT NULL
              AND COALESCE(v.cantidad_vendida, 0) > 0
              AND COALESCE(v.precio_unitario, 0) > 0
        ", ['porcentaje' => $porcentaje, 'porcentaje2' => $porcentaje]);
    }
}

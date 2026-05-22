<?php
namespace App\Database\Migrations;

use App\Core\Database;

class CreateAuditLogTable
{
    public function up(): void
    {
        $db = Database::getInstance();
        $db->query("CREATE TABLE IF NOT EXISTS audit_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT UNSIGNED NULL,
            usuario_nombre VARCHAR(100) NOT NULL DEFAULT 'Sistema',
            accion VARCHAR(50) NOT NULL,
            entidad VARCHAR(100) NOT NULL,
            entidad_id VARCHAR(50) NULL,
            detalle TEXT NULL,
            ip VARCHAR(45) NULL,
            user_agent VARCHAR(500) NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_audit_log_accion (accion),
            INDEX idx_audit_log_entidad (entidad),
            INDEX idx_audit_log_created_at (created_at),
            INDEX idx_audit_log_usuario (usuario_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS audit_log");
    }
}

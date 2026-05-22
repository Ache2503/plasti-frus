<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class CreateNotificacionesClienteTable extends Migration
{
    public function up(): void
    {
        $this->schema("
            CREATE TABLE IF NOT EXISTS notificaciones_cliente (
                id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
                id_cliente INT NOT NULL,
                tipo VARCHAR(50) NOT NULL DEFAULT 'info',
                titulo VARCHAR(255) NOT NULL,
                mensaje TEXT NULL,
                id_referencia INT NULL,
                leida TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down(): void
    {
        $this->schema("DROP TABLE IF EXISTS notificaciones_cliente");
    }
}

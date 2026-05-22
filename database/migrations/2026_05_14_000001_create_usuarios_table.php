<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class CreateUsuariosTable extends Migration
{
    public function up(): void
    {
        $this->schema("
            CREATE TABLE IF NOT EXISTS usuarios (
                id_usuario INT AUTO_INCREMENT PRIMARY KEY,
                nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                id_rol INT NOT NULL,
                id_empleado INT NULL,
                id_cliente INT NULL,
                activo TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down(): void
    {
        $this->schema("DROP TABLE IF EXISTS usuarios");
    }
}

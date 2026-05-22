<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class CreateShiftLogsTable extends Migration
{
    public function up(): void
    {
        $this->schema("
            CREATE TABLE IF NOT EXISTS shift_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                operador_id INT NOT NULL,
                maquina_id INT NULL,
                turno ENUM('matutino','vespertino','nocturno') NOT NULL,
                fecha DATE NOT NULL,
                nota TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (operador_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
                FOREIGN KEY (maquina_id) REFERENCES maquinas(id_maquina) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down(): void
    {
        $this->schema("DROP TABLE IF EXISTS shift_logs");
    }
}

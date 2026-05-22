<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class CreateTicketsSoporteTable extends Migration
{
    public function up(): void
    {
        $this->schema("
            CREATE TABLE IF NOT EXISTS tickets_soporte (
                id_ticket INT AUTO_INCREMENT PRIMARY KEY,
                id_cliente INT NOT NULL,
                id_usuario INT NULL,
                titulo VARCHAR(255) NOT NULL,
                descripcion TEXT NOT NULL,
                prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
                estatus ENUM('abierto', 'respondido', 'cerrado') DEFAULT 'abierto',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->schema("
            CREATE TABLE IF NOT EXISTS ticket_respuestas (
                id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
                id_ticket INT NOT NULL,
                id_usuario INT NULL,
                es_cliente TINYINT(1) DEFAULT 1,
                mensaje TEXT NOT NULL,
                archivo VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_ticket) REFERENCES tickets_soporte(id_ticket) ON DELETE CASCADE,
                FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down(): void
    {
        $this->schema("DROP TABLE IF EXISTS ticket_respuestas");
        $this->schema("DROP TABLE IF EXISTS tickets_soporte");
    }
}

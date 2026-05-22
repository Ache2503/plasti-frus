<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class CreateDireccionesClienteTable extends Migration
{
    public function up(): void
    {
        $this->schema("
            CREATE TABLE IF NOT EXISTS direcciones_cliente (
                id_direccion INT AUTO_INCREMENT PRIMARY KEY,
                id_cliente INT NOT NULL,
                alias VARCHAR(100) NOT NULL,
                destinatario VARCHAR(255) NULL,
                telefono_contacto VARCHAR(20) NULL,
                calle VARCHAR(255) NOT NULL,
                numero_exterior VARCHAR(20) NULL,
                numero_interior VARCHAR(20) NULL,
                colonia VARCHAR(255) NULL,
                ciudad VARCHAR(100) NOT NULL,
                estado VARCHAR(100) NOT NULL,
                codigo_postal VARCHAR(10) NOT NULL,
                referencia TEXT NULL,
                predeterminada TINYINT(1) DEFAULT 0,
                activa TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down(): void
    {
        $this->schema("DROP TABLE IF EXISTS direcciones_cliente");
    }
}

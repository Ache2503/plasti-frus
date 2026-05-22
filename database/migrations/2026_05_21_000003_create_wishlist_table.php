<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class CreateWishlistTable extends Migration
{
    public function up(): void
    {
        $this->schema("
            CREATE TABLE IF NOT EXISTS wishlist (
                id_wishlist INT AUTO_INCREMENT PRIMARY KEY,
                id_cliente INT NOT NULL,
                id_producto INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_cliente_producto (id_cliente, id_producto),
                FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE,
                FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down(): void
    {
        $this->schema("DROP TABLE IF EXISTS wishlist");
    }
}

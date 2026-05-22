<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class AddEstadoToVentasTable extends Migration
{
    public function up(): void
    {
        $this->schema("ALTER TABLE ventas ADD COLUMN IF NOT EXISTS estado VARCHAR(50) DEFAULT 'completado' AFTER estatus");
    }

    public function down(): void
    {
        $this->schema("ALTER TABLE ventas DROP COLUMN IF EXISTS estado");
    }
}

<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class AddEstadoColumnaMaquinas extends Migration
{
    public function up(): void
    {
        $this->schema("ALTER TABLE maquinas ADD COLUMN IF NOT EXISTS estado ENUM('operando','setup','detenida','apagada','mantenimiento') DEFAULT 'apagada' AFTER estatus");
    }

    public function down(): void
    {
        $this->schema("ALTER TABLE maquinas DROP COLUMN IF EXISTS estado");
    }
}

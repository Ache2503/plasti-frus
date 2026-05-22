<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class AddAccesoAutorizadoToTurnoProduccion extends Migration
{
    public function up(): void
    {
        $this->schema("
            ALTER TABLE turno_produccion
            ADD COLUMN acceso_autorizado TINYINT(1) DEFAULT 0,
            ADD COLUMN acceso_autorizado_por INT DEFAULT NULL,
            ADD COLUMN acceso_autorizado_hasta DATETIME DEFAULT NULL
        ");
    }

    public function down(): void
    {
        $this->schema("
            ALTER TABLE turno_produccion
            DROP COLUMN acceso_autorizado,
            DROP COLUMN acceso_autorizado_por,
            DROP COLUMN acceso_autorizado_hasta
        ");
    }
}

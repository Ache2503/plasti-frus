<?php

namespace App\Database\Migrations;

use App\Core\Migration;

class AddAsignadoToTicketsSoporte extends Migration
{
    public function up(): void
    {
        $this->schema("
            ALTER TABLE tickets_soporte
            ADD COLUMN id_usuario_asignado INT NULL AFTER id_usuario,
            ADD FOREIGN KEY (id_usuario_asignado) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
        ");
    }

    public function down(): void
    {
        $this->schema("
            ALTER TABLE tickets_soporte
            DROP FOREIGN KEY tickets_soporte_ibfk_3,
            DROP COLUMN id_usuario_asignado
        ");
    }
}

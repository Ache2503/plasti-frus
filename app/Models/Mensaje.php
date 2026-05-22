<?php
namespace App\Models;

use App\Core\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';
    protected $primaryKey = 'id_mensaje';

    public function inbox(int $userId): array
    {
        return $this->fetchAll("
            SELECT m.*, 
                   CONCAT(e_rem.nombre, ' ', e_rem.apellido_paterno) as remitente_nombre,
                   u_rem.nombre_usuario as remitente_usuario
            FROM mensajes m
            LEFT JOIN usuarios u_rem ON m.de_user_id = u_rem.id_usuario
            LEFT JOIN empleados e_rem ON u_rem.id_empleado = e_rem.id_empleado
            WHERE m.para_user_id = :user
            ORDER BY m.created_at DESC
        ", ['user' => $userId]);
    }

    public function sent(int $userId): array
    {
        return $this->fetchAll("
            SELECT m.*,
                   CONCAT(e_dest.nombre, ' ', e_dest.apellido_paterno) as destinatario_nombre,
                   u_dest.nombre_usuario as destinatario_usuario
            FROM mensajes m
            LEFT JOIN usuarios u_dest ON m.para_user_id = u_dest.id_usuario
            LEFT JOIN empleados e_dest ON u_dest.id_empleado = e_dest.id_empleado
            WHERE m.de_user_id = :user
            ORDER BY m.created_at DESC
        ", ['user' => $userId]);
    }

    public function noLeidos(int $userId): int
    {
        $r = $this->fetchOne("
            SELECT COUNT(*) as total FROM mensajes
            WHERE para_user_id = :user AND leido = 0
        ", ['user' => $userId]);
        return (int) ($r['total'] ?? 0);
    }

    public function marcarLeido(int $id, int $userId): void
    {
        $this->db->update($this->table, ['leido' => 1], 'id_mensaje = :id AND para_user_id = :user', [
            'id' => $id, 'user' => $userId,
        ]);
    }
}

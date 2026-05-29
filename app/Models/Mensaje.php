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

    public function getDestinatarios(int $excludeUserId): array
    {
        return $this->fetchAll("
            SELECT u.id_usuario, u.nombre_usuario, u.id_rol,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario) as nombre_completo,
                   r.nombre as rol_nombre
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            LEFT JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.activo = 1
              AND u.id_usuario != :uid
              AND u.id_rol IN (1, 3, 4)
            ORDER BY r.id_rol, nombre_completo
        ", ['uid' => $excludeUserId]);
    }

    public function getUsuarioActivo(int $idUsuario): ?array
    {
        return $this->fetchOne("
            SELECT id_usuario, id_rol, nombre_usuario
            FROM usuarios
            WHERE id_usuario = :id AND activo = 1
        ", ['id' => $idUsuario]) ?: null;
    }
}

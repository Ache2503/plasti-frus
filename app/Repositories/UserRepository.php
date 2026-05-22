<?php
namespace App\Repositories;

class UserRepository extends BaseRepository
{
    protected string $table = 'usuarios';
    protected string $primaryKey = 'id_usuario';

    public function allWithEmpleado(): array
    {
        return $this->db->fetchAll("
            SELECT u.*, r.nombre as rol_nombre,
                   CONCAT(e.nombre, ' ', e.apellido_paterno) as empleado_nombre
            FROM usuarios u
            LEFT JOIN roles r ON u.id_rol = r.id_rol
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            ORDER BY u.id_usuario DESC
        ");
    }

    public function findWithEmpleado(int $id): ?array
    {
        return $this->db->fetchOne("
            SELECT u.*, r.nombre as rol_nombre,
                   e.nombre, e.apellido_paterno, e.telefono, e.email
            FROM usuarios u
            LEFT JOIN roles r ON u.id_rol = r.id_rol
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE u.id_usuario = :id
        ", ['id' => $id]);
    }

    public function findByRol(int $idRol): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM usuarios WHERE id_rol = :rol AND activo = 1",
            ['rol' => $idRol]
        );
    }
}

<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    public function authenticate(string $username, string $password)
    {
        $user = $this->whereFirst('nombre_usuario', $username);
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function getWithEmpleado($id)
    {
        return $this->fetchOne("
            SELECT u.*, e.nombre, e.apellido_paterno, e.apellido_materno, 
                   r.nombre as rol
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            LEFT JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.id_usuario = :id
        ", ['id' => $id]);
    }

    public function getActiveUsers()
    {
        return $this->fetchAll("
            SELECT u.*, e.nombre, e.apellido_paterno, r.nombre as rol
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            LEFT JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.activo = 1
            ORDER BY u.created_at DESC
        ");
    }

    public function createUser(array $data): int
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        return $this->create($data);
    }

    public function updatePassword(int $id, string $password): int
    {
        return $this->update($id, [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }

    public function getAllWithEmpleado(): array
    {
        return $this->fetchAll("
            SELECT u.*, e.nombre, e.apellido_paterno, e.apellido_materno,
                   r.nombre as rol, r.id_rol
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            LEFT JOIN roles r ON u.id_rol = r.id_rol
            ORDER BY u.id_usuario DESC
        ");
    }

    public function getRoles(): array
    {
        return $this->fetchAll("SELECT * FROM roles ORDER BY id_rol ASC");
    }
}

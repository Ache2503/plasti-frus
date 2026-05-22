<?php
namespace App\Services;

use App\Models\User;
use App\Core\Database;

class AuthService
{
    private User $userModel;
    private Database $db;

    public function __construct()
    {
        $this->userModel = new User();
        $this->db = Database::getInstance();
    }

    public function login(string $username, string $password): ?array
    {
        $user = $this->userModel->authenticate($username, $password);
        if (!$user) return null;

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['user_name'] = $user['nombre_usuario'];
        $_SESSION['user_rol'] = $user['id_rol'];

        if (!empty($user['id_cliente'])) {
            $_SESSION['user_id_cliente'] = $user['id_cliente'];
        }

        $userData = $this->userModel->getWithEmpleado($user['id_usuario']);
        if ($userData && $userData['nombre']) {
            $_SESSION['empleado_nombre'] = $userData['nombre'] . ' ' . $userData['apellido_paterno'];
            $_SESSION['rol_nombre'] = $userData['rol'];
        } else {
            $rolNombres = [1 => 'Administrador', 2 => 'Operador', 3 => 'Supervisor', 4 => 'Vendedor', 5 => 'Cliente'];
            $_SESSION['rol_nombre'] = $rolNombres[$user['id_rol']] ?? 'Usuario';
            $_SESSION['empleado_nombre'] = $user['nombre_usuario'];
        }

        return $user;
    }

    public function registerCliente(string $username, string $password, int $idCliente): int
    {
        return $this->userModel->createUser([
            'nombre_usuario' => $username,
            'password' => $password,
            'id_rol' => 5,
            'id_cliente' => $idCliente,
            'activo' => 1,
        ]);
    }

    public function registerVendedor(string $username, string $password, string $nombre, string $apellido): int
    {
        $idEmpleado = $this->db->insert('empleados', [
            'nombre' => $nombre,
            'apellido_paterno' => $apellido,
            'estatus' => 'activo',
        ]);

        return $this->userModel->createUser([
            'id_empleado' => $idEmpleado,
            'nombre_usuario' => $username,
            'password' => $password,
            'id_rol' => ROL_VENDEDOR,
            'activo' => 1,
        ]);
    }

    public function checkOperatorAccess(int $userId): array
    {
        $empleado = $this->db->fetchOne("
            SELECT e.id_empleado FROM usuarios u
            JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE u.id_usuario = :id AND u.id_rol = 2
        ", ['id' => $userId]);

        if (!$empleado) {
            return ['permitido' => true];
        }

        $horario = $this->db->fetchOne("
            SELECT * FROM horarios_operador
            WHERE id_empleado = :id AND activo = 1 LIMIT 1
        ", ['id' => $empleado['id_empleado']]);

        if (!$horario) {
            return ['permitido' => false, 'mensaje' => 'No tienes un horario asignado.'];
        }

        $horaActual = date('H:i:s');
        $dentroHorario = false;
        if ($horario['hora_inicio'] < $horario['hora_fin']) {
            $dentroHorario = $horaActual >= $horario['hora_inicio'] && $horaActual <= $horario['hora_fin'];
        } else {
            $dentroHorario = $horaActual >= $horario['hora_inicio'] || $horaActual <= $horario['hora_fin'];
        }

        if ($dentroHorario) return ['permitido' => true];

        $override = $this->db->fetchOne("
            SELECT * FROM accesos_extraordinarios
            WHERE id_empleado = :id AND expiracion > NOW()
            ORDER BY expiracion DESC LIMIT 1
        ", ['id' => $empleado['id_empleado']]);

        if ($override) {
            return ['permitido' => true, 'override' => true, 'motivo' => $override['motivo']];
        }

        return [
            'permitido' => false,
            'mensaje' => 'Fuera de tu horario laboral (' . date('H:i', strtotime($horario['hora_inicio'])) . ' - ' . date('H:i', strtotime($horario['hora_fin'])) . '). Contacta a tu supervisor.',
        ];
    }

    public function logout(): void
    {
        session_destroy();
    }
}

<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        $this->view('auth/login');
    }

    public function login(): void
    {
        $username = $this->postParam('nombre_usuario');
        $password = $this->postParam('password');

        if (!$username || !$password) {
            $_SESSION['error'] = 'Usuario y contraseña son requeridos';
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->authenticate($username, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['nombre_usuario'];
            $_SESSION['user_rol'] = $user['id_rol'];

            if (!empty($user['id_cliente'])) {
                $_SESSION['user_id_cliente'] = $user['id_cliente'];
            }

            $userData = $userModel->getWithEmpleado($user['id_usuario']);
            if ($userData && $userData['nombre']) {
                $_SESSION['empleado_nombre'] = $userData['nombre'] . ' ' . $userData['apellido_paterno'];
                $_SESSION['rol_nombre'] = $userData['rol'];
            } else {
                $rolNombres = [1 => 'Administrador', 2 => 'Operador', 3 => 'Supervisor', 4 => 'Vendedor', 5 => 'Cliente'];
                $_SESSION['rol_nombre'] = $rolNombres[$user['id_rol']] ?? 'Usuario';
                $_SESSION['empleado_nombre'] = $user['nombre_usuario'];
            }

            if ($user['id_rol'] === 2) {
                $acceso = \verificar_acceso_operador();
                if (!$acceso['permitido']) {
                    $_SESSION['error_acceso'] = $acceso['mensaje'];
                    session_destroy();
                    $this->redirect('/acceso-denegado');
                }
            }

            set_flash('success', 'Bienvenido al sistema');
            $this->redirect('/');
        } else {
            $_SESSION['error'] = 'Usuario o contraseña incorrectos';
            $this->redirect('/login');
        }
    }

    public function showRegister(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        $db = \App\Core\Database::getInstance();
        $clientes = $db->fetchAll("SELECT id_cliente, razon_social, rfc FROM clientes ORDER BY razon_social");
        $this->view('auth/register', ['clientes' => $clientes]);
    }

    public function register(): void
    {
        $nombre = $this->postParam('nombre');
        $apellido = $this->postParam('apellido');
        $username = $this->postParam('nombre_usuario');
        $password = $this->postParam('password');
        $confirm = $this->postParam('password_confirm');
        $tipo = $this->postParam('tipo') ?: 'vendedor';
        $idCliente = $this->postParam('id_cliente');

        $_SESSION['_old'] = [
            'tipo' => $tipo,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'nombre_usuario' => $username,
            'id_cliente' => $idCliente,
        ];

        if ($tipo === 'cliente') {
            if (!$username || !$password) {
                $_SESSION['error'] = 'Usuario y contraseña son obligatorios';
                $this->redirect('/register');
            }
            if (!$idCliente) {
                $_SESSION['error'] = 'Debes seleccionar una empresa';
                $this->redirect('/register');
            }
        } else {
            if (!$nombre || !$username || !$password) {
                $_SESSION['error'] = 'Todos los campos son obligatorios';
                $this->redirect('/register');
            }
        }

        if ($password !== $confirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            $this->redirect('/register');
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres';
            $this->redirect('/register');
        }

        $userModel = new User();
        $existing = $userModel->whereFirst('nombre_usuario', $username);
        if ($existing) {
            $_SESSION['error'] = 'El nombre de usuario ya existe';
            $this->redirect('/register');
        }

        $db = \App\Core\Database::getInstance();

        $db->beginTransaction();
        try {
            if ($tipo === 'cliente') {
                $idRol = 5;
                $userModel->createUser([
                    'nombre_usuario' => $username,
                    'password' => $password,
                    'id_rol' => $idRol,
                    'id_cliente' => $idCliente ?: null,
                    'activo' => 1,
                ]);
            } else {
                $idEmpleado = $db->insert('empleados', [
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellido ?: '',
                    'estatus' => 'activo',
                ]);

                $idRol = ROL_VENDEDOR;
                $userModel->createUser([
                    'id_empleado' => $idEmpleado,
                    'nombre_usuario' => $username,
                    'password' => $password,
                    'id_rol' => $idRol,
                    'activo' => 1,
                ]);
            }

            unset($_SESSION['_old']);
            $db->commit();
            set_flash('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
            $this->redirect('/login');
        } catch (\Exception $e) {
            $db->rollback();
            $_SESSION['error'] = 'Error al registrar: ' . $e->getMessage();
            $this->redirect('/register');
        }
    }

    public function accesoDenegado(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        $this->view('operador/acceso_denegado');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}

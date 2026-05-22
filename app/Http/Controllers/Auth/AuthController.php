<?php
namespace App\Http\Controllers\Auth;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\NotificacionService;
use App\Repositories\ClienteRepository;
use App\Repositories\UserRepository;
use App\Http\Requests\LoginRequest;
use App\Exceptions\ValidationException;

class AuthController extends Controller
{
    private AuthService $authService;
    private NotificacionService $notificacionService;
    private ClienteRepository $clienteRepository;
    private UserRepository $userRepository;

    public function __construct(
        ?AuthService $authService = null,
        ?NotificacionService $notificacionService = null,
        ?ClienteRepository $clienteRepository = null,
        ?UserRepository $userRepository = null
    ) {
        $this->authService = $authService ?? new AuthService();
        $this->notificacionService = $notificacionService ?? new NotificacionService();
        $this->clienteRepository = $clienteRepository ?? new ClienteRepository();
        $this->userRepository = $userRepository ?? new UserRepository();
    }

    public function showLogin(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        csrf_token();
        $this->view('auth/login');
    }

    public function login(): void
    {
        if (!verify_csrf($this->postParam('csrf_token'))) {
            $_SESSION['error'] = 'Token de seguridad inválido. Intente nuevamente.';
            $this->redirect('/login');
        }

        try {
            $request = new LoginRequest($_POST);
            $validated = $request->validate();
        } catch (ValidationException $e) {
            $_SESSION['error'] = $e->getFirstError();
            $this->redirect('/login');
        }

        $user = $this->authService->login($validated['nombre_usuario'], $validated['password']);

        if (!$user) {
            $_SESSION['error'] = 'Usuario o contraseña incorrectos';
            $this->redirect('/login');
        }

        if ($user['id_rol'] === 2) {
            $acceso = $this->authService->checkOperatorAccess($user['id_usuario']);
            if (!$acceso['permitido']) {
                $_SESSION['error_acceso'] = $acceso['mensaje'];
                session_destroy();
                $this->redirect('/acceso-denegado');
            }
        }

        \App\Services\AuditService::log('LOGIN', 'Usuario', $user['id_usuario'], "Inicio de sesi&oacute;n: {$validated['nombre_usuario']}");
        set_flash('success', 'Bienvenido al sistema');
        $this->redirect('/');
    }

    public function showRegister(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        $clientes = $this->clienteRepository->all();
        usort($clientes, fn($a, $b) => strcmp($a['razon_social'], $b['razon_social']));
        $this->view('auth/register', ['clientes' => $clientes]);
    }

    public function register(): void
    {
        if (!verify_csrf($this->postParam('csrf_token'))) {
            $_SESSION['error'] = 'Token de seguridad inválido. Intente nuevamente.';
            $this->redirect('/register');
        }

        $nombre = $this->input('nombre');
        $apellido = $this->input('apellido');
        $username = $this->input('nombre_usuario');
        $password = $this->input('password');
        $confirm = $this->input('password_confirm');
        $tipo = $this->input('tipo', 'vendedor');
        $idCliente = $this->input('id_cliente');

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

        $existing = $this->userRepository->whereFirst('nombre_usuario', $username);
        if ($existing) {
            $_SESSION['error'] = 'El nombre de usuario ya existe';
            $this->redirect('/register');
        }

        try {
            if ($tipo === 'cliente') {
                $this->authService->registerCliente($username, $password, (int) $idCliente);
            } else {
                $this->authService->registerVendedor($username, $password, $nombre, $apellido ?: '');
            }

            unset($_SESSION['_old']);
            set_flash('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
            $this->redirect('/login');
        } catch (\Exception $e) {
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
        $userId = $_SESSION['user_id'] ?? null;
        $this->authService->logout();
        \App\Services\AuditService::log('LOGOUT', 'Usuario', $userId, 'Cierre de sesi&oacute;n');
        $this->redirect('/login');
    }
}

<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;
use App\Models\Empleado;
use App\Repositories\UserRepository;

class UsuariosController extends Controller
{
    private Database $db;
    private UserRepository $userRepo;
    private User $user;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userRepo = new UserRepository();
        $this->user = new User();
    }

    public function index(): void
    {
        requireRolMultiple([1, 3]);
        $data = [
            'usuarios' => $this->userRepo->allWithEmpleado(),
            'pageTitle' => 'Usuarios',
            'readonly' => !es_admin(),
        ];
        $this->view('usuarios/index', $data);
    }

    public function create(): void
    {
        $this->requireRol(1);
        $empleadoModel = new Empleado();
        $data = [
            'empleados_disponibles' => $empleadoModel->getSinUsuario(),
            'roles' => $this->user->getRoles(),
            'pageTitle' => 'Nuevo Usuario',
        ];
        $this->view('usuarios/create', $data);
    }

    public function store(): void
    {
        $this->requireRol(1);

        $nombreUsuario = $this->postParam('nombre_usuario');
        $password = $this->postParam('password');
        $idRol = $this->postParam('id_rol');
        $nombre = $this->postParam('nombre');
        $apellidoPaterno = $this->postParam('apellido_paterno');
        $apellidoMaterno = $this->postParam('apellido_materno');
        $idEmpleado = $this->postParam('id_empleado');

        if (empty($nombreUsuario) || empty($password)) {
            set_flash('error', 'Usuario y contraseña son obligatorios');
            $this->redirect('/usuarios/create');
        }

        $this->db->beginTransaction();
        try {
            if ($idEmpleado) {
                $empleado = $this->db->fetchOne("SELECT id_empleado FROM empleados WHERE id_empleado = :id", ['id' => $idEmpleado]);
                if (!$empleado) {
                    throw new \Exception('Empleado no encontrado');
                }
            } else {
                $idEmpleado = $this->db->insert('empleados', [
                    'nombre' => $nombre ?: $nombreUsuario,
                    'apellido_paterno' => $apellidoPaterno ?: '',
                    'apellido_materno' => $apellidoMaterno ?: '',
                    'estatus' => 'activo',
                ]);
            }

            $exists = $this->db->fetchOne("SELECT id_usuario FROM usuarios WHERE nombre_usuario = :u", ['u' => $nombreUsuario]);
            if ($exists) {
                throw new \Exception('El nombre de usuario ya existe');
            }

            $this->user->createUser([
                'id_empleado' => $idEmpleado,
                'nombre_usuario' => $nombreUsuario,
                'password' => $password,
                'id_rol' => $idRol ?: 2,
                'activo' => 1,
            ]);

            $this->db->commit();
            set_flash('success', 'Usuario creado correctamente');
            $this->redirect('/usuarios');
        } catch (\Exception $e) {
            $this->db->rollback();
            set_flash('error', $e->getMessage());
            $this->redirect('/usuarios/create');
        }
    }

    public function edit(array $params): void
    {
        $this->requireRol(1);
        $usuario = $this->userRepo->findWithEmpleado($params['id']);
        if (!$usuario) {
            set_flash('error', 'Usuario no encontrado');
            $this->redirect('/usuarios');
        }
        $data = [
            'usuario' => $usuario,
            'roles' => $this->user->getRoles(),
            'pageTitle' => 'Editar Usuario',
        ];
        $this->view('usuarios/edit', $data);
    }

    public function update(array $params): void
    {
        $this->requireRol(1);
        $usuario = $this->user->find($params['id']);
        if (!$usuario) {
            set_flash('error', 'Usuario no encontrado');
            $this->redirect('/usuarios');
        }

        $data = [
            'id_rol' => $this->postParam('id_rol') ?: $usuario['id_rol'],
            'activo' => $this->postParam('activo') ? 1 : 0,
        ];

        $password = $this->postParam('password');
        if (!empty($password)) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->user->update($params['id'], $data);

        set_flash('success', 'Usuario actualizado correctamente');
        $this->redirect('/usuarios');
    }

    public function delete(array $params): void
    {
        $this->requireRol(1);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/usuarios');
        }
        if ((int) $params['id'] === (int) ($_SESSION['user_id'] ?? 0)) {
            set_flash('error', 'No puedes eliminar tu propio usuario');
            $this->redirect('/usuarios');
        }
        $this->user->delete((int) $params['id']);
        set_flash('success', 'Usuario eliminado correctamente');
        $this->redirect('/usuarios');
    }
}

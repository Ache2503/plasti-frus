<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;
use App\Core\Database;
use App\Models\User;

class ProfileController extends Controller
{
    private Database $db;
    private User $user;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->user = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $usuario = $this->user->getWithEmpleado($_SESSION['user_id']);

        $cliente = null;
        if (es_cliente()) {
            $idCliente = user_id_cliente();
            if ($idCliente) {
                $cliente = $this->db->fetchOne("SELECT * FROM clientes WHERE id_cliente = :id", ['id' => $idCliente]);
            }
        }

        $data = [
            'usuario' => $usuario,
            'cliente' => $cliente,
            'pageTitle' => 'Mi Perfil',
        ];
        $this->view('profile/index', $data);
    }

    public function updatePassword(): void
    {
        $this->requireAuth();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/profile');
        }
        $current = $this->postParam('current_password');
        $new = $this->postParam('new_password');
        $confirm = $this->postParam('confirm_password');

        if (empty($current) || empty($new)) {
            set_flash('error', 'Todos los campos son obligatorios');
            $this->redirect('/profile');
        }

        if ($new !== $confirm) {
            set_flash('error', 'Las contraseñas nuevas no coinciden');
            $this->redirect('/profile');
        }

        if (strlen($new) < 6) {
            set_flash('error', 'La contraseña debe tener al menos 6 caracteres');
            $this->redirect('/profile');
        }

        $user = $this->user->find($_SESSION['user_id']);

        if (!password_verify($current, $user['password_hash'])) {
            set_flash('error', 'La contraseña actual no es correcta');
            $this->redirect('/profile');
        }

        $this->user->updatePassword($_SESSION['user_id'], $new);
        set_flash('success', 'Contraseña actualizada correctamente');
        $this->redirect('/profile');
    }

    public function updatePersonal(): void
    {
        $this->requireAuth();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/profile');
        }

        $userId = $_SESSION['user_id'];
        $nombre = trim($this->postParam('nombre', ''));
        $apellidoPaterno = trim($this->postParam('apellido_paterno', ''));
        $apellidoMaterno = trim($this->postParam('apellido_materno', ''));
        $correo = trim($this->postParam('correo', ''));
        $telefono = trim($this->postParam('telefono', ''));

        if (!es_cliente()) {
            $usuario = $this->db->fetchOne("SELECT id_empleado FROM usuarios WHERE id_usuario = :id", ['id' => $userId]);
            if ($usuario && $usuario['id_empleado']) {
                $this->db->update('empleados', [
                    'nombre' => $nombre ?: null,
                    'apellido_paterno' => $apellidoPaterno ?: null,
                    'apellido_materno' => $apellidoMaterno ?: null,
                    'correo' => $correo ?: null,
                    'telefono' => $telefono ?: null,
                ], 'id_empleado = :id', ['id' => $usuario['id_empleado']]);
                registrar_log('actualizar_personal', 'usuario', $userId, 'Actualizó información personal');
                set_flash('success', 'Datos personales actualizados correctamente');
            } else {
                set_flash('error', 'No hay un perfil de empleado vinculado');
            }
        } else {
            set_flash('error', 'Usa el formulario de representante para actualizar tus datos');
        }
        $this->redirect('/profile');
    }

    public function updateContacto(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Solo clientes pueden actualizar representante');
            $this->redirect('/profile');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/profile');
        }

        $idCliente = user_id_cliente();
        if (!$idCliente) {
            set_flash('error', 'Perfil de cliente no vinculado');
            $this->redirect('/profile');
        }

        $this->db->update('clientes', [
            'contacto_nombre' => trim($this->postParam('contacto_nombre', '')),
            'contacto_cargo' => trim($this->postParam('contacto_cargo', '')),
            'contacto_telefono' => trim($this->postParam('contacto_telefono', '')),
            'contacto_correo' => trim($this->postParam('contacto_correo', '')),
        ], 'id_cliente = :id', ['id' => $idCliente]);

        registrar_log('actualizar_contacto', 'cliente', $idCliente, 'Actualizó datos del representante');
        set_flash('success', 'Datos del representante actualizados correctamente');
        $this->redirect('/profile');
    }

    public function updateCliente(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/profile');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/profile');
        }

        $idCliente = user_id_cliente();
        if (!$idCliente) {
            set_flash('error', 'Perfil de cliente no vinculado');
            $this->redirect('/profile');
        }

        $this->db->update('clientes', [
            'razon_social' => $this->postParam('razon_social'),
            'rfc' => strtoupper(trim($this->postParam('rfc', ''))),
            'ciudad' => $this->postParam('ciudad'),
            'estado' => $this->postParam('estado'),
            'codigo_postal' => $this->postParam('codigo_postal'),
            'domicilio' => $this->postParam('domicilio'),
            'referencia_domicilio' => $this->postParam('referencia_domicilio'),
        ], 'id_cliente = :id', ['id' => $idCliente]);

        registrar_log('actualizar_empresa', 'cliente', $idCliente, 'Cliente actualizó datos de la empresa');
        set_flash('success', 'Datos de la empresa actualizados correctamente');
        $this->redirect('/profile');
    }
}

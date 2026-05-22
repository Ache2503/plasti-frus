<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\DireccionCliente;

class DireccionesController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        $idCliente = user_id_cliente();
        $direccionModel = new DireccionCliente();
        $direcciones = $idCliente ? $direccionModel->getByCliente($idCliente) : [];

        $data = [
            'pageTitle' => 'Mis Direcciones',
            'direcciones' => $direcciones,
        ];
        $this->view('portal/direcciones/index', $data);
    }

    public function agregar(): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/direcciones');
        }

        $idCliente = user_id_cliente();
        if (!$idCliente) {
            set_flash('error', 'Perfil de cliente no encontrado');
            $this->redirect('/direcciones');
        }

        $errors = [];
        if (empty($_POST['alias'] ?? '')) $errors[] = 'El alias es obligatorio';
        if (empty($_POST['calle'] ?? '')) $errors[] = 'La calle es obligatoria';
        if (empty($_POST['ciudad'] ?? '')) $errors[] = 'La ciudad es obligatoria';
        if (empty($_POST['estado'] ?? '')) $errors[] = 'El estado es obligatorio';
        if (empty($_POST['codigo_postal'] ?? '')) $errors[] = 'El código postal es obligatorio';

        if (!empty($errors)) {
            set_flash('error', implode('<br>', $errors));
            $this->redirect('/direcciones');
        }

        $direccionModel = new DireccionCliente();
        $direccionModel->createForCliente($idCliente, [
            'alias' => $_POST['alias'] ?? '',
            'destinatario' => $_POST['destinatario'] ?? '',
            'telefono_contacto' => $_POST['telefono_contacto'] ?? '',
            'calle' => $_POST['calle'] ?? '',
            'numero_exterior' => $_POST['numero_exterior'] ?? '',
            'numero_interior' => $_POST['numero_interior'] ?? '',
            'colonia' => $_POST['colonia'] ?? '',
            'ciudad' => $_POST['ciudad'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'codigo_postal' => $_POST['codigo_postal'] ?? '',
            'referencia' => $_POST['referencia'] ?? '',
            'predeterminada' => !empty($_POST['predeterminada']),
        ]);

        registrar_log('direccion_crear', 'direcciones_cliente', $idCliente, 'Nueva dirección agregada');
        set_flash('success', 'Dirección guardada correctamente');
        $this->redirect('/direcciones');
    }

    public function actualizar(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/direcciones');
        }

        $idDireccion = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        $direccionModel = new DireccionCliente();
        $direccionModel->updateForCliente($idDireccion, $idCliente, [
            'alias' => $_POST['alias'] ?? '',
            'destinatario' => $_POST['destinatario'] ?? '',
            'telefono_contacto' => $_POST['telefono_contacto'] ?? '',
            'calle' => $_POST['calle'] ?? '',
            'numero_exterior' => $_POST['numero_exterior'] ?? '',
            'numero_interior' => $_POST['numero_interior'] ?? '',
            'colonia' => $_POST['colonia'] ?? '',
            'ciudad' => $_POST['ciudad'] ?? '',
            'estado' => $_POST['estado'] ?? '',
            'codigo_postal' => $_POST['codigo_postal'] ?? '',
            'referencia' => $_POST['referencia'] ?? '',
            'predeterminada' => !empty($_POST['predeterminada']),
        ]);

        registrar_log('direccion_actualizar', 'direcciones_cliente', $idDireccion, 'Dirección actualizada');
        set_flash('success', 'Dirección actualizada correctamente');
        $this->redirect('/direcciones');
    }

    public function eliminar(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/direcciones');
        }

        $idDireccion = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        $direccionModel = new DireccionCliente();
        if ($direccionModel->deleteForCliente($idDireccion, $idCliente)) {
            registrar_log('direccion_eliminar', 'direcciones_cliente', $idDireccion, 'Dirección eliminada');
            set_flash('success', 'Dirección eliminada correctamente');
        } else {
            set_flash('error', 'No se pudo eliminar la dirección');
        }
        $this->redirect('/direcciones');
    }

    public function predeterminada(array $params): void
    {
        $this->requireAuth();
        if (!es_cliente()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }

        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/direcciones');
        }

        $idDireccion = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        $direccionModel = new DireccionCliente();
        if ($direccionModel->setDefault($idDireccion, $idCliente)) {
            set_flash('success', 'Dirección predeterminada actualizada');
        } else {
            set_flash('error', 'No se pudo actualizar la dirección predeterminada');
        }
        $this->redirect('/direcciones');
    }
}

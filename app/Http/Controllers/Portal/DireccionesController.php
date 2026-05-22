<?php
namespace App\Http\Controllers\Portal;

use App\Core\Controller;
use App\Core\Database;
use App\Models\DireccionCliente;

class DireccionesController extends Controller
{
    private Database $db;
    private DireccionCliente $direccionModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->direccionModel = new DireccionCliente();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        $idCliente = user_id_cliente();
        $direcciones = $idCliente ? $this->direccionModel->getByCliente($idCliente) : [];

        $data = [
            'pageTitle' => 'Mis Direcciones',
            'direcciones' => $direcciones,
        ];
        $this->view('portal.direcciones.index', $data);
    }

    public function agregar(): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/direcciones');
        }

        $idCliente = user_id_cliente();
        if (!$idCliente) {
            set_flash('error', 'Perfil de cliente no encontrado');
            $this->redirect('/direcciones');
        }

        $errors = [];
        if (empty($this->postParam('alias', ''))) $errors[] = 'El alias es obligatorio';
        if (empty($this->postParam('calle', ''))) $errors[] = 'La calle es obligatoria';
        if (empty($this->postParam('ciudad', ''))) $errors[] = 'La ciudad es obligatoria';
        if (empty($this->postParam('estado', ''))) $errors[] = 'El estado es obligatorio';
        if (empty($this->postParam('codigo_postal', ''))) $errors[] = 'El código postal es obligatorio';

        if (!empty($errors)) {
            set_flash('error', implode('<br>', $errors));
            $this->redirect('/direcciones');
        }

        $this->direccionModel->createForCliente($idCliente, [
            'alias' => $this->postParam('alias'),
            'destinatario' => $this->postParam('destinatario'),
            'telefono_contacto' => $this->postParam('telefono_contacto'),
            'calle' => $this->postParam('calle'),
            'numero_exterior' => $this->postParam('numero_exterior'),
            'numero_interior' => $this->postParam('numero_interior'),
            'colonia' => $this->postParam('colonia'),
            'ciudad' => $this->postParam('ciudad'),
            'estado' => $this->postParam('estado'),
            'codigo_postal' => $this->postParam('codigo_postal'),
            'referencia' => $this->postParam('referencia'),
            'predeterminada' => $this->postParam('predeterminada') === '1',
        ]);

        registrar_log('direccion_crear', 'direcciones_cliente', $idCliente, 'Nueva dirección agregada');
        set_flash('success', 'Dirección guardada correctamente');
        $this->redirect('/direcciones');
    }

    public function actualizar(array $params): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/direcciones');
        }

        $idDireccion = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        $result = $this->direccionModel->updateForCliente($idDireccion, $idCliente, [
            'alias' => $this->postParam('alias'),
            'destinatario' => $this->postParam('destinatario'),
            'telefono_contacto' => $this->postParam('telefono_contacto'),
            'calle' => $this->postParam('calle'),
            'numero_exterior' => $this->postParam('numero_exterior'),
            'numero_interior' => $this->postParam('numero_interior'),
            'colonia' => $this->postParam('colonia'),
            'ciudad' => $this->postParam('ciudad'),
            'estado' => $this->postParam('estado'),
            'codigo_postal' => $this->postParam('codigo_postal'),
            'referencia' => $this->postParam('referencia'),
            'predeterminada' => $this->postParam('predeterminada') === '1',
        ]);

        if (!$result) {
            set_flash('error', 'No se pudo actualizar la dirección');
            $this->redirect('/direcciones');
        }

        registrar_log('direccion_actualizar', 'direcciones_cliente', $idDireccion, 'Dirección actualizada');
        set_flash('success', 'Dirección actualizada correctamente');
        $this->redirect('/direcciones');
    }

    public function eliminar(array $params): void
    {
        $this->requireAuth();
        $this->requireRol(5);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/direcciones');
        }

        $idDireccion = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        if ($this->direccionModel->deleteForCliente($idDireccion, $idCliente)) {
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
        $this->requireRol(5);

        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/direcciones');
        }

        $idDireccion = (int) ($params['id'] ?? 0);
        $idCliente = user_id_cliente();

        if ($this->direccionModel->setDefault($idDireccion, $idCliente)) {
            set_flash('success', 'Dirección predeterminada actualizada');
        } else {
            set_flash('error', 'No se pudo actualizar la dirección predeterminada');
        }
        $this->redirect('/direcciones');
    }
}

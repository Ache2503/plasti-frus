<?php
namespace App\Http\Controllers\Purchasing;

use App\Core\Controller;
use App\Models\Proveedor;
use App\Repositories\ProveedorRepository;

class ProveedoresController extends Controller
{
    private Proveedor $proveedorModel;
    private ProveedorRepository $proveedorRepository;

    public function __construct()
    {
        $this->proveedorModel = new Proveedor();
        $this->proveedorRepository = new ProveedorRepository();
    }

    public function index(): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        $data = [
            'proveedores' => $this->proveedorRepository->all(),
            'pageTitle' => 'Proveedores',
        ];
        $this->view('proveedores/index', $data);
    }

    public function create(): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        $data = [
            'sectores' => $this->proveedorModel->getSectores(),
            'pageTitle' => 'Nuevo Proveedor',
        ];
        $this->view('proveedores/create', $data);
    }

    public function store(): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        $data = [
            'razon_social' => $this->postParam('razon_social'),
            'rfc' => $this->postParam('rfc'),
            'tipo_material' => $this->postParam('tipo_material'),
            'telefono' => $this->postParam('telefono'),
            'correo' => $this->postParam('correo'),
            'ciudad' => $this->postParam('ciudad'),
            'estado' => $this->postParam('estado'),
            'pais' => $this->postParam('pais'),
            'sector' => $this->postParam('sector'),
            'estatus' => $this->postParam('estatus') ?: 'activo',
        ];
        $this->proveedorRepository->create($data);
        set_flash('success', 'Proveedor creado correctamente');
        $this->redirect('/proveedores');
    }

    public function edit(array $params): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        $proveedor = $this->proveedorRepository->find($params['id']);
        if (!$proveedor) {
            set_flash('error', 'Proveedor no encontrado');
            $this->redirect('/proveedores');
        }
        $data = [
            'proveedor' => $proveedor,
            'pageTitle' => 'Editar Proveedor',
        ];
        $this->view('proveedores/edit', $data);
    }

    public function update(array $params): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        $data = [
            'razon_social' => $this->postParam('razon_social'),
            'rfc' => $this->postParam('rfc'),
            'tipo_material' => $this->postParam('tipo_material'),
            'telefono' => $this->postParam('telefono'),
            'correo' => $this->postParam('correo'),
            'ciudad' => $this->postParam('ciudad'),
            'estado' => $this->postParam('estado'),
            'pais' => $this->postParam('pais'),
            'sector' => $this->postParam('sector'),
            'estatus' => $this->postParam('estatus') ?: 'activo',
        ];
        $this->proveedorRepository->update($params['id'], $data);
        set_flash('success', 'Proveedor actualizado correctamente');
        $this->redirect('/proveedores');
    }

    public function delete(array $params): void
    {
        $this->requireAuth(); requireRolMultiple([1, 3, 6]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/proveedores');
        }
        $this->proveedorRepository->delete((int) $params['id']);
        set_flash('success', 'Proveedor eliminado correctamente');
        $this->redirect('/proveedores');
    }
}

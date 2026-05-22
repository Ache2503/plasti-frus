<?php
namespace App\Http\Controllers\Production;

use App\Core\Controller;
use App\Models\Molde;

class MoldesController extends Controller
{
    private Molde $moldeModel;

    public function __construct()
    {
        $this->moldeModel = new Molde();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $data = [
            'moldes' => $this->moldeModel->getWithCede(),
            'pageTitle' => 'Moldes',
            'puedeEliminar' => puedeEliminar(),
        ];
        $this->view('moldes/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        $data = [
            'cedes' => $this->moldeModel->getAvailableCedes(),
            'pageTitle' => 'Nuevo Molde',
        ];
        $this->view('moldes/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        $data = [
            'nombre_molde' => $this->postParam('nombre_molde'),
            'numero_cavidades' => $this->postParam('numero_cavidades') ?: 1,
            'material_molde' => $this->postParam('material_molde'),
            'vida_util_golpes' => $this->postParam('vida_util_golpes') ?: 0,
            'ciclos_acumulados' => $this->postParam('ciclos_acumulados') ?: 0,
            'estatus' => $this->postParam('estatus') ?: 'activo',
            'id_cedes' => $this->postParam('id_cedes') ?: null,
        ];
        $id = $this->moldeModel->create($data);
        registrar_log('crear', 'molde', $id, $data['nombre_molde']);
        set_flash('success', 'Molde creado correctamente');
        $this->redirect('/moldes');
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        $molde = $this->moldeModel->getByIdWithCede($params['id']);
        if (!$molde) {
            set_flash('error', 'Molde no encontrado');
            $this->redirect('/moldes');
        }
        $data = [
            'molde' => $molde,
            'cedes' => $this->moldeModel->getAvailableCedes(),
            'pageTitle' => 'Editar Molde',
        ];
        $this->view('moldes/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        $data = [
            'nombre_molde' => $this->postParam('nombre_molde'),
            'numero_cavidades' => $this->postParam('numero_cavidades') ?: 1,
            'material_molde' => $this->postParam('material_molde'),
            'vida_util_golpes' => $this->postParam('vida_util_golpes') ?: 0,
            'ciclos_acumulados' => $this->postParam('ciclos_acumulados') ?: 0,
            'estatus' => $this->postParam('estatus') ?: 'activo',
            'id_cedes' => $this->postParam('id_cedes') ?: null,
        ];
        $this->moldeModel->update($params['id'], $data);
        registrar_log('actualizar', 'molde', $params['id'], $data['nombre_molde']);
        set_flash('success', 'Molde actualizado correctamente');
        $this->redirect('/moldes');
    }

    public function delete(array $params): void
    {
        $this->checkAccess();
        if (!puedeEliminar()) {
            set_flash('error', 'No tienes permisos para eliminar');
            $this->redirect('/moldes');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/moldes');
        }
        $this->moldeModel->delete((int) $params['id']);
        registrar_log('eliminar', 'molde', $params['id'], 'Molde eliminado');
        set_flash('success', 'Molde eliminado correctamente');
        $this->redirect('/moldes');
    }
}

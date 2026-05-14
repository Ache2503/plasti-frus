<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Maquina;

class MaquinasController extends Controller
{
    private $maquina;

    public function __construct()
    {
        $this->maquina = new Maquina();
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
            'maquinas' => $this->maquina->all(),
            'pageTitle' => 'Máquinas',
            'puedeEliminar' => puedeEliminar(),
        ];
        $this->view('maquinas/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        $data = [
            'pageTitle' => 'Nueva Máquina',
        ];
        $this->view('maquinas/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        $data = [
            'nombre' => $this->postParam('nombre'),
            'modelo' => $this->postParam('modelo'),
            'numero_serie' => $this->postParam('numero_serie'),
            'estatus' => $this->postParam('estatus') ?: 'activo',
        ];
        $id = $this->maquina->create($data);
        registrar_log('crear', 'maquina', $id, $data['nombre']);
        set_flash('success', 'Máquina creada correctamente');
        $this->redirect('/maquinas');
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        $maquina = $this->maquina->find($params['id']);
        if (!$maquina) {
            set_flash('error', 'Máquina no encontrada');
            $this->redirect('/maquinas');
        }
        $data = [
            'maquina' => $maquina,
            'pageTitle' => 'Editar Máquina',
        ];
        $this->view('maquinas/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        $data = [
            'nombre' => $this->postParam('nombre'),
            'modelo' => $this->postParam('modelo'),
            'numero_serie' => $this->postParam('numero_serie'),
            'estatus' => $this->postParam('estatus') ?: 'activo',
        ];
        $this->maquina->update($params['id'], $data);
        registrar_log('actualizar', 'maquina', $params['id'], $data['nombre']);
        set_flash('success', 'Máquina actualizada correctamente');
        $this->redirect('/maquinas');
    }

    public function delete(array $params): void
    {
        $this->checkAccess();
        if (!puedeEliminar()) {
            set_flash('error', 'No tienes permisos para eliminar');
            $this->redirect('/maquinas');
        }
        $this->maquina->delete($params['id']);
        registrar_log('eliminar', 'maquina', $params['id'], 'Máquina eliminada');
        set_flash('success', 'Máquina eliminada correctamente');
        $this->redirect('/maquinas');
    }

}

<?php
namespace App\Http\Controllers\Crm;

use App\Core\Controller;
use App\Models\Actividad;

class ActividadController extends Controller
{
    private Actividad $model;

    public function __construct()
    {
        $this->model = new Actividad();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
    }

    private function userId(): int
    {
        return (int) $_SESSION['user_id'];
    }

    public function index(): void
    {
        $this->checkAccess();
        $mes = $this->getParam('mes', date('Y-m'));
        $data = [
            'pageTitle' => 'Mi Agenda',
            'mes' => $mes,
            'actividades' => $this->model->getByMonth($this->userId(), $mes),
            'tipos' => Actividad::TIPOS,
        ];
        $this->view('vendedor/agenda', $data);
    }

    public function data(): void
    {
        $this->checkAccess();
        $mes = $this->getParam('mes', date('Y-m'));
        $this->json($this->model->getByMonth($this->userId(), $mes));
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            $this->json(['error' => 'Token inválido'], 403);
        }
        $id = $this->model->create([
            'id_vendedor' => $this->userId(),
            'titulo' => $this->postParam('titulo'),
            'descripcion' => $this->postParam('descripcion'),
            'tipo' => $this->postParam('tipo', 'tarea'),
            'fecha_hora' => $this->postParam('fecha_hora'),
            'estado' => 'pendiente',
            'color' => $this->postParam('color', '#0d6efd'),
            'recordatorio' => $this->postParam('recordatorio') ? 1 : 0,
        ]);
        if ($this->postParam('recordatorio')) {
            notificar_vendedor($this->userId(), 'recordatorio_actividad', 'Recordatorio: ' . $this->postParam('titulo'), $this->postParam('descripcion'), $id);
        }
        if ($this->isPost() && !$this->getParam('redirect')) {
            $this->json(['success' => true, 'id' => $id]);
        }
        set_flash('success', 'Actividad creada');
        $this->redirect('/agenda');
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            $this->json(['error' => 'Token inválido'], 403);
        }
        $id = (int) $params['id'];
        $act = $this->model->find($id);
        if (!$act || (int) $act['id_vendedor'] !== $this->userId()) {
            $this->json(['error' => 'No autorizado'], 403);
        }
        $data = [];
        foreach (['titulo', 'descripcion', 'tipo', 'fecha_hora', 'estado', 'color'] as $field) {
            $val = $this->postParam($field);
            if ($val !== null) $data[$field] = $val;
        }
        if ($this->postParam('recordatorio') !== null) {
            $data['recordatorio'] = $this->postParam('recordatorio') ? 1 : 0;
        }
        $this->model->update($id, $data);
        if ($this->isPost() && !$this->getParam('redirect')) {
            $this->json(['success' => true]);
        }
        set_flash('success', 'Actividad actualizada');
        $this->redirect('/agenda');
    }

    public function destroy(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            $this->json(['error' => 'Token inválido'], 403);
        }
        $id = (int) $params['id'];
        $act = $this->model->find($id);
        if (!$act || (int) $act['id_vendedor'] !== $this->userId()) {
            $this->json(['error' => 'No autorizado'], 403);
        }
        $this->model->delete($id);
        $this->json(['success' => true]);
    }
}

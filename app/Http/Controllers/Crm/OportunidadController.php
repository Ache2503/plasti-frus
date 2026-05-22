<?php
namespace App\Http\Controllers\Crm;

use App\Core\Controller;
use App\Models\Oportunidad;

class OportunidadController extends Controller
{
    private Oportunidad $model;

    public function __construct()
    {
        $this->model = new Oportunidad();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        if (!es_vendedor() && !in_array(user_rol(), [1, 3])) {
            set_flash('error', 'Acceso denegado');
            $this->redirect('/');
        }
    }

    private function getUserId(): int
    {
        return (int) $_SESSION['user_id'];
    }

    public function index(): void
    {
        $this->checkAccess();
        $userId = $this->getUserId();

        $etapa = $this->getParam('etapa');
        $fechaDesde = $this->getParam('fecha_desde');
        $fechaHasta = $this->getParam('fecha_hasta');
        $valorMin = $this->getParam('valor_min');

        $filters = array_filter([
            'etapa' => $etapa,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'valor_min' => $valorMin,
        ]);

        $oportunidades = $filters
            ? $this->model->search($filters, $userId)
            : $this->model->findByVendedor($userId);

        $data = [
            'oportunidades' => $oportunidades,
            'etapas' => Oportunidad::ETAPAS,
            'total_pipeline' => $this->model->getTotalPipeline($userId),
            'tasa_conversion' => $this->model->getTasaConversion($userId),
            'pageTitle' => 'Pipeline de Ventas',
            'filtro_etapa' => $etapa,
        ];
        $this->view('vendedor/pipeline', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/pipeline');
        }
        $userId = $this->getUserId();
        $id = $this->model->create([
            'id_vendedor' => $userId,
            'id_cliente' => $this->postParam('id_cliente') ?: null,
            'titulo' => $this->postParam('titulo'),
            'valor' => (float) ($this->postParam('valor') ?: 0),
            'etapa' => $this->postParam('etapa', 'prospeccion'),
            'probabilidad' => (int) ($this->postParam('probabilidad') ?: Oportunidad::PROBABILIDADES[$this->postParam('etapa', 'prospeccion')] ?? 10),
            'fecha_cierre_estimada' => $this->postParam('fecha_cierre_estimada') ?: null,
            'notas' => $this->postParam('notas') ?: null,
        ]);
        registrar_log('crear', 'oportunidad', $id, $this->postParam('titulo'));
        set_flash('success', 'Oportunidad creada correctamente');
        $this->redirect('/pipeline');
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            $this->json(['error' => 'Token inválido'], 403);
        }
        $id = (int) $params['id'];
        $opp = $this->model->find($id);
        if (!$opp || (int) $opp['id_vendedor'] !== $this->getUserId()) {
            $this->json(['error' => 'No autorizado'], 403);
        }
        $data = [];
        foreach (['titulo', 'etapa', 'valor', 'probabilidad', 'fecha_cierre_estimada', 'notas', 'id_cliente'] as $field) {
            $val = $this->postParam($field);
            if ($val !== null) {
                $data[$field] = $field === 'valor' ? (float) $val : ($field === 'probabilidad' ? (int) $val : ($field === 'id_cliente' ? ($val ?: null) : $val));
            }
        }
        if (!empty($data['etapa']) && !isset($data['probabilidad'])) {
            $data['probabilidad'] = Oportunidad::PROBABILIDADES[$data['etapa']] ?? 0;
        }
        $this->model->update($id, $data);
        registrar_log('actualizar', 'oportunidad', $id, $data['titulo'] ?? '');
        if ($this->isPost() && empty($this->getParam('redirect'))) {
            $this->json(['success' => true]);
        }
        set_flash('success', 'Oportunidad actualizada');
        $this->redirect('/pipeline');
    }

    public function updateEtapa(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            $this->json(['error' => 'Token inválido'], 403);
        }
        $id = (int) $params['id'];
        $opp = $this->model->find($id);
        if (!$opp || (int) $opp['id_vendedor'] !== $this->getUserId()) {
            $this->json(['error' => 'No autorizado'], 403);
        }
        $etapa = $this->postParam('etapa');
        if (!array_key_exists($etapa, Oportunidad::ETAPAS)) {
            $this->json(['error' => 'Etapa inválida'], 400);
        }
        $this->model->update($id, [
            'etapa' => $etapa,
            'probabilidad' => Oportunidad::PROBABILIDADES[$etapa] ?? 0,
        ]);
        $this->json(['success' => true]);
    }

    public function destroy(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/pipeline');
        }
        $id = (int) $params['id'];
        $opp = $this->model->find($id);
        if (!$opp || (int) $opp['id_vendedor'] !== $this->getUserId()) {
            set_flash('error', 'No autorizado');
            $this->redirect('/pipeline');
        }
        $this->model->update($id, ['activo' => 0]);
        registrar_log('eliminar', 'oportunidad', $id, $opp['titulo']);
        set_flash('success', 'Oportunidad eliminada');
        $this->redirect('/pipeline');
    }

    public function data(): void
    {
        $this->checkAccess();
        $userId = $this->getUserId();
        $this->json($this->model->findByVendedor($userId));
    }

    public function resumen(): void
    {
        $this->checkAccess();
        $userId = $this->getUserId();
        $this->json([
            'pipeline' => $this->model->getPipelineResumen($userId),
            'total' => $this->model->getTotalPipeline($userId),
            'tasa' => $this->model->getTasaConversion($userId),
        ]);
    }
}

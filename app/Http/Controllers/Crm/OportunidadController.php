<?php
namespace App\Http\Controllers\Crm;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Oportunidad;
use App\Models\Vendedor;
use App\Repositories\ClienteRepository;

class OportunidadController extends Controller
{
    private Oportunidad $model;
    private ClienteRepository $clienteRepository;
    private Database $db;

    public function __construct()
    {
        $this->model = new Oportunidad();
        $this->clienteRepository = new ClienteRepository();
        $this->db = Database::getInstance();
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
        $vendedorFiltro = es_vendedor() ? $userId : (int) ($this->getParam('vendedor') ?: 0);
        $rol = (int) user_rol();

        $filters = array_filter([
            'etapa' => $etapa,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'valor_min' => $valorMin,
        ]);

        $oportunidades = $filters
            ? $this->model->searchVisible($filters, $userId, $rol, $vendedorFiltro ?: null)
            : $this->model->findVisibleByUser($userId, $rol, $vendedorFiltro ?: null);

        $data = [
            'oportunidades' => $oportunidades,
            'etapas' => Oportunidad::ETAPAS,
            'clientes' => $this->getClientesForPipeline($vendedorFiltro ?: null),
            'vendedores' => (new Vendedor())->getVendedores(),
            'total_pipeline' => $this->model->getTotalPipelineVisible($userId, $rol, $vendedorFiltro ?: null),
            'tasa_conversion' => $this->model->getTasaConversionVisible($userId, $rol, $vendedorFiltro ?: null),
            'pageTitle' => 'Pipeline de Ventas',
            'filtro_etapa' => $etapa,
            'filtro_vendedor' => $vendedorFiltro ?: null,
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
        $userId = $this->resolveVendedorId();
        if (!$userId) {
            $this->fail('Vendedor no válido', '/pipeline', 422);
        }
        $etapa = $this->postParam('etapa', 'prospeccion');
        if (!$this->isValidEtapa($etapa)) {
            $this->fail('Etapa inválida', '/pipeline', 422);
        }
        $titulo = trim((string) $this->postParam('titulo'));
        if ($titulo === '') {
            $this->fail('El título es obligatorio', '/pipeline', 422);
        }
        $idCliente = $this->postParam('id_cliente') ?: null;
        if (!$this->clienteValido($idCliente, $userId)) {
            $this->fail('Cliente no válido para este vendedor', '/pipeline', 422);
        }
        $id = $this->model->create([
            'id_vendedor' => $userId,
            'id_cliente' => $idCliente,
            'titulo' => $titulo,
            'valor' => (float) ($this->postParam('valor') ?: 0),
            'etapa' => $etapa,
            'probabilidad' => $this->resolveProbabilidad($etapa),
            'fecha_cierre_estimada' => $this->postParam('fecha_cierre_estimada') ?: null,
            'notas' => $this->postParam('notas') ?: null,
        ]);
        registrar_log('crear', 'oportunidad', $id, $titulo);
        if ($this->expectsJson()) {
            $this->json(['success' => true, 'id' => $id]);
        }
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
        if (!$opp || !$this->canAccessOpportunity($opp)) {
            $this->json(['error' => 'No autorizado'], 403);
        }
        $data = [];
        foreach (['titulo', 'etapa', 'valor', 'probabilidad', 'fecha_cierre_estimada', 'notas', 'id_cliente'] as $field) {
            $val = $this->postParam($field);
            if ($val !== null) {
                $data[$field] = $field === 'valor' ? (float) $val : ($field === 'probabilidad' ? (int) $val : ($field === 'id_cliente' ? ($val ?: null) : $val));
            }
        }
        if (isset($data['titulo']) && trim((string) $data['titulo']) === '') {
            $this->json(['error' => 'El título es obligatorio'], 422);
        }
        if (isset($data['etapa']) && !$this->isValidEtapa((string) $data['etapa'])) {
            $this->json(['error' => 'Etapa inválida'], 422);
        }
        if (array_key_exists('id_cliente', $data) && !$this->clienteValido($data['id_cliente'], (int) $opp['id_vendedor'])) {
            $this->json(['error' => 'Cliente no válido para este vendedor'], 422);
        }
        if (!empty($data['etapa']) && !isset($data['probabilidad'])) {
            $data['probabilidad'] = Oportunidad::PROBABILIDADES[$data['etapa']] ?? 0;
        }
        $this->model->update($id, $data);
        registrar_log('actualizar', 'oportunidad', $id, $data['titulo'] ?? '');
        if ($this->expectsJson()) {
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
        if (!$opp || !$this->canAccessOpportunity($opp)) {
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
        if (!$opp || !$this->canAccessOpportunity($opp)) {
            if ($this->expectsJson()) {
                $this->json(['error' => 'No autorizado'], 403);
            }
            set_flash('error', 'No autorizado');
            $this->redirect('/pipeline');
        }
        $this->model->update($id, ['activo' => 0]);
        registrar_log('eliminar', 'oportunidad', $id, $opp['titulo']);
        if ($this->expectsJson()) {
            $this->json(['success' => true]);
        }
        set_flash('success', 'Oportunidad eliminada');
        $this->redirect('/pipeline');
    }

    public function data(): void
    {
        $this->checkAccess();
        $userId = $this->getUserId();
        $rol = (int) user_rol();
        $vendedorFiltro = es_vendedor() ? $userId : (int) ($this->getParam('vendedor') ?: 0);
        $this->json($this->model->findVisibleByUser($userId, $rol, $vendedorFiltro ?: null));
    }

    public function resumen(): void
    {
        $this->checkAccess();
        $userId = $this->getUserId();
        $rol = (int) user_rol();
        $vendedorFiltro = es_vendedor() ? $userId : (int) ($this->getParam('vendedor') ?: 0);
        $this->json([
            'pipeline' => $this->model->getPipelineResumenVisible($userId, $rol, $vendedorFiltro ?: null),
            'total' => $this->model->getTotalPipelineVisible($userId, $rol, $vendedorFiltro ?: null),
            'tasa' => $this->model->getTasaConversionVisible($userId, $rol, $vendedorFiltro ?: null),
        ]);
    }

    private function resolveVendedorId(): int
    {
        if (es_vendedor()) {
            return $this->getUserId();
        }
        $id = (int) $this->postParam('id_vendedor');
        if ($id <= 0) {
            return 0;
        }
        $row = $this->db->fetchOne("SELECT id_usuario FROM usuarios WHERE id_usuario = :id AND id_rol = :rol AND activo = 1", [
            'id' => $id,
            'rol' => ROL_VENDEDOR,
        ]);
        return $row ? $id : 0;
    }

    private function clienteValido($idCliente, int $idVendedor): bool
    {
        if (!$idCliente) {
            return true;
        }
        $cliente = $this->clienteRepository->find((int) $idCliente);
        if (!$cliente) {
            return false;
        }
        $clienteVendedor = (int) ($cliente['id_vendedor'] ?? 0);
        return $clienteVendedor === 0 || $clienteVendedor === $idVendedor;
    }

    private function getClientesForPipeline(?int $idVendedor): array
    {
        if (es_vendedor()) {
            return $this->clienteRepository->findByVendedor($this->getUserId());
        }
        if ($idVendedor) {
            return $this->clienteRepository->findByVendedor($idVendedor);
        }
        return $this->db->fetchAll("SELECT id_cliente, razon_social, id_vendedor FROM clientes WHERE activo = 1 ORDER BY razon_social");
    }

    private function canAccessOpportunity(array $opp): bool
    {
        return !es_vendedor() || (int) $opp['id_vendedor'] === $this->getUserId();
    }

    private function isValidEtapa(?string $etapa): bool
    {
        return is_string($etapa) && array_key_exists($etapa, Oportunidad::ETAPAS);
    }

    private function resolveProbabilidad(string $etapa): int
    {
        $prob = $this->postParam('probabilidad');
        if ($prob === null || $prob === '') {
            return Oportunidad::PROBABILIDADES[$etapa] ?? 0;
        }
        return max(0, min(100, (int) $prob));
    }

    private function expectsJson(): bool
    {
        return strtolower((string) $this->getParam('format')) === 'json'
            || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    private function fail(string $message, string $redirect, int $status): void
    {
        if ($this->expectsJson()) {
            $this->json(['error' => $message], $status);
        }
        set_flash('error', $message);
        $this->redirect($redirect);
    }
}

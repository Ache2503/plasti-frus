<?php
namespace App\Http\Controllers\Crm;

use App\Core\Controller;
use App\Models\Interaccion;
use App\Models\Cliente;

class InteraccionController extends Controller
{
    private Interaccion $model;

    public function __construct()
    {
        $this->model = new Interaccion();
    }

    private function checkVendedor(): void
    {
        $this->requireAuth();
        if (!es_vendedor()) {
            $this->json(['error' => 'Acceso denegado'], 403);
        }
    }

    public function historial(array $params): void
    {
        $this->checkVendedor();
        $clienteId = (int) $params['id'];
        $userId = (int) $_SESSION['user_id'];
        $this->json($this->model->findByCliente($clienteId, $userId));
    }

    public function store(): void
    {
        $this->checkVendedor();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            $this->json(['error' => 'Token inválido'], 403);
        }
        $userId = (int) $_SESSION['user_id'];
        $clienteId = (int) $this->postParam('id_cliente');
        $cliente = (new Cliente())->find($clienteId);
        if (!$cliente || (int) ($cliente['id_vendedor'] ?? 0) !== $userId) {
            $this->json(['error' => 'Cliente no válido'], 403);
        }
        $id = $this->model->create([
            'id_cliente' => $clienteId,
            'id_vendedor' => $userId,
            'tipo' => $this->postParam('tipo', 'nota'),
            'descripcion' => $this->postParam('descripcion'),
            'fecha' => $this->postParam('fecha') ?: date('Y-m-d H:i:s'),
        ]);
        $this->json(['success' => true, 'id' => $id]);
    }
}

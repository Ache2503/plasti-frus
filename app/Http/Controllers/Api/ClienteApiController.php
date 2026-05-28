<?php
namespace App\Http\Controllers\Api;

use App\Repositories\ClienteRepository;

class ClienteApiController extends BaseApiController
{
    private ClienteRepository $clienteRepository;

    public function __construct()
    {
        $this->clienteRepository = new ClienteRepository();
    }

    public function index(): void
    {
        $this->success($this->clienteRepository->allWithVendedor());
    }

    public function show(array $params): void
    {
        $id = $this->positiveId($params);
        if ($id === null) {
            $this->error('ID de cliente inválido', 422);
            return;
        }

        $cliente = $this->clienteRepository->findWithVendedor($id);
        if (!$cliente) {
            $this->error('Cliente no encontrado', 404);
            return;
        }
        $this->success($cliente);
    }
}

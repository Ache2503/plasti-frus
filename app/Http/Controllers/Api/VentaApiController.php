<?php
namespace App\Http\Controllers\Api;

use App\Repositories\VentaRepository;

class VentaApiController extends BaseApiController
{
    private VentaRepository $ventaRepository;

    public function __construct()
    {
        $this->ventaRepository = new VentaRepository();
    }

    public function index(): void
    {
        $this->success($this->ventaRepository->allWithRelations());
    }

    public function show(array $params): void
    {
        $id = $this->positiveId($params);
        if ($id === null) {
            $this->error('ID de venta inválido', 422);
            return;
        }

        $venta = $this->ventaRepository->find($id);
        if (!$venta) {
            $this->error('Venta no encontrada', 404);
            return;
        }
        $this->success($venta);
    }
}

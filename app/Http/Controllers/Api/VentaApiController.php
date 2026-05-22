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
        $venta = $this->ventaRepository->find((int) $params['id']);
        if (!$venta) {
            $this->error('Venta no encontrada', 404);
            return;
        }
        $this->success($venta);
    }
}

<?php
namespace App\Http\Controllers\Api;

use App\Repositories\ProductoRepository;

class ProductoApiController extends BaseApiController
{
    private ProductoRepository $productoRepository;

    public function __construct()
    {
        $this->productoRepository = new ProductoRepository();
    }

    public function index(): void
    {
        $this->success($this->productoRepository->all());
    }

    public function show(array $params): void
    {
        $producto = $this->productoRepository->findWithRelations((int) $params['id']);
        if (!$producto) {
            $this->error('Producto no encontrado', 404);
            return;
        }
        $this->success($producto);
    }
}

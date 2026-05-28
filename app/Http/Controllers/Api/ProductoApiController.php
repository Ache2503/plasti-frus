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
        $id = $this->positiveId($params);
        if ($id === null) {
            $this->error('ID de producto inválido', 422);
            return;
        }

        $producto = $this->productoRepository->findWithRelations($id);
        if (!$producto) {
            $this->error('Producto no encontrado', 404);
            return;
        }
        $this->success($producto);
    }
}

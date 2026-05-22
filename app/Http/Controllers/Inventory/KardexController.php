<?php
namespace App\Http\Controllers\Inventory;

use App\Core\Controller;
use App\Services\KardexService;
use App\Repositories\MaterialRepository;

class KardexController extends Controller
{
    private KardexService $kardexService;
    private MaterialRepository $materialRepository;

    public function __construct()
    {
        $this->kardexService = new KardexService();
        $this->materialRepository = new MaterialRepository();
    }

    public function index(): void
    {
        $this->requireRol(3);
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
            'id_material' => $this->getParam('id_material'),
            'movimiento' => $this->getParam('movimiento'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        $data = [
            'movimientos' => $this->kardexService->getAll(),
            'materiales' => $this->materialRepository->all(),
            'pageTitle' => 'Kardex de Materiales',
            'filters' => $filters,
        ];
        $this->view('kardex/index', $data);
    }

    public function create(): void
    {
        $this->requireRol(3);
        $data = [
            'materiales' => $this->materialRepository->all(),
            'pageTitle' => 'Agregar Movimiento',
        ];
        $this->view('kardex/create', $data);
    }

    public function store(): void
    {
        $this->requireRol(3);
        $idMaterial = $this->postParam('id_material');

        $mat = $this->materialRepository->find($idMaterial);
        if (!$mat) {
            set_flash('error', 'Material no encontrado');
            $this->redirect('/kardex/create');
        }

        $this->kardexService->create([
            'id_material' => $idMaterial,
            'fecha' => $this->postParam('fecha'),
            'movimiento' => $this->postParam('movimiento'),
            'cantidad' => (float) ($this->postParam('cantidad') ?: 0),
            'operador' => $this->postParam('operador'),
        ]);

        set_flash('success', 'Movimiento registrado correctamente');
        $this->redirect('/kardex');
    }

    public function detalle($id): void
    {
        $this->requireRol(3);
        $material = $this->materialRepository->find($id);
        if (!$material) {
            set_flash('error', 'Material no encontrado');
            $this->redirect('/kardex');
        }
        $data = [
            'material' => $material,
            'movimientos' => $this->kardexService->getByMaterial((int) $id),
            'pageTitle' => 'Kardex: ' . $material['nombre'],
        ];
        $this->view('kardex/detalle', $data);
    }
}

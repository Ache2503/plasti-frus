<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\KardexMaterial;
use App\Models\Material;

class KardexController extends Controller
{
    private $kardex;

    public function __construct()
    {
        $this->kardex = new KardexMaterial();
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
        $materialModel = new Material();
        $data = [
            'movimientos' => $this->kardex->getWithMaterial($filters),
            'materiales' => $materialModel->all(),
            'pageTitle' => 'Kardex de Materiales',
            'filters' => $filters,
        ];
        $this->view('kardex/index', $data);
    }

    public function create(): void
    {
        $this->requireRol(3);
        $materialModel = new Material();
        $data = [
            'materiales' => $materialModel->all(),
            'pageTitle' => 'Agregar Movimiento',
        ];
        $this->view('kardex/create', $data);
    }

    public function store(): void
    {
        $this->requireRol(3);
        $idMaterial = $this->postParam('id_material');
        $movimiento = $this->postParam('movimiento');
        $cantidad = (float) ($this->postParam('cantidad') ?: 0);

        $materialModel = new Material();
        $mat = $materialModel->find($idMaterial);
        if (!$mat) {
            set_flash('error', 'Material no encontrado');
            $this->redirect('/kardex/create');
        }

        $stockFinal = $mat['stock_actual_kg'] + ($movimiento === 'entrada' ? $cantidad : -$cantidad);

        $this->kardex->create([
            'id_material' => $idMaterial,
            'fecha' => $this->postParam('fecha'),
            'movimiento' => $movimiento,
            'cantidad' => $cantidad,
            'stock_final' => $stockFinal,
            'operador' => $this->postParam('operador'),
        ]);

        $materialModel->updateStock($idMaterial, $movimiento === 'entrada' ? $cantidad : -$cantidad);

        set_flash('success', 'Movimiento registrado correctamente');
        $this->redirect('/kardex');
    }

    public function detalle($id): void
    {
        $this->requireRol(3);
        $materialModel = new Material();
        $material = $materialModel->find($id);
        if (!$material) {
            set_flash('error', 'Material no encontrado');
            $this->redirect('/kardex');
        }
        $data = [
            'material' => $material,
            'movimientos' => $this->kardex->getByMaterial($id),
            'pageTitle' => 'Kardex: ' . $material['nombre'],
        ];
        $this->view('kardex/detalle', $data);
    }
}

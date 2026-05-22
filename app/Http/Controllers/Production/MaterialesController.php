<?php
namespace App\Http\Controllers\Production;

use App\Core\Controller;
use App\Repositories\MaterialRepository;
use App\Models\Material;
use App\Models\Proveedor;

class MaterialesController extends Controller
{
    private MaterialRepository $materialRepo;
    private Material $materialModel;
    private Proveedor $proveedorModel;

    public function __construct()
    {
        $this->materialRepo = new MaterialRepository();
        $this->materialModel = new Material();
        $this->proveedorModel = new Proveedor();
    }

    public function index(): void
    {
        requireRolMultiple([1, 2, 3]);
        $data = [
            'materiales' => $this->materialModel->getWithProveedor(),
            'pageTitle' => 'Materiales',
        ];
        $this->view('materiales/index', $data);
    }

    public function create(): void
    {
        $this->requireRol(3);
        $data = [
            'proveedores' => $this->proveedorModel->all(),
            'pageTitle' => 'Nuevo Material',
        ];
        $this->view('materiales/create', $data);
    }

    public function store(): void
    {
        $this->requireRol(3);
        $data = [
            'id_proveedor' => $this->postParam('id_proveedor') ?: null,
            'tipo' => $this->postParam('tipo'),
            'nombre' => $this->postParam('nombre'),
            'presentacion' => $this->postParam('presentacion'),
            'unidad_medida' => $this->postParam('unidad_medida'),
            'stock_actual_kg' => $this->postParam('stock_actual_kg') ?: 0,
            'punto_reorden_kg' => $this->postParam('punto_reorden_kg') ?: 0,
            'lote_recepcion' => $this->postParam('lote_recepcion'),
        ];
        $this->materialRepo->create($data);
        set_flash('success', 'Material creado correctamente');
        $this->redirect('/materiales');
    }

    public function edit(array $params): void
    {
        $this->requireRol(3);
        $material = $this->materialRepo->find($params['id']);
        if (!$material) {
            set_flash('error', 'Material no encontrado');
            $this->redirect('/materiales');
        }
        $data = [
            'material' => $material,
            'proveedores' => $this->proveedorModel->all(),
            'pageTitle' => 'Editar Material',
        ];
        $this->view('materiales/edit', $data);
    }

    public function update(array $params): void
    {
        $this->requireRol(3);
        $data = [
            'id_proveedor' => $this->postParam('id_proveedor') ?: null,
            'tipo' => $this->postParam('tipo'),
            'nombre' => $this->postParam('nombre'),
            'presentacion' => $this->postParam('presentacion'),
            'unidad_medida' => $this->postParam('unidad_medida'),
            'stock_actual_kg' => $this->postParam('stock_actual_kg') ?: 0,
            'punto_reorden_kg' => $this->postParam('punto_reorden_kg') ?: 0,
            'lote_recepcion' => $this->postParam('lote_recepcion'),
        ];
        $this->materialRepo->update($params['id'], $data);
        set_flash('success', 'Material actualizado correctamente');
        $this->redirect('/materiales');
    }

    public function delete(array $params): void
    {
        $this->requireRol(3);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/materiales');
        }
        $this->materialRepo->delete((int) $params['id']);
        set_flash('success', 'Material eliminado correctamente');
        $this->redirect('/materiales');
    }
}

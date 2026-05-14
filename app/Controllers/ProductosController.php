<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Producto;

class ProductosController extends Controller
{
    private $producto;

    public function __construct()
    {
        $this->producto = new Producto();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $data = [
            'productos' => $this->producto->getWithRelations(),
            'pageTitle' => 'Productos',
            'puedeEliminar' => puedeEliminar(),
        ];
        $this->view('productos/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        $data = [
            'familias' => $this->producto->getFamilias(),
            'lineas' => $this->producto->getLineas(),
            'colores' => $this->producto->getColores(),
            'pageTitle' => 'Nuevo Producto',
        ];
        $this->view('productos/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        $data = [
            'codigo' => $this->postParam('codigo'),
            'nombre' => $this->postParam('nombre'),
            'familia' => $this->postParam('familia'),
            'linea' => $this->postParam('linea'),
            'color' => $this->postParam('color'),
            'peso_unitario_grs' => $this->postParam('peso_unitario_grs') ?: 0,
            'dimensiones' => $this->postParam('dimensiones'),
            'descripcion_comercial' => $this->postParam('descripcion_comercial'),
            'publicar_web' => $this->postParam('publicar_web') ? 1 : 0,
        ];
        $id = $this->producto->create($data);
        registrar_log('crear', 'producto', $id, $data['nombre']);
        set_flash('success', 'Producto creado correctamente');
        $this->redirect('/productos');
    }

    public function show(array $params): void
    {
        $this->checkAccess();
        $producto = $this->producto->getByIdWithRelations($params['id']);
        if (!$producto) {
            set_flash('error', 'Producto no encontrado');
            $this->redirect('/productos');
        }
        $data = [
            'producto' => $producto,
            'recetas' => $this->producto->getRecetasByProducto($params['id']),
            'ordenes' => $this->producto->getOrdenesByProducto($params['id']),
            'pageTitle' => 'Producto: ' . $producto['nombre'],
        ];
        $this->view('productos/show', $data);
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        $producto = $this->producto->find($params['id']);
        if (!$producto) {
            set_flash('error', 'Producto no encontrado');
            $this->redirect('/productos');
        }
        $data = [
            'producto' => $producto,
            'pageTitle' => 'Editar Producto',
        ];
        $this->view('productos/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        $data = [
            'codigo' => $this->postParam('codigo'),
            'nombre' => $this->postParam('nombre'),
            'familia' => $this->postParam('familia'),
            'linea' => $this->postParam('linea'),
            'color' => $this->postParam('color'),
            'peso_unitario_grs' => $this->postParam('peso_unitario_grs') ?: 0,
            'dimensiones' => $this->postParam('dimensiones'),
            'descripcion_comercial' => $this->postParam('descripcion_comercial'),
            'publicar_web' => $this->postParam('publicar_web') ? 1 : 0,
        ];
        $this->producto->update($params['id'], $data);
        registrar_log('actualizar', 'producto', $params['id'], $data['nombre']);
        set_flash('success', 'Producto actualizado correctamente');
        $this->redirect('/productos');
    }

    public function delete(array $params): void
    {
        $this->checkAccess();
        if (!puedeEliminar()) {
            set_flash('error', 'No tienes permisos para eliminar');
            $this->redirect('/productos');
        }
        $this->producto->delete($params['id']);
        registrar_log('eliminar', 'producto', $params['id'], 'Producto eliminado');
        set_flash('success', 'Producto eliminado correctamente');
        $this->redirect('/productos');
    }

}

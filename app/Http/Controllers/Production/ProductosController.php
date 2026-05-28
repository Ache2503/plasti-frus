<?php
namespace App\Http\Controllers\Production;

use App\Core\Controller;
use App\Core\Database;
use App\Repositories\ProductoRepository;
use App\Models\Producto;

class ProductosController extends Controller
{
    private ProductoRepository $productoRepo;
    private Producto $productoModel;

    public function __construct()
    {
        $this->productoRepo = new ProductoRepository();
        $this->productoModel = new Producto();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $pagination = paginate($db, "SELECT * FROM productos ORDER BY id_producto DESC", [], 15);
        $data = [
            'productos' => $pagination->items,
            'pageTitle' => 'Productos',
            'pagination' => $pagination,
            'puedeEliminar' => puedeEliminar(),
        ];
        $this->view('productos/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        $data = [
            'familias' => $this->productoModel->getFamilias(),
            'lineas' => $this->productoModel->getLineas(),
            'colores' => $this->productoModel->getColores(),
            'pageTitle' => 'Nuevo Producto',
        ];
        $this->view('productos/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/productos/create');
        }
        if (!$this->postParam('codigo') || !$this->postParam('nombre')) {
            set_flash('error', 'Código y nombre son obligatorios');
            $this->redirect('/productos/create');
        }
        if ((float) ($this->postParam('peso_unitario_grs') ?: 0) < 0) {
            set_flash('error', 'El peso unitario no puede ser negativo');
            $this->redirect('/productos/create');
        }
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
        $id = $this->productoRepo->create($data);
        registrar_log('crear', 'producto', $id, $data['nombre']);
        \App\Services\AuditService::log('INSERT', 'Producto', $id, "Producto creado: {$data['nombre']}");
        set_flash('success', 'Producto creado correctamente');
        $this->redirect('/productos');
    }

    public function show(array $params): void
    {
        $this->checkAccess();
        $producto = $this->productoModel->getByIdWithRelations($params['id']);
        if (!$producto) {
            set_flash('error', 'Producto no encontrado');
            $this->redirect('/productos');
        }
        $data = [
            'producto' => $producto,
            'recetas' => $this->productoModel->getRecetasByProducto($params['id']),
            'ordenes' => $this->productoModel->getOrdenesByProducto($params['id']),
            'pageTitle' => 'Producto: ' . $producto['nombre'],
        ];
        $this->view('productos/show', $data);
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        $producto = $this->productoRepo->find($params['id']);
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
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/productos');
        }
        if (!$this->postParam('codigo') || !$this->postParam('nombre')) {
            set_flash('error', 'Código y nombre son obligatorios');
            $this->redirect('/productos/edit/' . $params['id']);
        }
        if ((float) ($this->postParam('peso_unitario_grs') ?: 0) < 0) {
            set_flash('error', 'El peso unitario no puede ser negativo');
            $this->redirect('/productos/edit/' . $params['id']);
        }
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
        $this->productoRepo->update($params['id'], $data);
        registrar_log('actualizar', 'producto', $params['id'], $data['nombre']);
        \App\Services\AuditService::log('UPDATE', 'Producto', $params['id'], "Producto actualizado: {$data['nombre']}");
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
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/productos');
        }
        $this->productoRepo->delete((int) $params['id']);
        registrar_log('eliminar', 'producto', $params['id'], 'Producto eliminado');
        \App\Services\AuditService::log('DELETE', 'Producto', $params['id'], 'Producto eliminado');
        set_flash('success', 'Producto eliminado correctamente');
        $this->redirect('/productos');
    }
}

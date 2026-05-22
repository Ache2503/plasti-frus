<?php
namespace App\Http\Controllers\Production;

use App\Core\Controller;
use App\Models\RecetaCabe;
use App\Models\Producto;
use App\Models\Maquina;
use App\Models\Material;

class RecetasController extends Controller
{
    private RecetaCabe $recetaModel;
    private Producto $productoModel;
    private Maquina $maquinaModel;
    private Material $materialModel;

    public function __construct()
    {
        $this->recetaModel = new RecetaCabe();
        $this->productoModel = new Producto();
        $this->maquinaModel = new Maquina();
        $this->materialModel = new Material();
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
            'recetas' => $this->recetaModel->getWithRelations(),
            'pageTitle' => 'Recetas de Producción',
            'puedeEliminar' => puedeEliminar(),
        ];
        $this->view('recetas/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        $data = [
            'productos' => $this->productoModel->all(),
            'maquinas' => $this->maquinaModel->all(),
            'materiales' => $this->materialModel->all(),
            'pageTitle' => 'Nueva Receta',
        ];
        $this->view('recetas/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        $data = [
            'id_producto' => $this->postParam('id_producto'),
            'id_maquina' => $this->postParam('id_maquina') ?: null,
            'version' => $this->postParam('version'),
            'fecha_version' => $this->postParam('fecha_version') ?: date('Y-m-d'),
            'temperatura_inyeccion_C' => $this->postParam('temperatura_inyeccion_C') ?: null,
            'presion_inyeccion_bar' => $this->postParam('presion_inyeccion_bar') ?: null,
            'tiempo_enfriamiento_s' => $this->postParam('tiempo_enfriamiento_s') ?: null,
        ];
        $idReceta = $this->recetaModel->create($data);

        $materiales = $this->postParam('materiales');
        $porcentajes = $this->postParam('porcentajes');
        $tolerancias = $this->postParam('tolerancias');
        if ($materiales && is_array($materiales)) {
            foreach ($materiales as $i => $idMaterial) {
                if (!empty($idMaterial) && isset($porcentajes[$i])) {
                    $this->recetaModel->addDetalle([
                        'id_receta_cabe' => $idReceta,
                        'id_material' => $idMaterial,
                        'porcentaje_peso' => $porcentajes[$i] ?: 0,
                        'tolerancia_percent' => $tolerancias[$i] ?? null,
                    ]);
                }
            }
        }

        registrar_log('crear', 'receta', $idReceta, "Receta v{$data['version']} - Producto #{$data['id_producto']}");
        set_flash('success', 'Receta creada correctamente');
        $this->redirect('/recetas');
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        $receta = $this->recetaModel->getByIdWithRelations($params['id']);
        if (!$receta) {
            set_flash('error', 'Receta no encontrada');
            $this->redirect('/recetas');
        }
        $data = [
            'receta' => $receta,
            'detalles' => $this->recetaModel->getDetallesByReceta($params['id']),
            'productos' => $this->productoModel->all(),
            'maquinas' => $this->maquinaModel->all(),
            'pageTitle' => 'Editar Receta',
        ];
        $this->view('recetas/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        $data = [
            'id_producto' => $this->postParam('id_producto'),
            'id_maquina' => $this->postParam('id_maquina') ?: null,
            'version' => $this->postParam('version'),
            'fecha_version' => $this->postParam('fecha_version') ?: date('Y-m-d'),
            'temperatura_inyeccion_C' => $this->postParam('temperatura_inyeccion_C') ?: null,
            'presion_inyeccion_bar' => $this->postParam('presion_inyeccion_bar') ?: null,
            'tiempo_enfriamiento_s' => $this->postParam('tiempo_enfriamiento_s') ?: null,
        ];
        $this->recetaModel->update($params['id'], $data);
        registrar_log('actualizar', 'receta', $params['id'], "Receta #{$params['id']}");
        set_flash('success', 'Receta actualizada correctamente');
        $this->redirect('/recetas');
    }

    public function delete(array $params): void
    {
        $this->checkAccess();
        if (!puedeEliminar()) {
            set_flash('error', 'No tienes permisos para eliminar');
            $this->redirect('/recetas');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/recetas');
        }
        $this->recetaModel->delete((int) $params['id']);
        registrar_log('eliminar', 'receta', $params['id'], 'Receta eliminada');
        set_flash('success', 'Receta eliminada correctamente');
        $this->redirect('/recetas');
    }
}

<?php
namespace App\Http\Controllers\Production;

use App\Core\Controller;
use App\Core\Database;
use App\Services\OrdenService;
use App\Models\Producto;
use App\Models\RecetaCabe;
use App\Models\Molde;
use App\Models\Maquina;
use App\Models\OrdenCabe;
use App\Http\Requests\OrdenRequest;

class OrdenesController extends Controller
{
    private OrdenService $ordenService;
    private Producto $productoModel;
    private RecetaCabe $recetaModel;
    private Molde $moldeModel;
    private Maquina $maquinaModel;
    private OrdenCabe $ordenModel;

    public function __construct()
    {
        $this->ordenService = new OrdenService();
        $this->productoModel = new Producto();
        $this->recetaModel = new RecetaCabe();
        $this->moldeModel = new Molde();
        $this->maquinaModel = new Maquina();
        $this->ordenModel = new OrdenCabe();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
            'turno' => $this->getParam('turno'),
            'id_producto' => $this->getParam('id_producto'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $db = Database::getInstance();
        $query = "
            SELECT oc.*, 
                   p.nombre as producto_nombre, p.codigo as producto_codigo,
                   m.nombre as maquina_nombre,
                   md.nombre_molde as molde_nombre,
                   rc.version as receta_version
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            LEFT JOIN maquinas m ON oc.id_maquina = m.id_maquina
            LEFT JOIN moldes md ON oc.id_molde = md.id_molde
            LEFT JOIN recetas_cabecera rc ON oc.id_receta = rc.id_receta_cabe
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "oc.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "oc.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['turno'])) {
            $where[] = "oc.turno = :turno";
            $params['turno'] = $filters['turno'];
        }
        if (!empty($filters['id_producto'])) {
            $where[] = "oc.id_producto = :id_producto";
            $params['id_producto'] = $filters['id_producto'];
        }

        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        $query .= ' ORDER BY oc.id_orden_cabe DESC';

        $pagination = paginate($db, $query, $params, 15);

        $data = [
            'ordenes' => $pagination->items,
            'stats' => $this->ordenService->getStats(),
            'productos' => $this->productoModel->all(),
            'pageTitle' => 'Órdenes de Producción',
            'filters' => $filters,
            'pagination' => $pagination,
            'puedeEliminar' => puedeEliminar(),
        ];
        $this->view('ordenes/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        $data = [
            'productos' => $this->productoModel->all(),
            'recetas' => $this->recetaModel->all(),
            'moldes' => $this->moldeModel->all(),
            'maquinas' => $this->maquinaModel->all(),
            'pageTitle' => 'Nueva Orden de Producción',
        ];
        $this->view('ordenes/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/ordenes/create');
        }
        $request = new OrdenRequest();
        $validated = $request->validate();
        if (!$this->validateOrdenRelations($validated['id_producto'], $this->postParam('id_maquina'), $this->postParam('id_molde'), $this->postParam('id_receta'))) {
            set_flash('error', 'Producto, máquina, molde o receta no válido');
            $this->redirect('/ordenes/create');
        }
        $data = array_merge($validated, [
            'id_receta' => $this->postParam('id_receta') ?: null,
            'id_molde' => $this->postParam('id_molde') ?: null,
            'id_maquina' => $this->postParam('id_maquina') ?: null,
            'cantidad_real_buenas' => $this->postParam('cantidad_real_buenas') ?: null,
            'estatus' => 'pendiente',
        ]);
        $id = $this->ordenService->create($data);
        \App\Services\AuditService::log('INSERT', 'Orden', $id, "Orden #{$id} creada");
        set_flash('success', 'Orden de producción creada correctamente');
        $this->redirect('/ordenes');
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        $orden = $this->ordenModel->find($params['id']);
        if (!$orden) {
            set_flash('error', 'Orden no encontrada');
            $this->redirect('/ordenes');
        }
        $data = [
            'orden' => $orden,
            'productos' => $this->productoModel->all(),
            'recetas' => $this->recetaModel->all(),
            'moldes' => $this->moldeModel->all(),
            'maquinas' => $this->maquinaModel->all(),
            'pageTitle' => 'Editar Orden de Producción',
        ];
        $this->view('ordenes/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/ordenes');
        }
        if (!$this->ordenModel->find($params['id'])) {
            set_flash('error', 'Orden no encontrada');
            $this->redirect('/ordenes');
        }
        $request = new OrdenRequest();
        $validated = $request->validate();
        if (!$this->validateOrdenRelations($validated['id_producto'], $this->postParam('id_maquina'), $this->postParam('id_molde'), $this->postParam('id_receta'))) {
            set_flash('error', 'Producto, máquina, molde o receta no válido');
            $this->redirect('/ordenes/edit/' . $params['id']);
        }
        $data = array_merge($validated, [
            'id_receta' => $this->postParam('id_receta') ?: null,
            'id_molde' => $this->postParam('id_molde') ?: null,
            'id_maquina' => $this->postParam('id_maquina') ?: null,
            'cantidad_real_buenas' => $this->postParam('cantidad_real_buenas') ?: null,
            'estatus' => $this->postParam('estatus') ?: 'pendiente',
        ]);
        $this->ordenModel->update($params['id'], $data);
        \App\Services\AuditService::log('UPDATE', 'Orden', $params['id'], "Orden #{$params['id']} actualizada");
        set_flash('success', 'Orden de producción actualizada correctamente');
        $this->redirect('/ordenes');
    }

    public function detalle(array $params): void
    {
        $this->checkAccess();
        $orden = $this->ordenService->findById($params['id']);
        if (!$orden) {
            set_flash('error', 'Orden no encontrada');
            $this->redirect('/ordenes');
        }
        $data = [
            'orden' => $orden,
            'mermas' => $this->ordenService->getMermas($params['id']),
            'seguimiento' => $this->ordenService->getSeguimiento($params['id']),
            'pageTitle' => 'Orden #' . $params['id'],
        ];
        $this->view('ordenes/detalle', $data);
    }

    public function delete(array $params): void
    {
        $this->checkAccess();
        if (!puedeEliminar()) {
            set_flash('error', 'No tienes permisos para eliminar');
            $this->redirect('/ordenes');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/ordenes');
        }
        $this->ordenService->delete((int) $params['id']);
        \App\Services\AuditService::log('DELETE', 'Orden', $params['id'], 'Orden eliminada');
        set_flash('success', 'Orden eliminada correctamente');
        $this->redirect('/ordenes');
    }

    public function iniciar($params): void
    {
        $this->checkAccess();
        try {
            $this->ordenService->start($params['id']);
            \App\Services\AuditService::log('UPDATE', 'Orden', $params['id'], 'Orden iniciada');
            set_flash('success', 'Orden iniciada correctamente');
        } catch (\RuntimeException $e) {
            set_flash('error', $e->getMessage());
        }
        $this->redirect('/mis-ordenes');
    }

    public function completar($params): void
    {
        $this->checkAccess();
        $id = $params['id'];
        $data = [
            'buenas' => $this->postParam('cantidad_real_buenas') ?: 0,
            'merma_kg' => $this->postParam('merma_kg') ?: null,
            'merma_tipo' => $this->postParam('merma_tipo') ?: 'general',
            'merma_destino' => $this->postParam('merma_destino') ?: 'reciclaje',
            'observaciones' => $this->postParam('observaciones') ?: '',
        ];
        $this->ordenService->complete($id, $data);
        \App\Services\AuditService::log('UPDATE', 'Orden', $id, "Orden completada, buenas: {$data['buenas']}");
        set_flash('success', 'Orden completada correctamente');
        $redirect = $this->postParam('redirect_to') ?: '/ordenes/detalle/' . $id;
        $this->redirect($redirect);
    }

    public function misOrdenes(): void
    {
        $this->checkAccess();
        $hoy = date('Y-m-d');
        $fechaBuscar = $this->getParam('fecha') ?: $hoy;
        $turnos = ['matutino', 'vespertino', 'nocturno'];
        $turnoActual = $_SESSION['operador_turno_override'] ?? $turnos[(int) ((int) date('H') / 8)] ?? 'matutino';
        $turnoFiltro = $this->getParam('turno') ?: $turnoActual;

        $where = "o.fecha = :fecha";
        $dbParams = ['fecha' => $fechaBuscar];
        if ($this->getParam('turno')) {
            $where .= " AND o.turno = :turno";
            $dbParams['turno'] = $turnoFiltro;
        }

        $ordenes = $this->ordenModel->fetchAll("
            SELECT o.*, p.nombre as producto_nombre, m.nombre as maquina_nombre,
                   rc.temperatura_inyeccion_C, rc.presion_inyeccion_bar, rc.tiempo_enfriamiento_s
            FROM ordenes_cabecera o
            LEFT JOIN productos p ON o.id_producto = p.id_producto
            LEFT JOIN maquinas m ON o.id_maquina = m.id_maquina
            LEFT JOIN recetas_cabecera rc ON o.id_receta = rc.id_receta_cabe
            WHERE {$where}
            ORDER BY o.id_orden_cabe DESC
        ", $dbParams);
        $data = [
            'ordenes' => $ordenes,
            'turno_actual' => $turnoActual,
            'fecha_hoy' => $hoy,
            'fecha_buscar' => $fechaBuscar,
            'turno_filtro' => $turnoFiltro,
            'pageTitle' => ($fechaBuscar === $hoy ? 'Mis Órdenes de Hoy' : 'Órdenes del ' . format_date($fechaBuscar)),
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
        ];
        $this->view('ordenes/mis_ordenes', $data);
    }

    private function validateOrdenRelations($idProducto, $idMaquina = null, $idMolde = null, $idReceta = null): bool
    {
        if (!$this->productoModel->find($idProducto)) {
            return false;
        }
        if ($idMaquina && !$this->maquinaModel->find($idMaquina)) {
            return false;
        }
        if ($idMolde && !$this->moldeModel->find($idMolde)) {
            return false;
        }
        if ($idReceta && !$this->recetaModel->find($idReceta)) {
            return false;
        }
        return true;
    }
}

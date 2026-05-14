<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrdenCabe;
use App\Models\Producto;
use App\Models\RecetaCabe;
use App\Models\Molde;
use App\Models\Maquina;

class OrdenesController extends Controller
{
    private $orden;

    public function __construct()
    {
        $this->orden = new OrdenCabe();
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
        $productoModel = new Producto();
        $data = [
            'ordenes' => $this->orden->getWithRelations($filters),
            'stats' => $this->orden->getStats(),
            'productos' => $productoModel->all(),
            'pageTitle' => 'Órdenes de Producción',
            'filters' => $filters,
            'puedeEliminar' => puedeEliminar(),
        ];
        $this->view('ordenes/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        $productoModel = new Producto();
        $recetaModel = new RecetaCabe();
        $moldeModel = new Molde();
        $maquinaModel = new Maquina();

        $data = [
            'productos' => $productoModel->all(),
            'recetas' => $recetaModel->all(),
            'moldes' => $moldeModel->all(),
            'maquinas' => $maquinaModel->all(),
            'pageTitle' => 'Nueva Orden de Producción',
        ];
        $this->view('ordenes/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        $data = [
            'id_producto' => $this->postParam('id_producto'),
            'id_receta' => $this->postParam('id_receta') ?: null,
            'id_molde' => $this->postParam('id_molde') ?: null,
            'id_maquina' => $this->postParam('id_maquina') ?: null,
            'cantidad_planificada' => $this->postParam('cantidad_planificada') ?: 0,
            'cantidad_real_buenas' => $this->postParam('cantidad_real_buenas') ?: null,
            'fecha' => $this->postParam('fecha'),
            'turno' => $this->postParam('turno'),
            'estatus' => 'pendiente',
        ];
        $id = $this->orden->create($data);
        registrar_log('crear', 'orden', $id, "Orden #{$id} - Producto #{$data['id_producto']}");

        $db = \App\Core\Database::getInstance();
        $operadores = $db->fetchAll("SELECT id_usuario FROM usuarios WHERE id_rol = 2 AND activo = 1");
        foreach ($operadores as $op) {
            notificar_operador((int) $op['id_usuario'], 'nueva_orden', "Nueva Orden #{$id}",
                "Se creó una orden para el turno {$data['turno']} — {$data['cantidad_planificada']} pzas", $id);
        }

        $supervisores = $db->fetchAll("SELECT id_usuario FROM usuarios WHERE id_rol = 3 AND activo = 1");
        foreach ($supervisores as $sup) {
            notificar_supervisor((int) $sup['id_usuario'], 'nueva_orden', "Nueva Orden #{$id}",
                "Se creó la orden #{$id} para {$data['turno']} — {$data['cantidad_planificada']} pzas", $id);
        }

        set_flash('success', 'Orden de producción creada correctamente');
        $this->redirect('/ordenes');
    }

    public function detalle(array $params): void
    {
        $this->checkAccess();
        $orden = $this->orden->getByIdWithRelations($params['id']);
        if (!$orden) {
            set_flash('error', 'Orden no encontrada');
            $this->redirect('/ordenes');
        }
        $data = [
            'orden' => $orden,
            'mermas' => $this->orden->getMermasByOrden($params['id']),
            'seguimiento' => $this->orden->getSeguimientoByOrden($params['id']),
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
        $this->orden->delete($params['id']);
        registrar_log('eliminar', 'orden', $params['id'], 'Orden eliminada');
        set_flash('success', 'Orden eliminada correctamente');
        $this->redirect('/ordenes');
    }

    public function iniciar($params): void
    {
        $this->checkAccess();
        $db = \App\Core\Database::getInstance();
        $orden = $this->orden->find($params['id']);
        if (!$orden || $orden['cantidad_real_buenas']) {
            set_flash('error', 'No se puede iniciar esta orden');
            $this->redirect('/mis-ordenes');
        }
        $this->orden->update($params['id'], ['estatus' => 'en_progreso']);
        $db->insert('seguimiento_ordenes', [
            'id_orden_cabe' => $params['id'],
            'fecha' => date('Y-m-d H:i:s'),
            'estatus' => 'en_progreso',
            'comentarios' => 'Orden iniciada por ' . (user_nombre_completo() ?: 'operador'),
        ]);
        registrar_log('iniciar', 'orden', $params['id'], 'Orden iniciada');
        set_flash('success', 'Orden iniciada correctamente');
        $this->redirect('/mis-ordenes');
    }

    public function completar($params): void
    {
        $this->checkAccess();
        $db = \App\Core\Database::getInstance();
        $id = $params['id'];
        $buenas = $this->postParam('cantidad_real_buenas') ?: 0;
        $mermaKg = $this->postParam('merma_kg') ?: null;
        $mermaTipo = $this->postParam('merma_tipo') ?: 'general';
        $observaciones = $this->postParam('observaciones') ?: '';

        $this->orden->update($id, [
            'cantidad_real_buenas' => $buenas,
            'estatus' => 'completada',
        ]);
        $db->insert('seguimiento_ordenes', [
            'id_orden_cabe' => $id,
            'fecha' => date('Y-m-d H:i:s'),
            'estatus' => 'completada',
            'comentarios' => 'Cantidad real: ' . $buenas . ($observaciones ? ' — ' . $observaciones : ''),
        ]);
        if ($mermaKg && $mermaKg > 0) {
            $db->insert('ordenes_merma', [
                'id_orden_cabe' => $id,
                'tipo_merma' => $mermaTipo,
                'cantidad_kg' => $mermaKg,
                'destino' => $this->postParam('merma_destino') ?: 'reciclaje',
            ]);
        }

        $orden = $this->orden->find($id);
        if ($orden && !empty($orden['id_molde'])) {
            $moldeInfo = $db->fetchOne("SELECT numero_cavidades FROM moldes WHERE id_molde = :id", ['id' => $orden['id_molde']]);
            if ($moldeInfo) {
                $ciclos = (int) $buenas / max((int) ($moldeInfo['numero_cavidades'] ?? 1), 1);
                $db->query("UPDATE moldes SET ciclos_acumulados = COALESCE(ciclos_acumulados, 0) + :ciclos WHERE id_molde = :id", [
                    'ciclos' => max(1, (int) ceil($ciclos)),
                    'id' => $orden['id_molde'],
                ]);
            }
        }

        registrar_log('completar', 'orden', $id, "Cantidad real: {$buenas}");
        set_flash('success', 'Orden completada correctamente');
        $redirect = $this->postParam('redirect_to') ?: '/ordenes/detalle/' . $id;
        $this->redirect($redirect);
    }

    public function misOrdenes(): void
    {
        $this->checkAccess();
        $db = \App\Core\Database::getInstance();
        $hoy = date('Y-m-d');
        $fechaBuscar = $this->getParam('fecha') ?: $hoy;
        $turnos = ['matutino', 'vespertino', 'nocturno'];
        $turnoActual = $_SESSION['operador_turno_override'] ?? $turnos[(int) ((int) date('H') / 8)] ?? 'matutino';
        $turnoFiltro = $this->getParam('turno') ?: $turnoActual;

        $where = "o.fecha = :fecha";
        $params = ['fecha' => $fechaBuscar];
        if ($this->getParam('turno')) {
            $where .= " AND o.turno = :turno";
            $params['turno'] = $turnoFiltro;
        }

        $ordenes = $db->fetchAll("
            SELECT o.*, p.nombre as producto_nombre, m.nombre as maquina_nombre,
                   rc.temperatura_inyeccion_C, rc.presion_inyeccion_bar, rc.tiempo_enfriamiento_s
            FROM ordenes_cabecera o
            LEFT JOIN productos p ON o.id_producto = p.id_producto
            LEFT JOIN maquinas m ON o.id_maquina = m.id_maquina
            LEFT JOIN recetas_cabecera rc ON o.id_receta = rc.id_receta_cabe
            WHERE {$where}
            ORDER BY o.id_orden_cabe DESC
        ", $params);
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
}

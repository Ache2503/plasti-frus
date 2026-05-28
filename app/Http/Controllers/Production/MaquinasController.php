<?php
namespace App\Http\Controllers\Production;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Maquina;

class MaquinasController extends Controller
{
    private Maquina $maquinaModel;

    public function __construct()
    {
        $this->maquinaModel = new Maquina();
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
        $pagination = paginate($db, "SELECT * FROM maquinas ORDER BY id_maquina DESC", [], 15);
        $data = [
            'maquinas' => $pagination->items,
            'pageTitle' => 'Máquinas',
            'pagination' => $pagination,
            'puedeEliminar' => puedeEliminar(),
        ];
        $this->view('maquinas/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        $data = [
            'pageTitle' => 'Nueva Máquina',
        ];
        $this->view('maquinas/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/maquinas/create');
        }
        if (!$this->postParam('nombre')) {
            set_flash('error', 'El nombre de la máquina es obligatorio');
            $this->redirect('/maquinas/create');
        }
        $data = [
            'nombre' => $this->postParam('nombre'),
            'modelo' => $this->postParam('modelo'),
            'numero_serie' => $this->postParam('numero_serie'),
            'estatus' => $this->postParam('estatus') ?: 'activo',
        ];
        $id = $this->maquinaModel->create($data);
        registrar_log('crear', 'maquina', $id, $data['nombre']);
        set_flash('success', 'Máquina creada correctamente');
        $this->redirect('/maquinas');
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        $maquina = $this->maquinaModel->find($params['id']);
        if (!$maquina) {
            set_flash('error', 'Máquina no encontrada');
            $this->redirect('/maquinas');
        }
        $data = [
            'maquina' => $maquina,
            'pageTitle' => 'Editar Máquina',
        ];
        $this->view('maquinas/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/maquinas');
        }
        if (!$this->postParam('nombre')) {
            set_flash('error', 'El nombre de la máquina es obligatorio');
            $this->redirect('/maquinas/edit/' . $params['id']);
        }
        $data = [
            'nombre' => $this->postParam('nombre'),
            'modelo' => $this->postParam('modelo'),
            'numero_serie' => $this->postParam('numero_serie'),
            'estatus' => $this->postParam('estatus') ?: 'activo',
        ];
        $this->maquinaModel->update($params['id'], $data);
        registrar_log('actualizar', 'maquina', $params['id'], $data['nombre']);
        set_flash('success', 'Máquina actualizada correctamente');
        $this->redirect('/maquinas');
    }

    public function delete(array $params): void
    {
        $this->checkAccess();
        if (!puedeEliminar()) {
            set_flash('error', 'No tienes permisos para eliminar');
            $this->redirect('/maquinas');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/maquinas');
        }
        $this->maquinaModel->delete((int) $params['id']);
        registrar_log('eliminar', 'maquina', $params['id'], 'Máquina eliminada');
        set_flash('success', 'Máquina eliminada correctamente');
        $this->redirect('/maquinas');
    }

    public function estado(): void
    {
        $this->checkAccess();
        $db = \App\Core\Database::getInstance();
        $hasEstado = $db->columnExists('maquinas', 'estado');
        $hasSeccion = $db->columnExists('maquinas', 'seccion');
        $hasOrdenEstatus = $db->columnExists('ordenes_cabecera', 'estatus');

        $estadoColumn = $hasEstado ? 'm.estado' : "'apagada'";
        $ordenesPendientesWhere = $hasOrdenEstatus
            ? "o.estatus IS NULL OR o.estatus = 'pendiente'"
            : "o.cantidad_real_buenas IS NULL OR o.cantidad_real_buenas = 0";

        $seccion = $hasSeccion ? $this->getParam('seccion') : null;
        $where = '';
        $params = [];
        if ($seccion) {
            $where = 'WHERE m.seccion = :seccion';
            $params['seccion'] = $seccion;
        }
        $maquinas = $db->fetchAll("
            SELECT m.*,
                   CASE WHEN bp.id_bitacora IS NOT NULL THEN 'detenida' ELSE COALESCE({$estadoColumn}, 'apagada') END as estado_real,
                   bp.motivo_paro, bp.hora_inicio as paro_desde,
                   (SELECT COUNT(*) FROM ordenes_cabecera o WHERE o.id_maquina = m.id_maquina AND ({$ordenesPendientesWhere})) as ordenes_pendientes,
                   (SELECT MAX(o.fecha) FROM ordenes_cabecera o WHERE o.id_maquina = m.id_maquina AND o.cantidad_real_buenas IS NOT NULL) as ultima_orden
            FROM maquinas m
            LEFT JOIN bitacora_paros bp ON bp.id_maquina = m.id_maquina AND bp.hora_fin IS NULL
            {$where}
            ORDER BY m.nombre
        ", $params);
        $secciones = $hasSeccion
            ? $db->fetchAll("SELECT DISTINCT seccion FROM maquinas WHERE seccion IS NOT NULL AND seccion != '' ORDER BY seccion")
            : [];
        $data = [
            'maquinas' => $maquinas,
            'secciones' => array_column($secciones, 'seccion'),
            'seccion_activa' => $seccion,
            'pageTitle' => 'Estado de Máquinas',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
        ];
        $this->view('maquinas/estado', $data);
    }

    public function estadoJSON(): void
    {
        $this->checkAccess();
        $db = \App\Core\Database::getInstance();
        $hasEstado = $db->columnExists('maquinas', 'estado');
        $hasOrdenEstatus = $db->columnExists('ordenes_cabecera', 'estatus');
        $estadoColumn = $hasEstado ? 'm.estado' : "'apagada'";
        $estadoSelect = $hasEstado ? 'm.estado' : "'apagada' as estado";
        $ordenesActivasWhere = $hasOrdenEstatus
            ? "o.estatus IS NULL OR o.estatus IN ('pendiente','en_progreso')"
            : "o.cantidad_real_buenas IS NULL OR o.cantidad_real_buenas = 0";

        $maquinas = $db->fetchAll("
            SELECT m.id_maquina, m.nombre, {$estadoSelect},
                   CASE WHEN bp.id_bitacora IS NOT NULL THEN 'detenida' ELSE COALESCE({$estadoColumn}, 'apagada') END as estado_real,
                   bp.motivo_paro, bp.hora_inicio as paro_desde,
                   (SELECT COUNT(*) FROM ordenes_cabecera o WHERE o.id_maquina = m.id_maquina AND ({$ordenesActivasWhere})) as ordenes_activas
            FROM maquinas m
            LEFT JOIN bitacora_paros bp ON bp.id_maquina = m.id_maquina AND bp.hora_fin IS NULL
            ORDER BY m.nombre
        ");
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $maquinas]);
        exit;
    }
}

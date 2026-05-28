<?php
namespace App\Http\Controllers\Maintenance;

use App\Core\Controller;
use App\Core\Database;

class MantenimientoController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->requireRol(3);
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
            'tipo_mantenimiento' => $this->getParam('tipo_mantenimiento'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        $data = [
            'mantenimientos' => $this->getMantenimientos($filters),
            'pendientes' => $this->getPendientes(),
            'pageTitle' => 'Mantenimiento de Máquinas',
            'filters' => $filters,
        ];
        $this->view('mantenimiento/index', $data);
    }

    public function create(): void
    {
        $this->requireRol(3);
        $data = [
            'maquinas' => $this->getMaquinas(),
            'tecnicos' => $this->getUsuariosOperativos([1, 2, 3]),
            'pageTitle' => 'Registrar Mantenimiento',
        ];
        $this->view('mantenimiento/create', $data);
    }

    public function store(): void
    {
        $this->requireRol(3);
        $maq = $this->findMaquina($this->postParam('id_maquina'));
        if (!$maq) {
            set_flash('error', 'Máquina no encontrada');
            $this->redirect('/mantenimiento/create');
        }
        $tecnico = $this->findUsuario((int) $this->postParam('id_tecnico_responsable'));
        if (!$tecnico) {
            set_flash('error', 'Técnico responsable no válido');
            $this->redirect('/mantenimiento/create');
        }

        $data = [
            'id_maquina' => $this->postParam('id_maquina'),
            'fecha_mantenimiento' => $this->postParam('fecha_mantenimiento'),
            'tipo_mantenimiento' => $this->postParam('tipo_mantenimiento'),
            'tecnico_responsable' => $tecnico['nombre_completo'],
            'horas_paro' => $this->postParam('horas_paro') ?: 0,
            'resultado' => $this->postParam('resultado'),
        ];
        if ($this->db->columnExists('mantenimientos_maquinas', 'id_tecnico_responsable')) {
            $data['id_tecnico_responsable'] = $tecnico['id_usuario'];
        }
        $this->db->insert('mantenimientos_maquinas', $data);

        $this->updateMaquinaEstatus($this->postParam('id_maquina'), $this->postParam('resultado') === 'completado' ? 'activo' : 'mantenimiento');

        set_flash('success', 'Mantenimiento registrado correctamente');
        $this->redirect('/mantenimiento');
    }

    public function plan(): void
    {
        $this->requireRol(3);
        $data = [
            'maquinas' => $this->getMaquinas(),
            'tecnicos' => $this->getUsuariosOperativos([1, 2, 3]),
            'pageTitle' => 'Plan de Mantenimiento',
        ];
        $this->view('mantenimiento/plan_create', $data);
    }

    public function planStore(): void
    {
        $this->requireRol(3);
        $tecnico = null;
        if ($this->postParam('id_tecnico_responsable')) {
            $tecnico = $this->findUsuario((int) $this->postParam('id_tecnico_responsable'));
            if (!$tecnico) {
                set_flash('error', 'Técnico responsable no válido');
                $this->redirect('/mantenimiento/plan');
            }
        }

        $data = [
            'id_maquina' => $this->postParam('id_maquina'),
            'fecha_programada' => $this->postParam('fecha_programada'),
            'tipo_mantenimiento' => $this->postParam('tipo_mantenimiento'),
            'descripcion' => $this->postParam('descripcion'),
            'frecuencia_horas' => $this->postParam('frecuencia_horas') ?: null,
            'ultimo_mantenimiento' => $this->postParam('ultimo_mantenimiento') ?: null,
            'tecnico_responsable' => $tecnico['nombre_completo'] ?? null,
            'estatus' => 'pendiente',
        ];
        if ($this->db->columnExists('plan_mantenimiento', 'id_tecnico_responsable')) {
            $data['id_tecnico_responsable'] = $tecnico['id_usuario'] ?? null;
        }
        $this->db->insert('plan_mantenimiento', $data);
        set_flash('success', 'Actividad de mantenimiento programada');
        $this->redirect('/mantenimiento');
    }

    public function paros(): void
    {
        $this->requireRol(3);
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        $data = [
            'paros' => $this->getParos($filters),
            'pageTitle' => 'Bitácora de Paros',
            'filters' => $filters,
        ];
        $this->view('mantenimiento/paros', $data);
    }

    public function paroCreate(): void
    {
        $this->requireRol(3);
        $data = [
            'maquinas' => $this->getMaquinas(),
            'operadores' => $this->getUsuariosOperativos([2]),
            'pageTitle' => 'Registrar Paro',
        ];
        $this->view('mantenimiento/paro_create', $data);
    }

    public function paroStore(): void
    {
        $this->requireRol(3);
        $horaInicio = $this->postParam('hora_inicio');
        $horaFin = $this->postParam('hora_fin');
        $duracion = 0;
        if ($horaInicio && $horaFin) {
            $duracion = (strtotime($horaFin) - strtotime($horaInicio)) / 3600;
        }
        $operador = null;
        if ($this->postParam('id_operador')) {
            $operador = $this->findUsuario((int) $this->postParam('id_operador'));
            if (!$operador) {
                set_flash('error', 'Operador no válido');
                $this->redirect('/mantenimiento/paros/create');
            }
        }

        $data = [
            'id_maquina' => $this->postParam('id_maquina'),
            'fecha' => $this->postParam('fecha'),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin ?: null,
            'duracion_paro' => $duracion,
            'motivo_paro' => $this->postParam('motivo_paro'),
            'operador' => $operador['nombre_completo'] ?? null,
            'estatus' => $horaFin ? 'resuelto' : 'activo',
        ];
        if ($this->db->columnExists('bitacora_paros', 'id_operador')) {
            $data['id_operador'] = $operador['id_usuario'] ?? null;
        }
        $this->db->insert('bitacora_paros', $data);
        set_flash('success', 'Paro registrado correctamente');
        $this->redirect('/mantenimiento/paros');
    }

    public function delete(array $params): void
    {
        $this->requireRol(3);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/mantenimiento');
        }
        $this->db->delete('mantenimientos_maquinas', 'id_mantenimiento = :id', ['id' => $params['id']]);
        set_flash('success', 'Registro de mantenimiento eliminado');
        $this->redirect('/mantenimiento');
    }

    public function paroDelete(array $params): void
    {
        $this->requireRol(3);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/mantenimiento/paros');
        }
        $this->db->delete('bitacora_paros', 'id_bitacora = :id', ['id' => $params['id']]);
        set_flash('success', 'Paro eliminado');
        $this->redirect('/mantenimiento/paros');
    }

    private function getMantenimientos(array $filters = []): array
    {
        $sql = "
            SELECT m.*, maq.nombre as maquina_nombre, maq.modelo as maquina_modelo
            FROM mantenimientos_maquinas m
            LEFT JOIN maquinas maq ON m.id_maquina = maq.id_maquina
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "m.fecha_mantenimiento >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "m.fecha_mantenimiento <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['tipo_mantenimiento'])) {
            $where[] = "m.tipo_mantenimiento = :tipo";
            $params['tipo'] = $filters['tipo_mantenimiento'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY m.fecha_mantenimiento DESC';

        return $this->db->fetchAll($sql, $params);
    }

    private function getPendientes(): array
    {
        return $this->db->fetchAll("
            SELECT m.*, maq.nombre as maquina_nombre
            FROM plan_mantenimiento m
            LEFT JOIN maquinas maq ON m.id_maquina = maq.id_maquina
            WHERE m.estatus = 'pendiente' OR m.estatus IS NULL
            ORDER BY m.fecha_programada ASC
        ");
    }

    private function getMaquinas(): array
    {
        return $this->db->fetchAll("SELECT * FROM maquinas ORDER BY id_maquina DESC");
    }

    private function findMaquina($id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM maquinas WHERE id_maquina = :id", ['id' => $id]);
    }

    private function updateMaquinaEstatus($id, string $estatus): int
    {
        return $this->db->update('maquinas', ['estatus' => $estatus], 'id_maquina = :id', ['id' => $id]);
    }

    private function getUsuariosOperativos(array $roles): array
    {
        $placeholders = [];
        $params = [];
        foreach ($roles as $i => $rol) {
            $key = 'rol' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $rol;
        }

        return $this->db->fetchAll("
            SELECT u.id_usuario,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario) as nombre_completo,
                   r.nombre as rol
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            LEFT JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.activo = 1 AND u.id_rol IN (" . implode(',', $placeholders) . ")
            ORDER BY nombre_completo
        ", $params);
    }

    private function findUsuario(int $id): ?array
    {
        return $this->db->fetchOne("
            SELECT u.id_usuario,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario) as nombre_completo
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE u.id_usuario = :id AND u.activo = 1
        ", ['id' => $id]) ?: null;
    }

    private function getParos(array $filters = []): array
    {
        $sql = "
            SELECT bp.*, m.nombre as maquina_nombre
            FROM bitacora_paros bp
            LEFT JOIN maquinas m ON bp.id_maquina = m.id_maquina
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "bp.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "bp.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY bp.fecha DESC, bp.hora_inicio DESC';

        return $this->db->fetchAll($sql, $params);
    }
}

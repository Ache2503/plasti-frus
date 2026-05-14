<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\MantenimientoMaquina;
use App\Models\Maquina;

class MantenimientoController extends Controller
{
    private $mantenimiento;

    public function __construct()
    {
        $this->mantenimiento = new MantenimientoMaquina();
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
            'mantenimientos' => $this->mantenimiento->getWithMaquina($filters),
            'pendientes' => $this->mantenimiento->getPendientes(),
            'pageTitle' => 'Mantenimiento de Máquinas',
            'filters' => $filters,
        ];
        $this->view('mantenimiento/index', $data);
    }

    public function create(): void
    {
        $this->requireRol(3);
        $maquinaModel = new Maquina();
        $data = [
            'maquinas' => $maquinaModel->all(),
            'pageTitle' => 'Registrar Mantenimiento',
        ];
        $this->view('mantenimiento/create', $data);
    }

    public function store(): void
    {
        $this->requireRol(3);
        $maq = (new Maquina())->find($this->postParam('id_maquina'));
        if (!$maq) {
            set_flash('error', 'Máquina no encontrada');
            $this->redirect('/mantenimiento/create');
        }

        $this->mantenimiento->create([
            'id_maquina' => $this->postParam('id_maquina'),
            'fecha_mantenimiento' => $this->postParam('fecha_mantenimiento'),
            'tipo_mantenimiento' => $this->postParam('tipo_mantenimiento'),
            'tecnico_responsable' => $this->postParam('tecnico_responsable'),
            'horas_paro' => $this->postParam('horas_paro') ?: 0,
            'resultado' => $this->postParam('resultado'),
        ]);

        (new Maquina())->update($this->postParam('id_maquina'), ['estatus' => $this->postParam('resultado') === 'completado' ? 'activo' : 'mantenimiento']);

        set_flash('success', 'Mantenimiento registrado correctamente');
        $this->redirect('/mantenimiento');
    }

    public function plan(): void
    {
        $this->requireRol(3);
        $maquinaModel = new Maquina();
        $data = [
            'maquinas' => $maquinaModel->all(),
            'pageTitle' => 'Plan de Mantenimiento',
        ];
        $this->view('mantenimiento/plan_create', $data);
    }

    public function planStore(): void
    {
        $this->requireRol(3);
        $db = \App\Core\Database::getInstance();
        $db->insert('plan_mantenimiento', [
            'id_maquina' => $this->postParam('id_maquina'),
            'fecha_programada' => $this->postParam('fecha_programada'),
            'tipo_mantenimiento' => $this->postParam('tipo_mantenimiento'),
            'descripcion' => $this->postParam('descripcion'),
            'frecuencia_horas' => $this->postParam('frecuencia_horas') ?: null,
            'ultimo_mantenimiento' => $this->postParam('ultimo_mantenimiento') ?: null,
            'tecnico_responsable' => $this->postParam('tecnico_responsable'),
            'estatus' => 'pendiente',
        ]);
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
        $data = [
            'paros' => $this->mantenimiento->fetchAll($sql, $params),
            'pageTitle' => 'Bitácora de Paros',
            'filters' => $filters,
        ];
        $this->view('mantenimiento/paros', $data);
    }

    public function paroCreate(): void
    {
        $this->requireRol(3);
        $maquinaModel = new Maquina();
        $data = [
            'maquinas' => $maquinaModel->all(),
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

        $db = \App\Core\Database::getInstance();
        $db->insert('bitacora_paros', [
            'id_maquina' => $this->postParam('id_maquina'),
            'fecha' => $this->postParam('fecha'),
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin ?: null,
            'duracion_paro' => $duracion,
            'motivo_paro' => $this->postParam('motivo_paro'),
            'operador' => $this->postParam('operador'),
            'estatus' => $horaFin ? 'resuelto' : 'activo',
        ]);
        set_flash('success', 'Paro registrado correctamente');
        $this->redirect('/mantenimiento/paros');
    }

    public function delete($params): void
    {
        $this->requireRol(3);
        $this->mantenimiento->delete($params['id']);
        set_flash('success', 'Registro de mantenimiento eliminado');
        $this->redirect('/mantenimiento');
    }

    public function paroDelete($params): void
    {
        $this->requireRol(3);
        $db = \App\Core\Database::getInstance();
        $db->delete('bitacora_paros', 'id_bitacora = :id', ['id' => $params['id']]);
        set_flash('success', 'Paro eliminado');
        $this->redirect('/mantenimiento/paros');
    }
}

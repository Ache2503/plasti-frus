<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;
use App\Core\Database;
use App\Models\ShiftLog;
use App\Models\Maquina;

class ShiftLogController extends Controller
{
    private Database $db;
    private ShiftLog $shiftLogModel;
    private Maquina $maquinaModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->shiftLogModel = new ShiftLog();
        $this->maquinaModel = new Maquina();
    }

    public function index(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        $userId = (int) $_SESSION['user_id'];
        $hoy = date('Y-m-d');
        $fecha = $this->getParam('fecha') ?: $hoy;
        $turnos = ['matutino', 'vespertino', 'nocturno'];
        $turnoActual = $_SESSION['operador_turno_override'] ?? $turnos[(int) ((int) date('H') / 8)] ?? 'matutino';
        $notas = $this->db->fetchAll("
            SELECT sl.*, m.nombre as maquina_nombre
            FROM shift_logs sl
            LEFT JOIN maquinas m ON sl.maquina_id = m.id_maquina
            WHERE sl.fecha = :fecha
            ORDER BY sl.created_at DESC
        ", ['fecha' => $fecha]);
        $data = [
            'notas' => $notas,
            'maquinas' => $this->maquinaModel->all(),
            'fecha' => $fecha,
            'fecha_hoy' => $hoy,
            'turno_actual' => $turnoActual,
            'pageTitle' => 'Bitácora de Turno - ' . format_date($fecha),
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
        ];
        $this->view('operador.bitacora', $data);
    }

    public function store(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/bitacora');
        }
        $turnos = ['matutino', 'vespertino', 'nocturno'];
        $turnoActual = $_SESSION['operador_turno_override'] ?? $turnos[(int) ((int) date('H') / 8)] ?? 'matutino';
        $this->shiftLogModel->create([
            'operador_id' => (int) $_SESSION['user_id'],
            'maquina_id' => $this->postParam('maquina_id') ?: null,
            'turno' => $turnoActual,
            'fecha' => date('Y-m-d'),
            'nota' => $this->postParam('nota'),
        ]);
        registrar_log('crear_bitacora', 'shift_log', 0, 'Nota de turno agregada');
        set_flash('success', 'Nota registrada en la bitácora');
        $this->redirect('/bitacora');
    }
}

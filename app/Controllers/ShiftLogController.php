<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ShiftLogController extends Controller
{
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        requireAuth();
        requireRolMultiple([1, 2, 3]);
        $userId = (int) $_SESSION['user_id'];
        $hoy = date('Y-m-d');
        $fecha = $_GET['fecha'] ?? $hoy;
        $turnos = ['matutino', 'vespertino', 'nocturno'];
        $turnoActual = $_SESSION['operador_turno_override'] ?? $turnos[(int) ((int) date('H') / 8)] ?? 'matutino';
        $notas = $this->db->fetchAll("
            SELECT sl.*, m.nombre as maquina_nombre
            FROM shift_logs sl
            LEFT JOIN maquinas m ON sl.maquina_id = m.id_maquina
            WHERE sl.fecha = :fecha
            ORDER BY sl.created_at DESC
        ", ['fecha' => $fecha]);
        $maquinas = $this->db->fetchAll("SELECT * FROM maquinas ORDER BY nombre");
        $data = [
            'notas' => $notas,
            'maquinas' => $maquinas,
            'fecha' => $fecha,
            'fecha_hoy' => $hoy,
            'turno_actual' => $turnoActual,
            'pageTitle' => 'Bitácora de Turno - ' . format_date($fecha),
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
        ];
        view('operador/bitacora', $data);
    }

    public function store(): void
    {
        requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            redirect('/bitacora');
        }
        $turnos = ['matutino', 'vespertino', 'nocturno'];
        $turnoActual = $_SESSION['operador_turno_override'] ?? $turnos[(int) ((int) date('H') / 8)] ?? 'matutino';
        $this->db->insert('shift_logs', [
            'operador_id' => (int) $_SESSION['user_id'],
            'maquina_id' => $_POST['maquina_id'] ?: null,
            'turno' => $turnoActual,
            'fecha' => date('Y-m-d'),
            'nota' => $_POST['nota'],
        ]);
        registrar_log('crear_bitacora', 'shift_log', 0, 'Nota de turno agregada');
        set_flash('success', 'Nota registrada en la bitácora');
        redirect('/bitacora');
    }
}

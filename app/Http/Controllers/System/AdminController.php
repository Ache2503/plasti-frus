<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;
use App\Core\Database;

class AdminController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function horariosOperador(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);

        $operadores = $this->db->fetchAll("
            SELECT u.id_usuario, e.id_empleado, e.nombre, e.apellido_paterno,
                   h.id_horario, h.turno, h.hora_inicio, h.hora_fin, h.activo
            FROM empleados e
            INNER JOIN usuarios u ON u.id_empleado = e.id_empleado AND u.id_rol = 2
            LEFT JOIN horarios_operador h ON h.id_empleado = e.id_empleado
            ORDER BY e.nombre
        ");

        $data = [
            'operadores' => $operadores,
            'pageTitle' => 'Horarios de Operadores',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
        ];
        $this->view('admin/horarios_operador', $data);
    }

    public function guardarHorario(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/admin/horarios');
        }

        $idEmpleado = $this->postParam('id_empleado');
        $turno = $this->postParam('turno');
        $horaInicio = $this->postParam('hora_inicio');
        $horaFin = $this->postParam('hora_fin');
        $activo = $this->postParam('activo') ? 1 : 0;

        if (!in_array($turno, ['matutino', 'vespertino', 'nocturno'])) {
            set_flash('error', 'Turno inválido');
            $this->redirect('/admin/horarios');
        }

        $existente = $this->db->fetchOne("SELECT id_horario FROM horarios_operador WHERE id_empleado = :id", ['id' => $idEmpleado]);

        if ($existente) {
            $this->db->update('horarios_operador', [
                'turno' => $turno,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'activo' => $activo,
            ], 'id_horario = :id', ['id' => $existente['id_horario']]);
        } else {
            $this->db->insert('horarios_operador', [
                'id_empleado' => $idEmpleado,
                'turno' => $turno,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'activo' => $activo,
            ]);
        }

        set_flash('success', 'Horario actualizado correctamente');
        $this->redirect('/admin/horarios');
    }

    public function autorizarAcceso(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/admin/horarios');
        }

        $idEmpleado = $this->postParam('id_empleado');
        $horas = (int) ($this->postParam('horas') ?: 1);
        $motivo = $this->postParam('motivo') ?: 'Cobertura';

        $this->db->insert('accesos_extraordinarios', [
            'id_empleado' => $idEmpleado,
            'autorizado_por' => (int) $_SESSION['user_id'],
            'expiracion' => date('Y-m-d H:i:s', strtotime("+{$horas} hours")),
            'motivo' => $motivo,
        ]);

        registrar_log('autorizar_acceso', 'operador', $idEmpleado, "Acceso autorizado por {$horas}h: {$motivo}");
        set_flash('success', "Acceso autorizado por {$horas} hora(s)");
        $this->redirect('/admin/horarios');
    }
}

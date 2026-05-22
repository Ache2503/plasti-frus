<?php
namespace App\Http\Controllers\System;

use App\Core\Controller;
use App\Core\Database;
use App\Models\OrdenCabe;

class IncidenciasController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        requireRolMultiple([1, 2, 3]);
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
            'estatus' => $this->getParam('estatus'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $incidencias = $this->getIncidenciasWithOrden($filters);

        $data = [
            'incidencias' => $incidencias,
            'pageTitle' => 'Incidencias de Producción',
            'filters' => $filters,
        ];
        $this->view('incidencias/index', $data);
    }

    public function create(): void
    {
        requireRolMultiple([1, 2, 3]);
        $ordenModel = new OrdenCabe();
        $data = [
            'ordenes' => $ordenModel->getWithRelations(),
            'pageTitle' => 'Nueva Incidencia',
        ];
        $this->view('incidencias/create', $data);
    }

    public function store(): void
    {
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/incidencias');
        }
        $idIncidencia = $this->db->insert('incidencias_produccion', [
            'id_orden_cabe' => $this->postParam('id_orden_cabe') ?: null,
            'fecha' => $this->postParam('fecha'),
            'descripcion' => $this->postParam('descripcion'),
            'impacto' => $this->postParam('impacto'),
            'acciones_correctivas' => $this->postParam('acciones_correctivas'),
            'estatus' => $this->postParam('estatus') ?: 'abierta',
        ]);

        registrar_log('crear_incidencia', 'incidencia', $idIncidencia, $this->postParam('descripcion'));

        $userId = (int) $_SESSION['user_id'];
        $supervisores = $this->db->fetchAll("SELECT id_usuario FROM usuarios WHERE id_rol = 3 AND activo = 1");
        foreach ($supervisores as $sup) {
            if ((int) $sup['id_usuario'] !== $userId) {
                notificar_supervisor((int) $sup['id_usuario'], 'incidencia', 'Nueva incidencia',
                    "Incidencia #{$idIncidencia}: {$this->postParam('descripcion')}", $idIncidencia);
            }
        }

        set_flash('success', 'Incidencia registrada correctamente');
        $this->redirect('/incidencias');
    }

    public function cerrar($params): void
    {
        requireRolMultiple([1, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/incidencias');
        }
        $this->db->update('incidencias_produccion', [
            'estatus' => 'cerrada',
            'acciones_correctivas' => $this->postParam('acciones_correctivas'),
        ], 'id_incidencia = :id', ['id' => $params['id']]);
        set_flash('success', 'Incidencia cerrada correctamente');
        $this->redirect('/incidencias');
    }

    public function delete(array $params): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/incidencias');
        }
        $this->db->delete('incidencias_produccion', 'id_incidencia = :id', ['id' => $params['id']]);
        set_flash('success', 'Incidencia eliminada correctamente');
        $this->redirect('/incidencias');
    }

    private function getIncidenciasWithOrden(array $filters): array
    {
        $sql = "
            SELECT i.*, oc.id_orden_cabe, p.nombre as producto_nombre
            FROM incidencias_produccion i
            LEFT JOIN ordenes_cabecera oc ON i.id_orden_cabe = oc.id_orden_cabe
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
        ";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "i.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "i.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }
        if (!empty($filters['estatus'])) {
            $where[] = "i.estatus = :estatus";
            $params['estatus'] = $filters['estatus'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY i.fecha DESC';

        return $this->db->fetchAll($sql, $params);
    }
}

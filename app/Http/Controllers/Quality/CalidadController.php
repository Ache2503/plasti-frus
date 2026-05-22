<?php
namespace App\Http\Controllers\Quality;

use App\Core\Controller;
use App\Core\Database;

class CalidadController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function inspecciones(): void
    {
        $this->requireRol(3);
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        $data = [
            'inspecciones' => $this->getInspecciones($filters),
            'pageTitle' => 'Inspecciones de Calidad',
            'filters' => $filters,
        ];
        $this->view('calidad/inspecciones', $data);
    }

    public function inspeccionCreate(): void
    {
        $this->requireRol(3);
        $data = [
            'productos' => $this->getProductos(),
            'ordenes' => $this->getOrdenesPendientes(),
            'pageTitle' => 'Nueva Inspección',
        ];
        $this->view('calidad/inspeccion_create', $data);
    }

    public function inspeccionStore(): void
    {
        $this->requireRol(3);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/calidad/inspecciones');
        }
        $this->db->insert('inspecciones_calidad', [
            'id_inspeccion' => 'INS-' . strtoupper(substr(uniqid(), -5)),
            'id_orden' => $this->postParam('id_orden') ?: null,
            'id_producto' => $this->postParam('id_producto'),
            'fecha_inspeccion' => $this->postParam('fecha_inspeccion'),
            'muestreo_piezas' => $this->postParam('muestreo_piezas') ?: 0,
            'piezas_aprobadas' => $this->postParam('piezas_aprobadas') ?: 0,
            'piezas_rechazadas' => $this->postParam('piezas_rechazadas') ?: 0,
            'inspector' => $this->postParam('inspector'),
            'resultado' => $this->postParam('resultado'),
        ]);
        set_flash('success', 'Inspección registrada correctamente');
        $this->redirect('/calidad/inspecciones');
    }

    public function rechazos(): void
    {
        $this->requireRol(3);
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        $data = [
            'rechazos' => $this->getRechazos($filters),
            'pageTitle' => 'Rechazos de Calidad',
            'filters' => $filters,
        ];
        $this->view('calidad/rechazos', $data);
    }

    public function rechazoCreate(): void
    {
        $this->requireRol(3);
        $data = [
            'productos' => $this->getProductos(),
            'pageTitle' => 'Nuevo Rechazo',
        ];
        $this->view('calidad/rechazo_create', $data);
    }

    public function rechazoStore(): void
    {
        $this->requireRol(3);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/calidad/rechazos');
        }
        $this->db->insert('rechazos_calidad', [
            'id_producto' => $this->postParam('id_producto'),
            'fecha' => $this->postParam('fecha'),
            'cantidad_rechazada' => $this->postParam('cantidad_rechazada') ?: 0,
            'motivo_rechazo' => $this->postParam('motivo_rechazo'),
            'inspector' => $this->postParam('inspector'),
            'estatus' => $this->postParam('estatus'),
        ]);
        set_flash('success', 'Rechazo registrado correctamente');
        $this->redirect('/calidad/rechazos');
    }

    public function inspeccionDelete(array $params): void
    {
        $this->requireRol(3);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/calidad/inspecciones');
        }
        $this->db->delete('inspecciones_calidad', 'id_inspeccion = :id', ['id' => $params['id']]);
        set_flash('success', 'Inspección eliminada');
        $this->redirect('/calidad/inspecciones');
    }

    public function rechazoDelete(array $params): void
    {
        $this->requireRol(3);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/calidad/rechazos');
        }
        $this->db->delete('rechazos_calidad', 'id_rechazo = :id', ['id' => $params['id']]);
        set_flash('success', 'Rechazo eliminado');
        $this->redirect('/calidad/rechazos');
    }

    public function pendientes(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        $userId = (int) $_SESSION['user_id'];
        $pendientes = $this->db->fetchAll("
            SELECT i.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                   o.id_orden_cabe, o.cantidad_planificada, o.turno,
                   m.nombre as maquina_nombre
            FROM inspecciones_calidad i
            LEFT JOIN productos p ON i.id_producto = p.id_producto
            LEFT JOIN ordenes_cabecera o ON i.id_orden = o.id_orden_cabe
            LEFT JOIN maquinas m ON o.id_maquina = m.id_maquina
            WHERE i.resultado IS NULL OR i.resultado = ''
            ORDER BY i.fecha_inspeccion ASC
        ");
        $data = [
            'pendientes' => $pendientes,
            'pageTitle' => 'Inspecciones Pendientes',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
        ];
        $this->view('calidad/pendientes', $data);
    }

    public function realizarInspeccion(array $params): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/calidad/pendientes');
        }
        $id = $params['id'];
        $inspeccion = $this->db->fetchOne("SELECT * FROM inspecciones_calidad WHERE id_inspeccion = :id", ['id' => $id]);
        if (!$inspeccion) {
            set_flash('error', 'Inspección no encontrada');
            $this->redirect('/calidad/pendientes');
        }
        $muestreo = $this->postParam('muestreo_piezas') ?: $inspeccion['muestreo_piezas'];
        $aprobadas = $this->postParam('piezas_aprobadas') ?: 0;
        $rechazadas = $this->postParam('piezas_rechazadas') ?: 0;
        $resultado = $this->postParam('resultado');
        $this->db->update('inspecciones_calidad', [
            'muestreo_piezas' => $muestreo,
            'piezas_aprobadas' => $aprobadas,
            'piezas_rechazadas' => $rechazadas,
            'inspector' => $this->postParam('inspector') ?: user_nombre_completo(),
            'resultado' => $resultado,
        ], 'id_inspeccion = :id', ['id' => $id]);
        if ($resultado === 'no_conforme' && $rechazadas > 0) {
            $this->db->insert('rechazos_calidad', [
                'id_producto' => $inspeccion['id_producto'],
                'fecha' => date('Y-m-d'),
                'cantidad_rechazada' => $rechazadas,
                'motivo_rechazo' => $this->postParam('motivo_rechazo') ?: 'Rechazado en inspección',
                'inspector' => $this->postParam('inspector') ?: user_nombre_completo(),
                'estatus' => 'pendiente',
            ]);
        }
        registrar_log('realizar_inspeccion', 'inspeccion', $id, "Resultado: {$resultado}");
        set_flash('success', 'Inspección registrada correctamente');
        $this->redirect('/calidad/pendientes');
    }

    private function getInspecciones(array $filters = []): array
    {
        $sql = "SELECT i.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                       o.id_orden_cabe, o.cantidad_planificada
                FROM inspecciones_calidad i
                LEFT JOIN productos p ON i.id_producto = p.id_producto
                LEFT JOIN ordenes_cabecera o ON i.id_orden = o.id_orden_cabe";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "i.fecha_inspeccion >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "i.fecha_inspeccion <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY i.fecha_inspeccion DESC';

        return $this->db->fetchAll($sql, $params);
    }

    private function getRechazos(array $filters = []): array
    {
        $sql = "SELECT r.*, p.nombre as producto_nombre, p.codigo as producto_codigo
                FROM rechazos_calidad r
                LEFT JOIN productos p ON r.id_producto = p.id_producto";
        $params = [];
        $where = [];

        if (!empty($filters['fecha_desde'])) {
            $where[] = "r.fecha >= :fecha_desde";
            $params['fecha_desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "r.fecha <= :fecha_hasta";
            $params['fecha_hasta'] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY r.fecha DESC';

        return $this->db->fetchAll($sql, $params);
    }

    private function getProductos(): array
    {
        return $this->db->fetchAll("SELECT * FROM productos ORDER BY id_producto DESC");
    }

    private function getOrdenesPendientes(): array
    {
        return $this->db->fetchAll("
            SELECT oc.*, p.nombre as producto_nombre
            FROM ordenes_cabecera oc
            LEFT JOIN productos p ON oc.id_producto = p.id_producto
            WHERE oc.cantidad_real_buenas IS NULL OR oc.cantidad_real_buenas = 0
            ORDER BY oc.fecha DESC
        ");
    }
}

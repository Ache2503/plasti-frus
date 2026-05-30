<?php
namespace App\Http\Controllers\Quality;

use App\Core\Controller;
use App\Core\Database;
use App\Repositories\CatalogRepository;

class CalidadController extends Controller
{
    private Database $db;
    private CatalogRepository $catalogs;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->catalogs = new CatalogRepository();
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
            'inspectores' => $this->getUsuariosOperativos([1, 3]),
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
        $inspector = $this->findUsuario((int) $this->postParam('id_inspector'));
        if (!$inspector) {
            set_flash('error', 'Inspector no válido');
            $this->redirect('/calidad/inspecciones/create');
        }
        if (!$this->findProducto((int) $this->postParam('id_producto'))) {
            set_flash('error', 'Producto no válido');
            $this->redirect('/calidad/inspecciones/create');
        }
        if ($this->postParam('id_orden') && !$this->findOrden((int) $this->postParam('id_orden'))) {
            set_flash('error', 'Orden no válida');
            $this->redirect('/calidad/inspecciones/create');
        }
        $this->db->insert('inspecciones_calidad', [
            'id_inspeccion' => 'INS-' . strtoupper(substr(uniqid(), -5)),
            'id_orden' => $this->postParam('id_orden') ?: null,
            'id_producto' => $this->postParam('id_producto'),
            'fecha_inspeccion' => $this->postParam('fecha_inspeccion'),
            'muestreo_piezas' => $this->postParam('muestreo_piezas') ?: 0,
            'piezas_aprobadas' => $this->postParam('piezas_aprobadas') ?: 0,
            'piezas_rechazadas' => $this->postParam('piezas_rechazadas') ?: 0,
            'inspector' => $inspector['nombre_completo'],
            'id_inspector' => $inspector['id_usuario'],
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
            'inspectores' => $this->getUsuariosOperativos([1, 3]),
            'motivos_rechazo' => $this->catalogs->motivosRechazo(),
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
        $inspector = $this->findUsuario((int) $this->postParam('id_inspector'));
        if (!$inspector) {
            set_flash('error', 'Inspector no válido');
            $this->redirect('/calidad/rechazos/create');
        }
        $motivo = $this->catalogs->findMotivoRechazo((int) $this->postParam('id_motivo_rechazo'));
        if (!$motivo) {
            set_flash('error', 'Motivo de rechazo no válido');
            $this->redirect('/calidad/rechazos/create');
        }
        if (!$this->findProducto((int) $this->postParam('id_producto'))) {
            set_flash('error', 'Producto no válido');
            $this->redirect('/calidad/rechazos/create');
        }
        $this->db->insert('rechazos_calidad', [
            'id_producto' => $this->postParam('id_producto'),
            'fecha' => $this->postParam('fecha'),
            'cantidad_rechazada' => $this->postParam('cantidad_rechazada') ?: 0,
            'motivo_rechazo' => $motivo['nombre'],
            'id_motivo_rechazo' => $motivo['id_motivo_rechazo'],
            'inspector' => $inspector['nombre_completo'],
            'id_inspector' => $inspector['id_usuario'],
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
                   m.nombre as maquina_nombre,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, i.inspector) as inspector_nombre
            FROM inspecciones_calidad i
            LEFT JOIN productos p ON i.id_producto = p.id_producto
            LEFT JOIN ordenes_cabecera o ON i.id_orden = o.id_orden_cabe
            LEFT JOIN maquinas m ON o.id_maquina = m.id_maquina
            LEFT JOIN usuarios u ON i.id_inspector = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE i.resultado IS NULL OR i.resultado = ''
            ORDER BY i.fecha_inspeccion ASC
        ");
        $data = [
            'pendientes' => $pendientes,
            'pageTitle' => 'Inspecciones Pendientes',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'motivos_rechazo' => $this->catalogs->motivosRechazo(),
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
        $inspector = $this->findUsuario((int) ($_SESSION['user_id'] ?? 0));
        $updateData = [
            'muestreo_piezas' => $muestreo,
            'piezas_aprobadas' => $aprobadas,
            'piezas_rechazadas' => $rechazadas,
            'inspector' => $inspector['nombre_completo'] ?? user_nombre_completo(),
            'resultado' => $resultado,
        ];
        if ($this->db->columnExists('inspecciones_calidad', 'id_inspector')) {
            $updateData['id_inspector'] = $inspector['id_usuario'] ?? null;
        }
        $this->db->update('inspecciones_calidad', $updateData, 'id_inspeccion = :id', ['id' => $id]);
        if ($resultado === 'no_conforme' && $rechazadas > 0) {
            $motivo = $this->catalogs->findMotivoRechazo((int) $this->postParam('id_motivo_rechazo'))
                ?? $this->findMotivoRechazoFromText('Rechazado en inspección');
            $rechazoData = [
                'id_producto' => $inspeccion['id_producto'],
                'fecha' => date('Y-m-d'),
                'cantidad_rechazada' => $rechazadas,
                'motivo_rechazo' => $motivo['nombre'],
                'inspector' => $inspector['nombre_completo'] ?? user_nombre_completo(),
                'estatus' => 'pendiente',
            ];
            if ($this->db->columnExists('rechazos_calidad', 'id_inspector')) {
                $rechazoData['id_inspector'] = $inspector['id_usuario'] ?? null;
            }
            if ($this->db->columnExists('rechazos_calidad', 'id_motivo_rechazo')) {
                $rechazoData['id_motivo_rechazo'] = $motivo['id_motivo_rechazo'];
            }
            $this->db->insert('rechazos_calidad', $rechazoData);
        }
        registrar_log('realizar_inspeccion', 'inspeccion', $id, "Resultado: {$resultado}");
        set_flash('success', 'Inspección registrada correctamente');
        $this->redirect('/calidad/pendientes');
    }

    private function getInspecciones(array $filters = []): array
    {
        $sql = "SELECT i.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                       o.id_orden_cabe, o.cantidad_planificada,
                       COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, i.inspector) as inspector_nombre
                FROM inspecciones_calidad i
                LEFT JOIN productos p ON i.id_producto = p.id_producto
                LEFT JOIN ordenes_cabecera o ON i.id_orden = o.id_orden_cabe
                LEFT JOIN usuarios u ON i.id_inspector = u.id_usuario
                LEFT JOIN empleados e ON u.id_empleado = e.id_empleado";
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
        $sql = "SELECT r.*, p.nombre as producto_nombre, p.codigo as producto_codigo,
                       COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario, r.inspector) as inspector_nombre,
                       COALESCE(mr.nombre, r.motivo_rechazo) as motivo_rechazo_nombre
                FROM rechazos_calidad r
                LEFT JOIN productos p ON r.id_producto = p.id_producto
                LEFT JOIN usuarios u ON r.id_inspector = u.id_usuario
                LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
                LEFT JOIN motivos_rechazo mr ON r.id_motivo_rechazo = mr.id_motivo_rechazo";
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

    private function findProducto(int $id): ?array
    {
        return $this->db->fetchOne("SELECT id_producto FROM productos WHERE id_producto = :id", ['id' => $id]) ?: null;
    }

    private function findOrden(int $id): ?array
    {
        return $this->db->fetchOne("SELECT id_orden_cabe FROM ordenes_cabecera WHERE id_orden_cabe = :id", ['id' => $id]) ?: null;
    }

    private function findMotivoRechazoFromText(string $texto): array
    {
        $slug = strtolower(trim(str_replace([' ', 'ó'], ['_', 'o'], $texto)));
        $motivos = $this->catalogs->motivosRechazo();
        foreach ($motivos as $motivo) {
            if ($motivo['slug'] === $slug) {
                return $motivo;
            }
        }

        foreach ($motivos as $motivo) {
            if ($motivo['slug'] === 'rechazado_en_inspeccion' || $motivo['slug'] === 'otro') {
                return $motivo;
            }
        }

        return ['id_motivo_rechazo' => null, 'nombre' => $texto];
    }
}

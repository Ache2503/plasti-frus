<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\InspeccionCalidad;
use App\Models\RechazoCalidad;
use App\Models\Producto;
use App\Models\OrdenCabe;

class CalidadController extends Controller
{
    private $inspeccion;

    public function __construct()
    {
        $this->inspeccion = new InspeccionCalidad();
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
            'inspecciones' => $this->inspeccion->getWithRelations($filters),
            'pageTitle' => 'Inspecciones de Calidad',
            'filters' => $filters,
        ];
        $this->view('calidad/inspecciones', $data);
    }

    public function inspeccionCreate(): void
    {
        $this->requireRol(3);
        $productoModel = new Producto();
        $ordenModel = new OrdenCabe();
        $data = [
            'productos' => $productoModel->all(),
            'ordenes' => $ordenModel->getPending(),
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
        $this->inspeccion->create([
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
        $rechazoModel = new RechazoCalidad();
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        $data = [
            'rechazos' => $rechazoModel->getWithProducto($filters),
            'pageTitle' => 'Rechazos de Calidad',
            'filters' => $filters,
        ];
        $this->view('calidad/rechazos', $data);
    }

    public function rechazoCreate(): void
    {
        $this->requireRol(3);
        $productoModel = new Producto();
        $data = [
            'productos' => $productoModel->all(),
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
        $rechazoModel = new RechazoCalidad();
        $rechazoModel->create([
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

    public function inspeccionDelete($params): void
    {
        $this->requireRol(3);
        $this->inspeccion->delete($params['id']);
        set_flash('success', 'Inspección eliminada');
        $this->redirect('/calidad/inspecciones');
    }

    public function rechazoDelete($params): void
    {
        $this->requireRol(3);
        $rechazoModel = new RechazoCalidad();
        $rechazoModel->delete($params['id']);
        set_flash('success', 'Rechazo eliminado');
        $this->redirect('/calidad/rechazos');
    }

    public function pendientes(): void
    {
        requireAuth();
        requireRolMultiple([1, 2, 3]);
        $db = \App\Core\Database::getInstance();
        $pendientes = $db->fetchAll("
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
        view('calidad/pendientes', $data);
    }

    public function realizarInspeccion(array $params): void
    {
        requireAuth();
        requireRolMultiple([1, 2, 3]);
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            set_flash('error', 'Token de seguridad inválido');
            redirect('/calidad/pendientes');
        }
        $db = \App\Core\Database::getInstance();
        $id = $params['id'];
        $inspeccion = $db->fetchOne("SELECT * FROM inspecciones_calidad WHERE id_inspeccion = :id", ['id' => $id]);
        if (!$inspeccion) {
            set_flash('error', 'Inspección no encontrada');
            redirect('/calidad/pendientes');
        }
        $muestreo = $_POST['muestreo_piezas'] ?? $inspeccion['muestreo_piezas'];
        $aprobadas = $_POST['piezas_aprobadas'] ?? 0;
        $rechazadas = $_POST['piezas_rechazadas'] ?? 0;
        $resultado = $_POST['resultado'];
        $db->update('inspecciones_calidad', [
            'muestreo_piezas' => $muestreo,
            'piezas_aprobadas' => $aprobadas,
            'piezas_rechazadas' => $rechazadas,
            'inspector' => $_POST['inspector'] ?? user_nombre_completo(),
            'resultado' => $resultado,
        ], 'id_inspeccion = :id', ['id' => $id]);
        if ($resultado === 'no_conforme' && $rechazadas > 0) {
            $db->insert('rechazos_calidad', [
                'id_producto' => $inspeccion['id_producto'],
                'fecha' => date('Y-m-d'),
                'cantidad_rechazada' => $rechazadas,
                'motivo_rechazo' => $_POST['motivo_rechazo'] ?? 'Rechazado en inspección',
                'inspector' => user_nombre_completo(),
                'estatus' => 'pendiente',
            ]);
        }
        registrar_log('realizar_inspeccion', 'inspeccion', $id, "Resultado: {$resultado}");
        set_flash('success', 'Inspección registrada correctamente');
        redirect('/calidad/pendientes');
    }
}

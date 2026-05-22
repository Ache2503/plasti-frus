<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\IncidenciaProduccion;
use App\Models\OrdenCabe;

class IncidenciasController extends Controller
{
    private $incidencia;

    public function __construct()
    {
        $this->incidencia = new IncidenciaProduccion();
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
        $data = [
            'incidencias' => $this->incidencia->getWithOrden($filters),
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
        $idIncidencia = $this->incidencia->create([
            'id_orden_cabe' => $this->postParam('id_orden_cabe') ?: null,
            'fecha' => $this->postParam('fecha'),
            'descripcion' => $this->postParam('descripcion'),
            'impacto' => $this->postParam('impacto'),
            'acciones_correctivas' => $this->postParam('acciones_correctivas'),
            'estatus' => $this->postParam('estatus') ?: 'abierta',
        ]);

        registrar_log('crear_incidencia', 'incidencia', $idIncidencia, $this->postParam('descripcion'));

        $userId = (int) $_SESSION['user_id'];
        $supervisores = $db->fetchAll("SELECT id_usuario FROM usuarios WHERE id_rol = 3 AND activo = 1");
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
        $this->incidencia->update($params['id'], [
            'estatus' => 'cerrada',
            'acciones_correctivas' => $this->postParam('acciones_correctivas'),
        ]);
        set_flash('success', 'Incidencia cerrada correctamente');
        $this->redirect('/incidencias');
    }

    public function delete($params): void
    {
        requireRolMultiple([1, 3]);
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/incidencias');
        }
        $this->incidencia->delete($params['id']);
        set_flash('success', 'Incidencia eliminada correctamente');
        $this->redirect('/incidencias');
    }
}

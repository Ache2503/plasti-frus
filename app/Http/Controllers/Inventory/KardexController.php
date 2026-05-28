<?php
namespace App\Http\Controllers\Inventory;

use App\Core\Controller;
use App\Core\Database;
use App\Services\KardexService;
use App\Repositories\MaterialRepository;

class KardexController extends Controller
{
    private KardexService $kardexService;
    private MaterialRepository $materialRepository;
    private Database $db;

    public function __construct()
    {
        $this->kardexService = new KardexService();
        $this->materialRepository = new MaterialRepository();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->requireRol(3);
        $filters = [
            'fecha_desde' => $this->getParam('fecha_desde'),
            'fecha_hasta' => $this->getParam('fecha_hasta'),
            'id_material' => $this->getParam('id_material'),
            'movimiento' => $this->getParam('movimiento'),
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        $data = [
            'movimientos' => $this->kardexService->getAll(),
            'materiales' => $this->materialRepository->all(),
            'pageTitle' => 'Kardex de Materiales',
            'filters' => $filters,
        ];
        $this->view('kardex/index', $data);
    }

    public function create(): void
    {
        $this->requireRol(3);
        $data = [
            'materiales' => $this->materialRepository->all(),
            'operadores' => $this->getOperadores(),
            'pageTitle' => 'Agregar Movimiento',
        ];
        $this->view('kardex/create', $data);
    }

    public function store(): void
    {
        $this->requireRol(3);
        $idMaterial = $this->postParam('id_material');

        $mat = $this->materialRepository->find($idMaterial);
        if (!$mat) {
            set_flash('error', 'Material no encontrado');
            $this->redirect('/kardex/create');
        }
        $operador = $this->findUsuario((int) $this->postParam('id_operador'));
        if (!$operador) {
            set_flash('error', 'Operador no válido');
            $this->redirect('/kardex/create');
        }

        $data = [
            'id_material' => $idMaterial,
            'fecha' => $this->postParam('fecha'),
            'movimiento' => $this->postParam('movimiento'),
            'cantidad' => (float) ($this->postParam('cantidad') ?: 0),
            'operador' => $operador['nombre_completo'],
        ];
        if ($this->db->columnExists('kardex_materiales', 'id_operador')) {
            $data['id_operador'] = $operador['id_usuario'];
        }
        $this->kardexService->create($data);

        set_flash('success', 'Movimiento registrado correctamente');
        $this->redirect('/kardex');
    }

    public function detalle(array $params): void
    {
        $this->requireRol(3);
        $id = (int) ($params['id'] ?? 0);
        $material = $this->materialRepository->find($id);
        if (!$material) {
            set_flash('error', 'Material no encontrado');
            $this->redirect('/kardex');
        }
        $data = [
            'material' => $material,
            'movimientos' => $this->kardexService->getByMaterial($id),
            'pageTitle' => 'Kardex: ' . $material['nombre'],
        ];
        $this->view('kardex/detalle', $data);
    }

    private function getOperadores(): array
    {
        return $this->db->fetchAll("
            SELECT u.id_usuario,
                   COALESCE(NULLIF(TRIM(CONCAT(e.nombre, ' ', e.apellido_paterno)), ''), u.nombre_usuario) as nombre_completo
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE u.activo = 1 AND u.id_rol = 2
            ORDER BY nombre_completo
        ");
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
}

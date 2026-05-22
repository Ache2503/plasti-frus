<?php
namespace App\Http\Controllers\Accounting;

use App\Core\Controller;
use App\Core\Database;

class PlanCuentasController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $buscar = $this->getParam('buscar');
        $tipo_filtro = $this->getParam('tipo');

        $data = [
            'pageTitle' => 'Plan de Cuentas',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuentas' => $this->buscarCuentas($buscar, $tipo_filtro),
            'filtro_buscar' => $buscar,
            'filtro_tipo' => $tipo_filtro,
        ];
        $this->view('plan_cuentas/index', $data);
    }

    public function create(): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('crear')) {
            set_flash('error', 'No tienes permisos para crear cuentas');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $data = [
            'pageTitle' => 'Nueva Cuenta',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuentas_padre' => $this->getCuentasActivas(),
        ];
        $this->view('plan_cuentas/create', $data);
    }

    public function store(): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('crear')) {
            set_flash('error', 'No tienes permisos para crear cuentas');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $codigo = trim($this->postParam('codigo'));
        $nombre = trim($this->postParam('nombre'));
        if (empty($codigo) || empty($nombre)) {
            set_flash('error', 'Código y nombre son obligatorios');
            $this->redirect('/contabilidad/plan-cuentas/create');
        }
        if ($this->existeCodigo($codigo)) {
            set_flash('error', "El código '{$codigo}' ya existe");
            $this->redirect('/contabilidad/plan-cuentas/create');
        }
        $nivelInput = (int) $this->postParam('nivel');
        $idPadre = $this->postParam('id_padre') ?: null;
        if ($idPadre) {
            $nivelPadre = $this->getNivelCuenta($idPadre);
            if ($nivelPadre !== null) {
                $nivelInput = $nivelPadre + 1;
            }
        }
        $this->db->insert('plan_cuentas', [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'tipo' => $this->postParam('tipo'),
            'nivel' => $nivelInput,
            'id_padre' => $idPadre,
            'naturaleza' => $this->postParam('naturaleza'),
        ]);
        registrar_log('crear_cuenta', 'plan_cuentas', null, "Cuenta {$codigo}: {$nombre}");
        set_flash('success', "Cuenta {$codigo} creada correctamente");
        $this->redirect('/contabilidad/plan-cuentas');
    }

    public function edit(array $params): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('editar')) {
            set_flash('error', 'No tienes permisos para editar cuentas');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $cuenta = $this->getCuentaById($params['id']);
        if (!$cuenta) {
            set_flash('error', 'Cuenta no encontrada');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $data = [
            'pageTitle' => 'Editar Cuenta',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuenta' => $cuenta,
            'cuentas_padre' => $this->getCuentasActivasExcepto($params['id']),
        ];
        $this->view('plan_cuentas/edit', $data);
    }

    public function update(array $params): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('editar')) {
            set_flash('error', 'No tienes permisos para editar cuentas');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $codigo = trim($this->postParam('codigo'));
        $nombre = trim($this->postParam('nombre'));
        if (empty($codigo) || empty($nombre)) {
            set_flash('error', 'Código y nombre son obligatorios');
            $this->redirect('/contabilidad/plan-cuentas/edit/' . $params['id']);
        }
        if ($this->existeCodigoExcepto($codigo, $params['id'])) {
            set_flash('error', "El código '{$codigo}' ya está en uso");
            $this->redirect('/contabilidad/plan-cuentas/edit/' . $params['id']);
        }
        $nivelInput = (int) $this->postParam('nivel');
        $idPadre = $this->postParam('id_padre') ?: null;
        if ($idPadre) {
            $nivelPadre = $this->getNivelCuenta($idPadre);
            if ($nivelPadre !== null) {
                $nivelInput = $nivelPadre + 1;
            }
        }
        $this->db->update('plan_cuentas', [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'tipo' => $this->postParam('tipo'),
            'nivel' => $nivelInput,
            'id_padre' => $idPadre,
            'naturaleza' => $this->postParam('naturaleza'),
            'activo' => $this->postParam('activo') ? 1 : 0,
        ], 'id_cuenta = :id', ['id' => $params['id']]);
        registrar_log('editar_cuenta', 'plan_cuentas', $params['id'], "Cuenta: {$codigo}");
        set_flash('success', 'Cuenta actualizada correctamente');
        $this->redirect('/contabilidad/plan-cuentas');
    }

    public function delete(array $params): void
    {
        $this->checkAccess();
        if (!contabilidad_permiso('eliminar')) {
            set_flash('error', 'No tienes permisos para eliminar cuentas');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        if (!verify_csrf($this->postParam('csrf_token'))) {
            set_flash('error', 'Token de seguridad inválido');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        if ($this->cuentaTieneHijos($params['id'])) {
            set_flash('error', 'No se puede eliminar: tiene cuentas hijas');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $movimientos = $this->cuentaTieneMovimientos($params['id']);
        if ($movimientos > 0) {
            set_flash('error', "No se puede eliminar: tiene {$movimientos} movimiento(s) contable(s). Desactívela en su lugar.");
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $this->db->delete('plan_cuentas', 'id_cuenta = :id', ['id' => $params['id']]);
        registrar_log('eliminar_cuenta', 'plan_cuentas', $params['id'], 'Cuenta eliminada');
        set_flash('success', 'Cuenta eliminada correctamente');
        $this->redirect('/contabilidad/plan-cuentas');
    }

    private function buscarCuentas(?string $buscar, ?string $tipo): array
    {
        $sql = "SELECT c.*, p.nombre as padre_nombre FROM plan_cuentas c
                LEFT JOIN plan_cuentas p ON c.id_padre = p.id_cuenta";
        $params = [];
        $where = [];

        if (!empty($buscar)) {
            $where[] = "(c.codigo LIKE :buscar OR c.nombre LIKE :buscar2)";
            $params['buscar'] = "%{$buscar}%";
            $params['buscar2'] = "%{$buscar}%";
        }
        if (!empty($tipo)) {
            $where[] = "c.tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY c.codigo';

        return $this->db->fetchAll($sql, $params);
    }

    private function getCuentasActivas(): array
    {
        return $this->db->fetchAll(
            "SELECT id_cuenta, codigo, nombre, nivel, tipo
             FROM plan_cuentas WHERE activo = 1 ORDER BY codigo"
        );
    }

    private function getCuentasActivasExcepto(string $exceptId): array
    {
        return $this->db->fetchAll(
            "SELECT id_cuenta, codigo, nombre, nivel, tipo
             FROM plan_cuentas WHERE activo = 1 AND id_cuenta != :id ORDER BY codigo",
            ['id' => $exceptId]
        );
    }

    private function getCuentaById(string $id): ?array
    {
        $cuenta = $this->db->fetchOne(
            "SELECT * FROM plan_cuentas WHERE id_cuenta = :id",
            ['id' => $id]
        );
        return $cuenta ?: null;
    }

    private function existeCodigo(string $codigo): bool
    {
        return (bool) $this->db->fetchOne(
            "SELECT id_cuenta FROM plan_cuentas WHERE codigo = :c",
            ['c' => $codigo]
        );
    }

    private function existeCodigoExcepto(string $codigo, string $exceptId): bool
    {
        return (bool) $this->db->fetchOne(
            "SELECT id_cuenta FROM plan_cuentas WHERE codigo = :c AND id_cuenta != :id",
            ['c' => $codigo, 'id' => $exceptId]
        );
    }

    private function getNivelCuenta(string $id): ?int
    {
        $padre = $this->db->fetchOne(
            "SELECT nivel FROM plan_cuentas WHERE id_cuenta = :id",
            ['id' => $id]
        );
        return $padre ? (int) $padre['nivel'] : null;
    }

    private function cuentaTieneHijos(string $id): bool
    {
        $hijos = $this->db->fetchOne(
            "SELECT COUNT(*) as t FROM plan_cuentas WHERE id_padre = :id",
            ['id' => $id]
        );
        return ($hijos['t'] ?? 0) > 0;
    }

    private function cuentaTieneMovimientos(string $id): int
    {
        $movimientos = $this->db->fetchOne(
            "SELECT COUNT(*) as t FROM polizas_detalle WHERE id_cuenta = :id",
            ['id' => $id]
        );
        return (int) ($movimientos['t'] ?? 0);
    }
}

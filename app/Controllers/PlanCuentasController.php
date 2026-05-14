<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class PlanCuentasController extends Controller
{
    private function checkAccess(): void
    {
        $this->requireAuth();
        requireRolMultiple([1, 3, 6]);
    }

    public function index(): void
    {
        $this->checkAccess();
        $db = Database::getInstance();
        $buscar = $this->getParam('buscar');
        $tipo_filtro = $this->getParam('tipo');

        $sql = "SELECT c.*, p.nombre as padre_nombre FROM plan_cuentas c
                LEFT JOIN plan_cuentas p ON c.id_padre = p.id_cuenta";
        $params = [];
        $where = [];

        if (!empty($buscar)) {
            $where[] = "(c.codigo LIKE :buscar OR c.nombre LIKE :buscar2)";
            $params['buscar'] = "%{$buscar}%";
            $params['buscar2'] = "%{$buscar}%";
        }
        if (!empty($tipo_filtro)) {
            $where[] = "c.tipo = :tipo";
            $params['tipo'] = $tipo_filtro;
        }
        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY c.codigo';

        $cuentas = $db->fetchAll($sql, $params);

        $data = [
            'pageTitle' => 'Plan de Cuentas',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuentas' => $cuentas,
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
        $db = Database::getInstance();
        $cuentas_padre = $db->fetchAll("
            SELECT id_cuenta, codigo, nombre, nivel, tipo
            FROM plan_cuentas WHERE activo = 1 ORDER BY codigo
        ");
        $data = [
            'pageTitle' => 'Nueva Cuenta',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuentas_padre' => $cuentas_padre,
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
        $db = Database::getInstance();
        $existe = $db->fetchOne("SELECT id_cuenta FROM plan_cuentas WHERE codigo = :c", ['c' => $codigo]);
        if ($existe) {
            set_flash('error', "El código '{$codigo}' ya existe");
            $this->redirect('/contabilidad/plan-cuentas/create');
        }
        $nivelInput = (int) $this->postParam('nivel');
        $idPadre = $this->postParam('id_padre') ?: null;
        if ($idPadre) {
            $padre = $db->fetchOne("SELECT nivel FROM plan_cuentas WHERE id_cuenta = :id", ['id' => $idPadre]);
            if ($padre) {
                $nivelInput = $padre['nivel'] + 1;
            }
        }
        $db->insert('plan_cuentas', [
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
        $db = Database::getInstance();
        $cuenta = $db->fetchOne("SELECT * FROM plan_cuentas WHERE id_cuenta = :id", ['id' => $params['id']]);
        if (!$cuenta) {
            set_flash('error', 'Cuenta no encontrada');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $cuentas_padre = $db->fetchAll("
            SELECT id_cuenta, codigo, nombre, nivel, tipo
            FROM plan_cuentas WHERE activo = 1 AND id_cuenta != :id ORDER BY codigo
        ", ['id' => $params['id']]);
        $data = [
            'pageTitle' => 'Editar Cuenta',
            'rol' => user_rol(),
            'rol_nombre' => user_rol_nombre(),
            'cuenta' => $cuenta,
            'cuentas_padre' => $cuentas_padre,
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
        $db = Database::getInstance();
        $existe = $db->fetchOne("SELECT id_cuenta FROM plan_cuentas WHERE codigo = :c AND id_cuenta != :id", ['c' => $codigo, 'id' => $params['id']]);
        if ($existe) {
            set_flash('error', "El código '{$codigo}' ya está en uso");
            $this->redirect('/contabilidad/plan-cuentas/edit/' . $params['id']);
        }
        $nivelInput = (int) $this->postParam('nivel');
        $idPadre = $this->postParam('id_padre') ?: null;
        if ($idPadre) {
            $padre = $db->fetchOne("SELECT nivel FROM plan_cuentas WHERE id_cuenta = :id", ['id' => $idPadre]);
            if ($padre) {
                $nivelInput = $padre['nivel'] + 1;
            }
        }
        $db->update('plan_cuentas', [
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
        $db = Database::getInstance();
        $hijos = $db->fetchOne("SELECT COUNT(*) as t FROM plan_cuentas WHERE id_padre = :id", ['id' => $params['id']])['t'] ?? 0;
        if ($hijos > 0) {
            set_flash('error', 'No se puede eliminar: tiene cuentas hijas');
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $movimientos = $db->fetchOne("SELECT COUNT(*) as t FROM polizas_detalle WHERE id_cuenta = :id", ['id' => $params['id']])['t'] ?? 0;
        if ($movimientos > 0) {
            set_flash('error', "No se puede eliminar: tiene {$movimientos} movimiento(s) contable(s). Desactívela en su lugar.");
            $this->redirect('/contabilidad/plan-cuentas');
        }
        $db->delete('plan_cuentas', 'id_cuenta = :id', ['id' => $params['id']]);
        registrar_log('eliminar_cuenta', 'plan_cuentas', $params['id'], 'Cuenta eliminada');
        set_flash('success', 'Cuenta eliminada correctamente');
        $this->redirect('/contabilidad/plan-cuentas');
    }
}

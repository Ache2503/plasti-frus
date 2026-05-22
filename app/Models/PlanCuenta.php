<?php
namespace App\Models;

use App\Core\Model;

class PlanCuenta extends Model
{
    protected $table = 'plan_cuentas';
    protected $primaryKey = 'id_cuenta';

    public function getTree(): array
    {
        $all = $this->fetchAll("SELECT * FROM plan_cuentas WHERE activo = 1 ORDER BY codigo");
        return $this->buildTree($all);
    }

    public function buildTree(array $elements, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['id_padre'] === $parentId) {
                $children = $this->buildTree($elements, $element['id_cuenta']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    public function getWithSaldo(?string $fecha = null): array
    {
        $fecha = $fecha ?: date('Y-m-d');
        return $this->fetchAll("
            SELECT c.*,
                   COALESCE(SUM(CASE WHEN p.estatus = 'activo' AND p.fecha <= :fecha THEN
                       CASE WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono
                       ELSE pd.abono - pd.cargo END
                   ELSE 0 END), 0) as saldo
            FROM plan_cuentas c
            LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            LEFT JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
            WHERE c.activo = 1
            GROUP BY c.id_cuenta
            ORDER BY c.codigo
        ", ['fecha' => $fecha]);
    }

    public function getById(int $id): ?array
    {
        $r = $this->fetchOne("SELECT * FROM plan_cuentas WHERE id_cuenta = :id AND activo = 1", ['id' => $id]);
        return $r ?: null;
    }

    public function getForPoliza(): array
    {
        return $this->fetchAll(
            "SELECT id_cuenta, codigo, nombre, nivel, naturaleza, tipo
             FROM plan_cuentas WHERE activo = 1 ORDER BY codigo"
        );
    }

    public function hasChildren(int $id): bool
    {
        return (bool) $this->fetchOne(
            "SELECT id_cuenta FROM plan_cuentas WHERE id_padre = :id AND activo = 1 LIMIT 1",
            ['id' => $id]
        );
    }

    public function hasMovimientos(int $id): bool
    {
        return (bool) $this->fetchOne(
            "SELECT pd.id_detalle FROM polizas_detalle pd
             JOIN polizas p ON pd.id_poliza = p.id_poliza AND p.estatus = 'activo'
             WHERE pd.id_cuenta = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public function deleteSafe(int $id): bool
    {
        if ($this->hasChildren($id) || $this->hasMovimientos($id)) {
            $this->db->update($this->table, ['activo' => 0], 'id_cuenta = :id', ['id' => $id]);
            return false;
        }
        $this->db->delete($this->table, 'id_cuenta = :id', ['id' => $id]);
        return true;
    }
}

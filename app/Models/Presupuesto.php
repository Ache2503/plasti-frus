<?php
namespace App\Models;

use App\Core\Model;

class Presupuesto extends Model
{
    protected $table = 'presupuestos';
    protected $primaryKey = 'id';

    public function getByPeriodo(int $anio, ?int $mes = null): array
    {
        $where = "WHERE p.anio = :anio";
        $params = ['anio' => $anio];
        if ($mes !== null) {
            $where .= " AND p.mes = :mes";
            $params['mes'] = $mes;
        }
        return $this->fetchAll("
            SELECT p.*, c.codigo, c.nombre, c.tipo
            FROM presupuestos p
            JOIN plan_cuentas c ON p.cuenta_id = c.id_cuenta
            {$where}
            ORDER BY c.codigo, p.mes
        ", $params);
    }

    public function getAnualResumen(int $anio): array
    {
        return $this->fetchAll("
            SELECT p.cuenta_id, c.codigo, c.nombre, c.tipo,
                   SUM(p.monto) as presupuesto_anual,
                   COALESCE(SUM(CASE
                       WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono
                       ELSE pd.abono - pd.cargo
                   END), 0) as real_anual
            FROM presupuestos p
            JOIN plan_cuentas c ON p.cuenta_id = c.id_cuenta
            LEFT JOIN polizas_detalle pd ON p.cuenta_id = pd.id_cuenta
            LEFT JOIN polizas po ON pd.id_poliza = po.id_poliza
                AND po.estatus = 'activo'
                AND YEAR(po.fecha) = :anio
            WHERE p.anio = :anio2
            GROUP BY p.cuenta_id
            ORDER BY c.codigo
        ", ['anio' => $anio, 'anio2' => $anio]);
    }

    public function getComparacionMensual(int $anio, int $mes): array
    {
        return $this->fetchAll("
            SELECT c.id_cuenta, c.codigo, c.nombre, c.tipo, c.naturaleza,
                   COALESCE(p.monto, 0) as presupuesto,
                   COALESCE(SUM(CASE
                       WHEN c.naturaleza = 'deudora' THEN pd.cargo - pd.abono
                       ELSE pd.abono - pd.cargo
                   END), 0) as real_mes
            FROM plan_cuentas c
            LEFT JOIN presupuestos p ON c.id_cuenta = p.cuenta_id AND p.anio = :anio AND p.mes = :mes
            LEFT JOIN polizas_detalle pd ON c.id_cuenta = pd.id_cuenta
            LEFT JOIN polizas po ON pd.id_poliza = po.id_poliza
                AND po.estatus = 'activo'
                AND MONTH(po.fecha) = :mes2 AND YEAR(po.fecha) = :anio2
            WHERE c.tipo IN ('ingreso', 'gasto') AND c.activo = 1
            GROUP BY c.id_cuenta
            ORDER BY c.codigo
        ", ['anio' => $anio, 'mes' => $mes, 'mes2' => $mes, 'anio2' => $anio]);
    }

    public function setPresupuesto(int $cuentaId, int $anio, int $mes, float $monto): void
    {
        $existe = $this->fetchOne(
            "SELECT id FROM presupuestos WHERE cuenta_id = :c AND anio = :a AND mes = :m",
            ['c' => $cuentaId, 'a' => $anio, 'm' => $mes]
        );
        if ($existe) {
            $this->db->update($this->table, ['monto' => $monto], 'id = :id', ['id' => $existe['id']]);
        } else {
            $this->db->insert($this->table, [
                'cuenta_id' => $cuentaId,
                'anio' => $anio,
                'mes' => $mes,
                'monto' => $monto,
            ]);
        }
    }
}

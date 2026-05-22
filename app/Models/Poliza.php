<?php
namespace App\Models;

use App\Core\Model;

class Poliza extends Model
{
    protected $table = 'polizas';
    protected $primaryKey = 'id_poliza';

    public function getWithFilters(array $filters, int $page, int $perPage): array
    {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filters['fecha_desde'])) {
            $where .= " AND p.fecha >= :desde";
            $params['desde'] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where .= " AND p.fecha <= :hasta";
            $params['hasta'] = $filters['fecha_hasta'];
        }
        if (!empty($filters['tipo'])) {
            $where .= " AND p.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }
        if (!empty($filters['estatus'])) {
            $where .= " AND p.estatus = :estatus";
            $params['estatus'] = $filters['estatus'];
        }

        $offset = ($page - 1) * $perPage;

        $total = (int) ($this->fetchOne(
            "SELECT COUNT(*) as c FROM polizas p {$where}", $params
        )['c'] ?? 0);

        $polizas = $this->fetchAll("
            SELECT p.*, u.nombre_usuario
            FROM polizas p
            LEFT JOIN usuarios u ON p.created_by = u.id_usuario
            {$where}
            ORDER BY p.fecha DESC, p.id_poliza DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);

        return ['polizas' => $polizas, 'total' => $total, 'totalPages' => max(1, ceil($total / $perPage))];
    }

    public function getWithDetalles(int $id): ?array
    {
        $poliza = $this->fetchOne("
            SELECT p.*, u.nombre_usuario
            FROM polizas p
            LEFT JOIN usuarios u ON p.created_by = u.id_usuario
            WHERE p.id_poliza = :id
        ", ['id' => $id]);
        if (!$poliza) return null;

        $detalles = $this->fetchAll("
            SELECT pd.*, pc.codigo, pc.nombre as cuenta_nombre
            FROM polizas_detalle pd
            LEFT JOIN plan_cuentas pc ON pd.id_cuenta = pc.id_cuenta
            WHERE pd.id_poliza = :id
            ORDER BY pd.id_detalle
        ", ['id' => $id]);

        $poliza['detalles'] = $detalles;
        $poliza['total_cargo'] = array_sum(array_column($detalles, 'cargo'));
        $poliza['total_abono'] = array_sum(array_column($detalles, 'abono'));
        return $poliza;
    }

    public function generarFolio(int $timestamp): string
    {
        $anio = date('Y', $timestamp);
        $mes = date('m', $timestamp);
        $ultimo = $this->fetchOne(
            "SELECT folio FROM polizas WHERE folio LIKE :patron ORDER BY id_poliza DESC LIMIT 1",
            ['patron' => "POL-{$anio}{$mes}-%"]
        );
        if ($ultimo) {
            $parts = explode('-', $ultimo['folio']);
            $nuevoNum = ((int) end($parts)) + 1;
        } else {
            $nuevoNum = 1;
        }
        return "POL-{$anio}{$mes}-" . str_pad((string) $nuevoNum, 4, '0', STR_PAD_LEFT);
    }

    public function createWithDetalles(array $encabezado, array $detalles): int
    {
        $totalCargo = array_sum(array_column($detalles, 'cargo'));
        $totalAbono = array_sum(array_column($detalles, 'abono'));

        if (abs($totalCargo - $totalAbono) > 0.01) {
            throw new \InvalidArgumentException("Partida doble no cuadra: {$totalCargo} vs {$totalAbono}");
        }

        $this->db->insert($this->table, $encabezado);

        $idPoliza = $this->db->lastInsertId();

        foreach ($detalles as $d) {
            $this->db->insert('polizas_detalle', [
                'id_poliza' => $idPoliza,
                'id_cuenta' => $d['id_cuenta'],
                'concepto' => $d['concepto'] ?? '',
                'cargo' => $d['cargo'],
                'abono' => $d['abono'],
            ]);
        }

        return $idPoliza;
    }

    public function periodoEstaCerrado(int $mes, int $anio): bool
    {
        $r = $this->fetchOne(
            "SELECT cerrado FROM periodos_contables WHERE mes = :mes AND anio = :anio",
            ['mes' => $mes, 'anio' => $anio]
        );
        return $r ? (bool) $r['cerrado'] : false;
    }
}

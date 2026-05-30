<?php
namespace App\Services;

use App\Core\Database;

class ComisionService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function syncForVenta(int $idVenta): void
    {
        if (!$this->db->tableExists('comisiones_vendedor')) {
            return;
        }

        $venta = $this->db->fetchOne("
            SELECT v.*, c.id_vendedor as cliente_vendedor
            FROM ventas v
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE v.id_venta = :id
        ", ['id' => $idVenta]);
        if (!$venta) {
            $this->deleteByVenta($idVenta);
            return;
        }

        $existing = $this->findByVenta($idVenta);
        $idVendedor = (int) ($venta['id_vendedor'] ?: ($venta['cliente_vendedor'] ?? 0));
        $estatusVenta = (string) ($venta['estatus'] ?? $venta['estado'] ?? '');
        if ($idVendedor <= 0 || $estatusVenta === 'cancelado') {
            $this->cancelPendingByVenta($idVenta);
            return;
        }

        $porcentaje = (float) COMISION_PORCENTAJE;
        $monto = $this->calculateAmount((float) $venta['cantidad_vendida'], (float) $venta['precio_unitario'], $porcentaje);
        $data = [
            'id_vendedor' => $idVendedor,
            'id_venta' => $idVenta,
            'monto_comision' => $monto,
            'porcentaje_comision' => $porcentaje,
            'fecha_calculo' => date('Y-m-d'),
        ];

        if ($existing) {
            if ($existing['estatus'] === 'pagada') {
                return;
            }
            $data['estatus'] = 'pendiente';
            $data['fecha_pago'] = null;
            $this->db->update('comisiones_vendedor', $data, 'id_comision = :id', ['id' => $existing['id_comision']]);
            return;
        }

        $data['estatus'] = 'pendiente';
        $idComision = $this->db->insert('comisiones_vendedor', $data);
        if ($this->db->tableExists('notificaciones_vendedor')) {
            notificar_vendedor(
                $idVendedor,
                'comision_calculada',
                'Comisión calculada',
                "Comisión de \${$monto} ({$porcentaje}%) generada por venta #{$idVenta}",
                $idComision
            );
        }
    }

    public function markAsPaid(int $idComision): ?array
    {
        $comision = $this->db->fetchOne("
            SELECT *
            FROM comisiones_vendedor
            WHERE id_comision = :id
        ", ['id' => $idComision]);
        if (!$comision || $comision['estatus'] !== 'pendiente') {
            return null;
        }

        $this->db->update('comisiones_vendedor', [
            'estatus' => 'pagada',
            'fecha_pago' => date('Y-m-d'),
        ], 'id_comision = :id', ['id' => $idComision]);

        return $this->db->fetchOne("SELECT * FROM comisiones_vendedor WHERE id_comision = :id", ['id' => $idComision]) ?: null;
    }

    public function deleteByVenta(int $idVenta): void
    {
        if ($this->db->tableExists('comisiones_vendedor')) {
            $this->db->delete('comisiones_vendedor', 'id_venta = :id', ['id' => $idVenta]);
        }
    }

    public function calculateAmount(float $cantidad, float $precioUnitario, float $porcentaje): float
    {
        return round(max(0, $cantidad) * max(0, $precioUnitario) * max(0, $porcentaje) / 100, 2);
    }

    private function findByVenta(int $idVenta): ?array
    {
        return $this->db->fetchOne("
            SELECT *
            FROM comisiones_vendedor
            WHERE id_venta = :id
            ORDER BY id_comision ASC
            LIMIT 1
        ", ['id' => $idVenta]) ?: null;
    }

    private function cancelPendingByVenta(int $idVenta): void
    {
        $this->db->update('comisiones_vendedor', [
            'estatus' => 'cancelada',
            'fecha_pago' => null,
        ], "id_venta = :id AND estatus = 'pendiente'", ['id' => $idVenta]);
    }
}

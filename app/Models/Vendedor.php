<?php
namespace App\Models;

use App\Core\Model;

class Vendedor extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';

    public function getVendedores(): array
    {
        return $this->fetchAll("
            SELECT u.id_usuario, u.nombre_usuario, e.nombre, e.apellido_paterno, e.correo, e.telefono
            FROM usuarios u
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            WHERE u.id_rol = :rol AND u.activo = 1
            ORDER BY e.nombre
        ", ['rol' => ROL_VENDEDOR]);
    }

    public function getComisiones(int $vendedorId): array
    {
        return $this->fetchAll("
            SELECT c.*, t.folio_unico as venta_folio, cl.razon_social as cliente
            FROM comisiones_vendedor c
            LEFT JOIN ventas v ON c.id_venta = v.id_venta
            LEFT JOIN tickets t ON v.id_venta = t.id_venta
            LEFT JOIN clientes cl ON v.id_cliente = cl.id_cliente
            WHERE c.id_vendedor = :id
            ORDER BY c.created_at DESC
        ", ['id' => $vendedorId]);
    }

    public function getResumenComisiones(int $vendedorId): array
    {
        $pendiente = $this->fetchOne("
            SELECT COALESCE(SUM(monto_comision), 0) as total
            FROM comisiones_vendedor
            WHERE id_vendedor = :id AND estatus = 'pendiente'
        ", ['id' => $vendedorId]);

        $pagado = $this->fetchOne("
            SELECT COALESCE(SUM(monto_comision), 0) as total
            FROM comisiones_vendedor
            WHERE id_vendedor = :id AND estatus = 'pagada'
        ", ['id' => $vendedorId]);

        return [
            'pendiente' => (float) ($pendiente['total'] ?? 0),
            'pagado' => (float) ($pagado['total'] ?? 0),
        ];
    }

    public function getAllComisiones(?int $vendedorId = null, ?string $estatus = null): array
    {
        $where = 'WHERE 1=1';
        $params = [];
        if ($vendedorId !== null) {
            $where .= ' AND c.id_vendedor = :vendedor';
            $params['vendedor'] = $vendedorId;
        }
        if ($estatus !== null) {
            $where .= ' AND c.estatus = :estatus';
            $params['estatus'] = $estatus;
        }
        return $this->fetchAll("
            SELECT c.*, t.folio_unico as venta_folio, cl.razon_social as cliente,
                   CONCAT(e.nombre, ' ', e.apellido_paterno) as vendedor_nombre,
                   u.nombre_usuario
            FROM comisiones_vendedor c
            LEFT JOIN ventas v ON c.id_venta = v.id_venta
            LEFT JOIN tickets t ON v.id_venta = t.id_venta
            LEFT JOIN clientes cl ON v.id_cliente = cl.id_cliente
            LEFT JOIN usuarios u ON c.id_vendedor = u.id_usuario
            LEFT JOIN empleados e ON u.id_empleado = e.id_empleado
            {$where}
            ORDER BY c.created_at DESC
        ", $params);
    }

    public function getTotalPendienteGlobal(): float
    {
        $r = $this->fetchOne("SELECT COALESCE(SUM(monto_comision), 0) as total FROM comisiones_vendedor WHERE estatus = 'pendiente'");
        return (float) ($r['total'] ?? 0);
    }

    public function getTotalPagadoGlobal(): float
    {
        $r = $this->fetchOne("SELECT COALESCE(SUM(monto_comision), 0) as total FROM comisiones_vendedor WHERE estatus = 'pagada'");
        return (float) ($r['total'] ?? 0);
    }
}

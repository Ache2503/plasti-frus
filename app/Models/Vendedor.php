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
            SELECT c.*, t.folio_unico as venta_folio, cl.razon_social as cliente, p.nombre as producto_nombre
            FROM comisiones_vendedor c
            LEFT JOIN ventas v ON c.id_venta = v.id_venta
            LEFT JOIN tickets t ON v.id_venta = t.id_venta
            LEFT JOIN clientes cl ON v.id_cliente = cl.id_cliente
            LEFT JOIN productos p ON v.id_producto = p.id_producto
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

    public function resumenDashboard(int $vendedorId): array
    {
        $db = \App\Core\Database::getInstance();
        $anio = (int) date('Y');
        $mes = (int) date('m');
        $ventasMes = $db->fetchOne("
            SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto
            FROM ventas v LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
              AND MONTH(v.fecha_venta) = :mes AND YEAR(v.fecha_venta) = :anio
        ", ['uid' => $vendedorId, 'uid2' => $vendedorId, 'mes' => $mes, 'anio' => $anio]);
        $nuevosClientes = (int) $db->fetchOne("SELECT COUNT(*) as t FROM clientes WHERE id_vendedor = :uid AND activo = 1 AND MONTH(created_at) = :mes AND YEAR(created_at) = :anio", ['uid' => $vendedorId, 'mes' => $mes, 'anio' => $anio])['t'];
        $ventasMensuales = $db->fetchAll("
            SELECT DATE_FORMAT(v.fecha_venta, '%Y-%m') as mes, COUNT(*) as total_ventas,
                   COALESCE(SUM(v.cantidad_vendida * v.precio_unitario), 0) as monto
            FROM ventas v LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)
              AND v.fecha_venta >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
            GROUP BY mes ORDER BY mes ASC
        ", ['uid' => $vendedorId, 'uid2' => $vendedorId]);
        $meta = (new \App\Models\MetaVendedor())->getMetaMes($vendedorId, $anio, $mes);
        $montoMeta = (float) ($meta['monto_objetivo'] ?? 0);
        $montoActual = (float) ($ventasMes['monto'] ?? 0);
        return [
            'ventas_mes' => $ventasMes,
            'nuevos_clientes_mes' => $nuevosClientes,
            'ventas_mensuales' => $ventasMensuales,
            'meta_mes' => $montoMeta,
            'avance_meta' => $montoMeta > 0 ? round($montoActual / $montoMeta * 100, 1) : 0,
            'monto_actual' => $montoActual,
        ];
    }

    public function getVentasByProducto(int $vendedorId, ?string $desde = null, ?string $hasta = null): array
    {
        $db = \App\Core\Database::getInstance();
        $where = 'WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)';
        $params = ['uid' => $vendedorId, 'uid2' => $vendedorId];
        if ($desde) { $where .= ' AND v.fecha_venta >= :desde'; $params['desde'] = $desde; }
        if ($hasta) { $where .= ' AND v.fecha_venta <= :hasta'; $params['hasta'] = $hasta; }
        return $db->fetchAll("
            SELECT p.nombre, SUM(v.cantidad_vendida) as cantidad, COALESCE(SUM(v.cantidad_vendida * v.precio_unitario), 0) as total
            FROM ventas v LEFT JOIN productos p ON v.id_producto = p.id_producto
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            {$where}
            GROUP BY p.id_producto ORDER BY total DESC
        ", $params);
    }

    public function getVentasByCliente(int $vendedorId, ?string $desde = null, ?string $hasta = null): array
    {
        $db = \App\Core\Database::getInstance();
        $where = 'WHERE (v.id_vendedor = :uid OR c.id_vendedor = :uid2)';
        $params = ['uid' => $vendedorId, 'uid2' => $vendedorId];
        if ($desde) { $where .= ' AND v.fecha_venta >= :desde'; $params['desde'] = $desde; }
        if ($hasta) { $where .= ' AND v.fecha_venta <= :hasta'; $params['hasta'] = $hasta; }
        return $db->fetchAll("
            SELECT c.razon_social, COUNT(v.id_venta) as total_ventas, COALESCE(SUM(v.cantidad_vendida * v.precio_unitario), 0) as total
            FROM ventas v LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            {$where}
            GROUP BY c.id_cliente ORDER BY total DESC
        ", $params);
    }
}

<?php
namespace App\Http\Controllers\Api;

use App\Core\Database;

class StatsApiController extends BaseApiController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function dashboard(): void
    {
        $ordenesWhere = $this->db->columnExists('ordenes_cabecera', 'estatus')
            ? "WHERE estatus NOT IN ('completada', 'cancelada')"
            : "WHERE cantidad_real_buenas IS NULL OR cantidad_real_buenas = 0";

        $clientesWhere = $this->db->columnExists('clientes', 'activo')
            ? "WHERE activo = 1"
            : "";

        $productosWhere = $this->db->columnExists('productos', 'activo')
            ? "WHERE activo = 1"
            : "";

        $stats = [
            'ventas_mes' => $this->db->fetchOne("SELECT COUNT(*) as total, COALESCE(SUM(cantidad_vendida * precio_unitario), 0) as monto FROM ventas WHERE MONTH(fecha_venta) = MONTH(CURRENT_DATE) AND YEAR(fecha_venta) = YEAR(CURRENT_DATE)"),
            'ordenes_pendientes' => $this->db->fetchOne("SELECT COUNT(*) as total FROM ordenes_cabecera {$ordenesWhere}"),
            'clientes_activos' => $this->db->fetchOne("SELECT COUNT(*) as total FROM clientes {$clientesWhere}"),
            'productos' => $this->db->fetchOne("SELECT COUNT(*) as total FROM productos {$productosWhere}"),
        ];
        $this->success($stats);
    }

    public function produccion(): void
    {
        $data = $this->db->fetchAll("
            SELECT DATE(fecha) as dia, 
                   COUNT(*) as total_ordenes,
                   COALESCE(SUM(cantidad_planificada), 0) as planificadas,
                   COALESCE(SUM(cantidad_real_buenas), 0) as completadas
            FROM ordenes_cabecera
            WHERE fecha >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY DATE(fecha)
            ORDER BY dia DESC
            LIMIT 30
        ");
        $this->success($data);
    }
}

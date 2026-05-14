<?php
namespace App\Models;

use App\Core\Model;

class Ticket extends Model
{
    protected $table = 'tickets';
    protected $primaryKey = 'id_ticket';

    public function getByFolio(string $folio)
    {
        return $this->fetchOne(
            "SELECT t.*, v.id_cliente, v.id_producto, v.cantidad_vendida, v.precio_unitario, v.moneda, v.fecha_venta, v.estatus as venta_estatus,
                    c.razon_social, c.rfc, c.codigo_postal, c.regimen_fiscal, c.uso_cfdi, c.correo_fiscal, c.ciudad, c.estado,
                    p.nombre as producto_nombre, p.codigo as producto_codigo
             FROM tickets t
             INNER JOIN ventas v ON t.id_venta = v.id_venta
             LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
             LEFT JOIN productos p ON v.id_producto = p.id_producto
             WHERE t.folio_unico = :folio",
            ['folio' => $folio]
        );
    }

    public function getByVenta(int $idVenta)
    {
        return $this->fetchOne(
            "SELECT * FROM tickets WHERE id_venta = :id",
            ['id' => $idVenta]
        );
    }

    public function getByCliente(int $idCliente): array
    {
        return $this->fetchAll(
            "SELECT t.*, v.cantidad_vendida, v.precio_unitario, v.moneda, v.fecha_venta, p.nombre as producto_nombre
             FROM tickets t
             INNER JOIN ventas v ON t.id_venta = v.id_venta
             LEFT JOIN productos p ON v.id_producto = p.id_producto
             WHERE v.id_cliente = :id
             ORDER BY t.fecha_emision DESC",
            ['id' => $idCliente]
        );
    }

    public function createFromVenta(array $venta, array $producto): int
    {
        $folio = generate_folio('TKT');
        $datos = [
            'venta' => $venta,
            'producto' => $producto,
        ];
        return $this->create([
            'id_venta' => $venta['id_venta'],
            'folio_unico' => $folio,
            'datos_json' => json_encode($datos, JSON_UNESCAPED_UNICODE),
            'estatus' => 'emitido',
        ]);
    }

    public function cancelar(int $id): void
    {
        $this->update($id, ['estatus' => 'cancelado']);
    }
}

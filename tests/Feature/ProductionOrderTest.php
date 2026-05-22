<?php
namespace Tests\Feature;

use Tests\TestCase;

class ProductionOrderTest extends TestCase
{
    public function test_can_create_order(): void
    {
        $idProducto = $this->db()->insert('productos', [
            'codigo' => 'PROD-' . uniqid(),
            'nombre' => 'Producto Orden Test',
            'familia' => 'Prueba',
            'precio_venta' => 100.00,
            'stock_actual' => 0,
            'peso_unitario_grs' => 30.00,
        ]);

        $id = $this->db()->insert('ordenes_cabecera', [
            'id_producto' => $idProducto,
            'cantidad_planificada' => 500,
            'cantidad_real_buenas' => 0,
            'fecha' => date('Y-m-d'),
            'turno' => 'Matutino',
            'estatus' => 'pendiente',
        ]);

        $order = $this->db()->fetchOne(
            "SELECT * FROM ordenes_cabecera WHERE id_orden_cabe = :id",
            ['id' => $id]
        );
        $this->assertNotNull($order);
        $this->assertEquals(500, $order['cantidad_planificada']);
        $this->assertEquals('pendiente', $order['estatus']);
        $this->assertEquals('Matutino', $order['turno']);
    }

    public function test_order_requires_product(): void
    {
        $this->expectException(\PDOException::class);
        $this->db()->insert('ordenes_cabecera', [
            'id_producto' => 99999,
            'cantidad_planificada' => 100,
            'fecha' => date('Y-m-d'),
            'turno' => 'Matutino',
            'estatus' => 'pendiente',
        ]);
    }

    public function test_can_update_order_status(): void
    {
        $idProducto = $this->db()->insert('productos', [
            'codigo' => 'PROD-' . uniqid(),
            'nombre' => 'Status Test',
            'familia' => 'Prueba',
            'precio_venta' => 50.00,
        ]);

        $id = $this->db()->insert('ordenes_cabecera', [
            'id_producto' => $idProducto,
            'cantidad_planificada' => 300,
            'fecha' => date('Y-m-d'),
            'turno' => 'Vespertino',
            'estatus' => 'pendiente',
        ]);

        $this->db()->update('ordenes_cabecera',
            ['estatus' => 'en_proceso', 'cantidad_real_buenas' => 150],
            'id_orden_cabe = :id',
            ['id' => $id]
        );

        $order = $this->db()->fetchOne(
            "SELECT * FROM ordenes_cabecera WHERE id_orden_cabe = :id",
            ['id' => $id]
        );
        $this->assertEquals('en_proceso', $order['estatus']);
        $this->assertEquals(150, $order['cantidad_real_buenas']);
    }

    public function test_planned_quantity_is_positive(): void
    {
        $idProducto = $this->db()->insert('productos', [
            'codigo' => 'PROD-' . uniqid(),
            'nombre' => 'Quantity Test',
            'familia' => 'Prueba',
            'precio_venta' => 75.00,
        ]);

        $id = $this->db()->insert('ordenes_cabecera', [
            'id_producto' => $idProducto,
            'cantidad_planificada' => 0,
            'fecha' => date('Y-m-d'),
            'turno' => 'Matutino',
            'estatus' => 'pendiente',
        ]);

        $order = $this->db()->fetchOne(
            "SELECT * FROM ordenes_cabecera WHERE id_orden_cabe = :id",
            ['id' => $id]
        );
        $this->assertEquals(0, $order['cantidad_planificada']);
    }
}

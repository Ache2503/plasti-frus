<?php
namespace Tests\Feature;

use Tests\TestCase;

class InventoryTest extends TestCase
{
    public function test_can_create_material(): void
    {
        $idProveedor = $this->db()->insert('proveedores', [
            'razon_social' => 'Proveedor Test ' . uniqid(),
            'rfc' => 'XAXX010101000',
            'estatus' => 'activo',
        ]);

        $id = $this->db()->insert('materiales', [
            'id_proveedor' => $idProveedor,
            'tipo' => 'Polímero',
            'nombre' => 'Polipropileno Test',
            'presentacion' => 'Saco 25kg',
            'unidad_medida' => 'kg',
            'stock_actual_kg' => 500.00,
            'punto_reorden_kg' => 50.00,
        ]);

        $material = $this->db()->fetchOne(
            "SELECT * FROM materiales WHERE id_material = :id",
            ['id' => $id]
        );
        $this->assertNotNull($material);
        $this->assertEquals('Polipropileno Test', $material['nombre']);
        $this->assertEquals(500.00, (float)$material['stock_actual_kg']);
    }

    public function test_can_create_product(): void
    {
        $id = $this->db()->insert('productos', [
            'codigo' => 'PROD-' . uniqid(),
            'nombre' => 'Tapadera Plástica Test',
            'familia' => 'Tapas',
            'linea' => 'Línea Estándar',
            'color' => 'Negro',
            'peso_unitario_grs' => 45.50,
            'precio_venta' => 12.50,
            'stock_actual' => 1000,
            'publicar_web' => 0,
        ]);

        $product = $this->db()->fetchOne(
            "SELECT * FROM productos WHERE id_producto = :id",
            ['id' => $id]
        );
        $this->assertNotNull($product);
        $this->assertEquals('Tapadera Plástica Test', $product['nombre']);
        $this->assertEquals(45.50, (float)$product['peso_unitario_grs']);
        $this->assertEquals(12.50, (float)$product['precio_venta']);
    }

    public function test_stock_calculation(): void
    {
        $id = $this->db()->insert('materiales', [
            'tipo' => 'Polímero',
            'nombre' => 'Stock Test Material',
            'presentacion' => 'Saco 25kg',
            'unidad_medida' => 'kg',
            'stock_actual_kg' => 200.00,
            'punto_reorden_kg' => 50.00,
        ]);

        $total = $this->db()->fetchOne(
            "SELECT SUM(stock_actual_kg) as total FROM materiales WHERE id_material = :id",
            ['id' => $id]
        );
        $this->assertEquals(200.00, (float)$total['total']);
    }

    public function test_low_stock_detection(): void
    {
        $id = $this->db()->insert('materiales', [
            'tipo' => 'Polímero',
            'nombre' => 'Low Stock Material',
            'presentacion' => 'Saco 25kg',
            'unidad_medida' => 'kg',
            'stock_actual_kg' => 10.00,
            'punto_reorden_kg' => 50.00,
        ]);

        $lowStock = $this->db()->fetchAll(
            "SELECT * FROM materiales WHERE stock_actual_kg <= punto_reorden_kg AND id_material = :id",
            ['id' => $id]
        );
        $this->assertCount(1, $lowStock);
    }
}

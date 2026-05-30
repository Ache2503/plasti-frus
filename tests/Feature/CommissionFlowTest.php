<?php
namespace Tests\Feature;

use App\Services\ComisionService;
use App\Services\VentaService;
use Tests\TestCase;

class CommissionFlowTest extends TestCase
{
    public function test_sale_creation_generates_commission_for_sale_vendor(): void
    {
        $vendedor = $this->createVendor();
        $cliente = $this->createClient();
        $producto = $this->createProduct();

        $ventaService = new VentaService();
        $idVenta = $ventaService->create([
            'id_cliente' => $cliente,
            'id_vendedor' => $vendedor,
            'id_producto' => $producto,
            'cantidad_vendida' => 10,
            'precio_unitario' => 100,
            'fecha_venta' => date('Y-m-d'),
            'estatus' => 'completado',
        ]);

        $comision = $this->db()->fetchOne('SELECT * FROM comisiones_vendedor WHERE id_venta = :id', ['id' => $idVenta]);
        $this->assertNotFalse($comision);
        $this->assertSame($vendedor, (int) $comision['id_vendedor']);
        $this->assertSame(50.0, (float) $comision['monto_comision']);
        $this->assertSame('pendiente', $comision['estatus']);
    }

    public function test_sale_creation_uses_client_vendor_when_sale_vendor_is_empty(): void
    {
        $vendedor = $this->createVendor();
        $cliente = $this->createClient($vendedor);
        $producto = $this->createProduct();

        $idVenta = (new VentaService())->create([
            'id_cliente' => $cliente,
            'id_producto' => $producto,
            'cantidad_vendida' => 4,
            'precio_unitario' => 250,
            'fecha_venta' => date('Y-m-d'),
            'estatus' => 'completado',
        ]);

        $comision = $this->db()->fetchOne('SELECT * FROM comisiones_vendedor WHERE id_venta = :id', ['id' => $idVenta]);
        $this->assertSame($vendedor, (int) $comision['id_vendedor']);
        $this->assertSame(50.0, (float) $comision['monto_comision']);
    }

    public function test_sale_update_recalculates_pending_commission(): void
    {
        $vendedor = $this->createVendor();
        $cliente = $this->createClient($vendedor);
        $producto = $this->createProduct();
        $ventaService = new VentaService();

        $idVenta = $ventaService->create([
            'id_cliente' => $cliente,
            'id_producto' => $producto,
            'cantidad_vendida' => 1,
            'precio_unitario' => 100,
            'fecha_venta' => date('Y-m-d'),
            'estatus' => 'completado',
        ]);
        $ventaService->update($idVenta, [
            'id_cliente' => $cliente,
            'id_producto' => $producto,
            'cantidad_vendida' => 3,
            'precio_unitario' => 200,
            'fecha_venta' => date('Y-m-d'),
            'estatus' => 'completado',
        ]);

        $comisiones = $this->db()->fetchAll('SELECT * FROM comisiones_vendedor WHERE id_venta = :id', ['id' => $idVenta]);
        $this->assertCount(1, $comisiones);
        $this->assertSame(30.0, (float) $comisiones[0]['monto_comision']);
    }

    public function test_cancelled_sale_cancels_pending_commission(): void
    {
        $vendedor = $this->createVendor();
        $cliente = $this->createClient($vendedor);
        $producto = $this->createProduct();
        $ventaService = new VentaService();

        $idVenta = $ventaService->create([
            'id_cliente' => $cliente,
            'id_producto' => $producto,
            'cantidad_vendida' => 5,
            'precio_unitario' => 100,
            'fecha_venta' => date('Y-m-d'),
            'estatus' => 'completado',
        ]);
        $ventaService->update($idVenta, [
            'id_cliente' => $cliente,
            'id_producto' => $producto,
            'cantidad_vendida' => 5,
            'precio_unitario' => 100,
            'fecha_venta' => date('Y-m-d'),
            'estatus' => 'cancelado',
        ]);

        $comision = $this->db()->fetchOne('SELECT * FROM comisiones_vendedor WHERE id_venta = :id', ['id' => $idVenta]);
        $this->assertSame('cancelada', $comision['estatus']);
    }

    public function test_paid_commission_is_not_recalculated_by_sale_update(): void
    {
        $vendedor = $this->createVendor();
        $cliente = $this->createClient($vendedor);
        $producto = $this->createProduct();
        $ventaService = new VentaService();

        $idVenta = $ventaService->create([
            'id_cliente' => $cliente,
            'id_producto' => $producto,
            'cantidad_vendida' => 2,
            'precio_unitario' => 100,
            'fecha_venta' => date('Y-m-d'),
            'estatus' => 'completado',
        ]);
        $comision = $this->db()->fetchOne('SELECT * FROM comisiones_vendedor WHERE id_venta = :id', ['id' => $idVenta]);
        (new ComisionService())->markAsPaid((int) $comision['id_comision']);

        $ventaService->update($idVenta, [
            'id_cliente' => $cliente,
            'id_producto' => $producto,
            'cantidad_vendida' => 20,
            'precio_unitario' => 100,
            'fecha_venta' => date('Y-m-d'),
            'estatus' => 'completado',
        ]);

        $pagada = $this->db()->fetchOne('SELECT * FROM comisiones_vendedor WHERE id_venta = :id', ['id' => $idVenta]);
        $this->assertSame('pagada', $pagada['estatus']);
        $this->assertSame(10.0, (float) $pagada['monto_comision']);
    }

    private function createVendor(): int
    {
        $empleado = $this->db()->insert('empleados', [
            'nombre' => 'Vendedor',
            'apellido_paterno' => uniqid(),
            'correo' => uniqid() . '@test.local',
            'estatus' => 'activo',
        ]);
        return $this->db()->insert('usuarios', [
            'id_empleado' => $empleado,
            'nombre_usuario' => 'vend_' . uniqid(),
            'password_hash' => password_hash('test123456', PASSWORD_DEFAULT),
            'id_rol' => ROL_VENDEDOR,
            'activo' => 1,
        ]);
    }

    private function createClient(?int $idVendedor = null): int
    {
        return $this->db()->insert('clientes', [
            'razon_social' => 'Cliente Comision ' . uniqid(),
            'rfc' => 'XAXX010101000',
            'id_vendedor' => $idVendedor,
            'activo' => 1,
        ]);
    }

    private function createProduct(): int
    {
        return $this->db()->insert('productos', [
            'codigo' => 'COM-' . uniqid(),
            'nombre' => 'Producto Comision',
            'precio_venta' => 100,
            'stock_actual' => 0,
        ]);
    }
}

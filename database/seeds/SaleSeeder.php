<?php
namespace Database\Seeds;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $comisiones = new \App\Services\ComisionService();
        $clientes = $this->db->fetchAll("SELECT id_cliente FROM clientes");
        $clienteIds = array_column($clientes, 'id_cliente');
        $productos = $this->db->fetchAll("SELECT id_producto, precio_venta FROM productos WHERE precio_venta IS NOT NULL");
        $vendedores = $this->db->fetchAll("SELECT id_usuario FROM usuarios WHERE id_rol = 4");

        if (empty($clienteIds) || empty($productos) || empty($vendedores)) return;

        $vendedorIds = array_column($vendedores, 'id_usuario');
        $condiciones = ['Contado', '30 días', '60 días', 'Crédito 15 días', 'Crédito 45 días'];
        $monedas = ['MXN', 'MXN', 'MXN', 'USD'];
        $estatuses = ['completado', 'completado', 'completado', 'pendiente', 'cancelado'];

        $today = new \DateTime();

        for ($i = 0; $i < 18; $i++) {
            $clienteId = $clienteIds[array_rand($clienteIds)];
            $producto = $productos[array_rand($productos)];
            $vendedorId = $vendedorIds[array_rand($vendedorIds)];

            $diasAtras = rand(0, 29);
            $fecha = (clone $today)->modify("-{$diasAtras} days")->format('Y-m-d');
            $cantidad = rand(50, 5000);
            $precioUnitario = (float)$producto['precio_venta'];

            $condicion = $condiciones[array_rand($condiciones)];
            $moneda = $monedas[array_rand($monedas)];
            $estatus = $estatuses[array_rand($estatuses)];

            $idVenta = $this->insert('ventas', [
                'id_cliente' => $clienteId,
                'id_vendedor' => $vendedorId,
                'id_pedido' => null,
                'id_producto' => $producto['id_producto'],
                'cantidad_vendida' => $cantidad,
                'fecha_venta' => $fecha,
                'precio_unitario' => $precioUnitario,
                'moneda' => $moneda,
                'condiciones_pago' => $condicion,
                'estatus' => $estatus,
                'estado' => $estatus,
            ]);
            $comisiones->syncForVenta($idVenta);
        }
    }
}

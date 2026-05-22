<?php
namespace Database\Seeds;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            // Familia Tapas
            ['codigo' => 'TAP-010', 'nombre' => 'Tapa Rosca 28mm Natural', 'familia' => 'Tapas', 'linea' => 'Bebidas', 'color' => 'Natural', 'peso' => 2.80, 'dimensiones' => '28mm x 8mm', 'precio' => 0.85, 'stock' => 50000, 'web' => 1],
            ['codigo' => 'TAP-011', 'nombre' => 'Tapa Rosca 38mm Blanco', 'familia' => 'Tapas', 'linea' => 'Bebidas', 'color' => 'Blanco', 'peso' => 3.50, 'dimensiones' => '38mm x 10mm', 'precio' => 1.20, 'stock' => 35000, 'web' => 1],
            ['codigo' => 'TAP-012', 'nombre' => 'Tapa Rosca 48mm Azul', 'familia' => 'Tapas', 'linea' => 'Bebidas', 'color' => 'Azul', 'peso' => 4.20, 'dimensiones' => '48mm x 12mm', 'precio' => 1.50, 'stock' => 25000, 'web' => 1],
            ['codigo' => 'TAP-013', 'nombre' => 'Tapa Flip-Top 28mm Negro', 'familia' => 'Tapas', 'linea' => 'Premium', 'color' => 'Negro', 'peso' => 4.80, 'dimensiones' => '28mm x 15mm', 'precio' => 2.50, 'stock' => 15000, 'web' => 1],
            ['codigo' => 'TAP-014', 'nombre' => 'Tapa Dispensadora 38mm', 'familia' => 'Tapas', 'linea' => 'Especiales', 'color' => 'Blanco', 'peso' => 6.20, 'dimensiones' => '38mm x 18mm', 'precio' => 3.80, 'stock' => 8000, 'web' => 1],
            ['codigo' => 'TAP-015', 'nombre' => 'Tapa Snorkel 28mm', 'familia' => 'Tapas', 'linea' => 'Deportivas', 'color' => 'Azul', 'peso' => 5.10, 'dimensiones' => '28mm x 20mm', 'precio' => 3.20, 'stock' => 12000, 'web' => 1],
            ['codigo' => 'TAP-016', 'nombre' => 'Tapa Industrial 63mm', 'familia' => 'Tapas', 'linea' => 'Industrial', 'color' => 'Gris', 'peso' => 8.50, 'dimensiones' => '63mm x 15mm', 'precio' => 2.80, 'stock' => 5000, 'web' => 0],
            // Familia Envases
            ['codigo' => 'ENV-010', 'nombre' => 'Botella 500ml PET', 'familia' => 'Envases', 'linea' => 'Bebidas', 'color' => 'Transparente', 'peso' => 18.00, 'dimensiones' => '65x65x180mm', 'precio' => 3.50, 'stock' => 20000, 'web' => 1],
            ['codigo' => 'ENV-011', 'nombre' => 'Botella 1L PET', 'familia' => 'Envases', 'linea' => 'Bebidas', 'color' => 'Transparente', 'peso' => 28.00, 'dimensiones' => '80x80x250mm', 'precio' => 4.80, 'stock' => 15000, 'web' => 1],
            ['codigo' => 'ENV-012', 'nombre' => 'Botella 2L PET', 'familia' => 'Envases', 'linea' => 'Bebidas', 'color' => 'Transparente', 'peso' => 42.00, 'dimensiones' => '100x100x300mm', 'precio' => 6.50, 'stock' => 10000, 'web' => 1],
            ['codigo' => 'ENV-013', 'nombre' => 'Garrafón 5L PP', 'familia' => 'Envases', 'linea' => 'Hogar', 'color' => 'Natural', 'peso' => 85.00, 'dimensiones' => '150x150x350mm', 'precio' => 18.50, 'stock' => 5000, 'web' => 1],
            ['codigo' => 'ENV-014', 'nombre' => 'Garrafón 20L PEAD', 'familia' => 'Envases', 'linea' => 'Industrial', 'color' => 'Azul', 'peso' => 320.00, 'dimensiones' => '280x280x500mm', 'precio' => 45.00, 'stock' => 2000, 'web' => 0],
            ['codigo' => 'ENV-015', 'nombre' => 'Cubeta 19L PEAD', 'familia' => 'Envases', 'linea' => 'Industrial', 'color' => 'Blanco', 'peso' => 450.00, 'dimensiones' => '300x300x400mm', 'precio' => 55.00, 'stock' => 3000, 'web' => 1],
            ['codigo' => 'ENV-016', 'nombre' => 'Envase 250ml PP', 'familia' => 'Envases', 'linea' => 'Alimentos', 'color' => 'Natural', 'peso' => 8.50, 'dimensiones' => '50x50x120mm', 'precio' => 2.20, 'stock' => 30000, 'web' => 1],
            // Familia Industriales
            ['codigo' => 'IND-001', 'nombre' => 'Caja Plástica 40x30x25', 'familia' => 'Industriales', 'linea' => 'Almacén', 'color' => 'Gris', 'peso' => 850.00, 'dimensiones' => '400x300x250mm', 'precio' => 85.00, 'stock' => 1500, 'web' => 1],
            ['codigo' => 'IND-002', 'nombre' => 'Tarima Plástica 120x100', 'familia' => 'Industriales', 'linea' => 'Logística', 'color' => 'Azul', 'peso' => 2500.00, 'dimensiones' => '1200x1000x150mm', 'precio' => 320.00, 'stock' => 500, 'web' => 1],
            ['codigo' => 'IND-003', 'nombre' => 'Tambo 200L PEAD', 'familia' => 'Industriales', 'linea' => 'Industrial', 'color' => 'Azul', 'peso' => 5800.00, 'dimensiones' => '580x580x900mm', 'precio' => 450.00, 'stock' => 200, 'web' => 0],
            ['codigo' => 'IND-004', 'nombre' => 'Contenedor 1000L', 'familia' => 'Industriales', 'linea' => 'Industrial', 'color' => 'Negro', 'peso' => 12000.00, 'dimensiones' => '1200x1000x1100mm', 'precio' => 2800.00, 'stock' => 50, 'web' => 0],
            ['codigo' => 'IND-005', 'nombre' => 'Caja para Baterías 30x20x15', 'familia' => 'Industriales', 'linea' => 'Automotriz', 'color' => 'Negro', 'peso' => 350.00, 'dimensiones' => '300x200x150mm', 'precio' => 45.00, 'stock' => 3000, 'web' => 1],
            // Familia Hogar
            ['codigo' => 'HOG-001', 'nombre' => 'Cesto para Ropa 60L', 'familia' => 'Hogar', 'linea' => 'Organización', 'color' => 'Blanco', 'peso' => 320.00, 'dimensiones' => '350x350x450mm', 'precio' => 65.00, 'stock' => 800, 'web' => 1],
            ['codigo' => 'HOG-002', 'nombre' => 'Perchero Multiusos 5 Ganchos', 'familia' => 'Hogar', 'linea' => 'Organización', 'color' => 'Blanco', 'peso' => 180.00, 'dimensiones' => '400x100x50mm', 'precio' => 38.00, 'stock' => 1200, 'web' => 1],
            ['codigo' => 'HOG-003', 'nombre' => 'Organizador de Cajones 8 Secciones', 'familia' => 'Hogar', 'linea' => 'Organización', 'color' => 'Gris', 'peso' => 95.00, 'dimensiones' => '280x180x40mm', 'precio' => 28.00, 'stock' => 2000, 'web' => 1],
            ['codigo' => 'HOG-004', 'nombre' => 'Jarra con Tapa 1.5L', 'familia' => 'Hogar', 'linea' => 'Cocina', 'color' => 'Transparente', 'peso' => 75.00, 'dimensiones' => '100x100x200mm', 'precio' => 22.00, 'stock' => 2500, 'web' => 1],
            ['codigo' => 'HOG-005', 'nombre' => 'Escurreplatos 2 Niveles', 'familia' => 'Hogar', 'linea' => 'Cocina', 'color' => 'Blanco', 'peso' => 280.00, 'dimensiones' => '400x300x150mm', 'precio' => 55.00, 'stock' => 600, 'web' => 1],
            ['codigo' => 'HOG-006', 'nombre' => 'Base para Escritorio 60x40', 'familia' => 'Hogar', 'linea' => 'Oficina', 'color' => 'Negro', 'peso' => 150.00, 'dimensiones' => '600x400x10mm', 'precio' => 35.00, 'stock' => 400, 'web' => 1],
            // Familia Especiales
            ['codigo' => 'ESP-001', 'nombre' => 'Tapón Cónico para Tubo 2"', 'familia' => 'Especiales', 'linea' => 'Industrial', 'color' => 'Gris', 'peso' => 12.50, 'dimensiones' => '50x30mm', 'precio' => 4.50, 'stock' => 8000, 'web' => 0],
            ['codigo' => 'ESP-002', 'nombre' => 'Anillo de Sello para Tambo', 'familia' => 'Especiales', 'linea' => 'Industrial', 'color' => 'Negro', 'peso' => 8.00, 'dimensiones' => '580mm diámetro', 'precio' => 6.00, 'stock' => 3000, 'web' => 0],
            ['codigo' => 'ESP-003', 'nombre' => 'Soporte Plástico para Estante', 'familia' => 'Especiales', 'linea' => 'Almacén', 'color' => 'Gris', 'peso' => 65.00, 'dimensiones' => '150x50x40mm', 'precio' => 8.50, 'stock' => 5000, 'web' => 0],
            ['codigo' => 'ESP-004', 'nombre' => 'Separador de Botellas 6 Divisiones', 'familia' => 'Especiales', 'linea' => 'Logística', 'color' => 'Natural', 'peso' => 45.00, 'dimensiones' => '300x200x50mm', 'precio' => 12.00, 'stock' => 3500, 'web' => 1],
            ['codigo' => 'ESP-005', 'nombre' => 'Tapa para Garrafón 5L Dispensadora', 'familia' => 'Especiales', 'linea' => 'Hogar', 'color' => 'Azul', 'peso' => 15.00, 'dimensiones' => '45mm x 25mm', 'precio' => 5.50, 'stock' => 6000, 'web' => 1],
        ];

        foreach ($productos as $p) {
            $this->insert('productos', [
                'codigo' => $p['codigo'],
                'nombre' => $p['nombre'],
                'familia' => $p['familia'],
                'linea' => $p['linea'],
                'color' => $p['color'],
                'peso_unitario_grs' => $p['peso'],
                'dimensiones' => $p['dimensiones'],
                'descripcion_comercial' => $p['nombre'] . ' - ' . $p['familia'] . ' línea ' . $p['linea'],
                'precio_venta' => $p['precio'],
                'stock_actual' => $p['stock'],
                'publicar_web' => $p['web'],
            ]);
        }
    }
}

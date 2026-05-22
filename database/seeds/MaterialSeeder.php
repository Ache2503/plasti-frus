<?php
namespace Database\Seeds;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = $this->db->fetchAll("SELECT id_proveedor FROM proveedores");
        $provIds = array_column($proveedores, 'id_proveedor');
        if (empty($provIds)) $provIds = [1, 2, 3, 4];

        $tipos_material = ['resina', 'resina', 'resina', 'colorante', 'colorante', 'aditivo', 'aditivo', 'empaque', 'empaque'];

        $materiales = [
            ['nombre' => 'Polipropileno Homopolímero Grado Inyección', 'presentacion' => 'Saco 25kg', 'unidad' => 'kg', 'stock' => 4200, 'reorden' => 800, 'tipo' => 'resina'],
            ['nombre' => 'Polipropileno Copolímero Impacto', 'presentacion' => 'Saco 25kg', 'unidad' => 'kg', 'stock' => 2800, 'reorden' => 600, 'tipo' => 'resina'],
            ['nombre' => 'Polietileno Alta Densidad Soplado', 'presentacion' => 'Saco 25kg', 'unidad' => 'kg', 'stock' => 5600, 'reorden' => 1000, 'tipo' => 'resina'],
            ['nombre' => 'Polietileno Baja Densidad Film', 'presentacion' => 'Saco 25kg', 'unidad' => 'kg', 'stock' => 1800, 'reorden' => 500, 'tipo' => 'resina'],
            ['nombre' => 'ABS General Purpose', 'presentacion' => 'Saco 25kg', 'unidad' => 'kg', 'stock' => 950, 'reorden' => 300, 'tipo' => 'resina'],
            ['nombre' => 'Nylon 6 Grado Inyección', 'presentacion' => 'Saco 25kg', 'unidad' => 'kg', 'stock' => 620, 'reorden' => 200, 'tipo' => 'resina'],
            ['nombre' => 'Policarbonato Grado Óptico', 'presentacion' => 'Saco 25kg', 'unidad' => 'kg', 'stock' => 340, 'reorden' => 150, 'tipo' => 'resina'],
            ['nombre' => 'Polietileno Tereftalato PET', 'presentacion' => 'Saco 25kg', 'unidad' => 'kg', 'stock' => 2100, 'reorden' => 500, 'tipo' => 'resina'],
            ['nombre' => 'Polietileno de Ultra Alto Peso Molecular', 'presentacion' => 'Saco 20kg', 'unidad' => 'kg', 'stock' => 180, 'reorden' => 100, 'tipo' => 'resina'],
            ['nombre' => 'Masterbatch Negro Grado Inyección', 'presentacion' => 'Caja 20kg', 'unidad' => 'kg', 'stock' => 420, 'reorden' => 100, 'tipo' => 'colorante'],
            ['nombre' => 'Masterbatch Blanco Óptico', 'presentacion' => 'Caja 20kg', 'unidad' => 'kg', 'stock' => 85, 'reorden' => 100, 'tipo' => 'colorante'],
            ['nombre' => 'Masterbatch Rojo Fuego', 'presentacion' => 'Caja 20kg', 'unidad' => 'kg', 'stock' => 210, 'reorden' => 80, 'tipo' => 'colorante'],
            ['nombre' => 'Masterbatch Azul Marino', 'presentacion' => 'Caja 20kg', 'unidad' => 'kg', 'stock' => 165, 'reorden' => 80, 'tipo' => 'colorante'],
            ['nombre' => 'Masterbatch Verde Olivo', 'presentacion' => 'Caja 20kg', 'unidad' => 'kg', 'stock' => 45, 'reorden' => 60, 'tipo' => 'colorante'],
            ['nombre' => 'Masterbatch Amarillo', 'presentacion' => 'Caja 20kg', 'unidad' => 'kg', 'stock' => 95, 'reorden' => 60, 'tipo' => 'colorante'],
            ['nombre' => 'Antioxidante Primario', 'presentacion' => 'Tambor 25kg', 'unidad' => 'kg', 'stock' => 180, 'reorden' => 50, 'tipo' => 'aditivo'],
            ['nombre' => 'Estabilizante UV Orgánico', 'presentacion' => 'Tambor 20kg', 'unidad' => 'kg', 'stock' => 120, 'reorden' => 40, 'tipo' => 'aditivo'],
            ['nombre' => 'Lubricante Externo', 'presentacion' => 'Tambor 25kg', 'unidad' => 'kg', 'stock' => 65, 'reorden' => 30, 'tipo' => 'aditivo'],
            ['nombre' => 'Agente Antiestático', 'presentacion' => 'Tambor 20kg', 'unidad' => 'kg', 'stock' => 42, 'reorden' => 25, 'tipo' => 'aditivo'],
            ['nombre' => 'Pigmento Líquido Negro', 'presentacion' => 'Garrafa 5L', 'unidad' => 'L', 'stock' => 80, 'reorden' => 30, 'tipo' => 'aditivo'],
            ['nombre' => 'Caja de Cartón Corrugado 40x30x20', 'presentacion' => 'Paquete 25 pzas', 'unidad' => 'pzas', 'stock' => 3000, 'reorden' => 500, 'tipo' => 'empaque'],
            ['nombre' => 'Bolsa Polietileno 30x40cm', 'presentacion' => 'Paquete 100 pzas', 'unidad' => 'pzas', 'stock' => 5000, 'reorden' => 1000, 'tipo' => 'empaque'],
            ['nombre' => 'Cinta Empaque Transparente', 'presentacion' => 'Rollos 48mmx100m', 'unidad' => 'pzas', 'stock' => 240, 'reorden' => 50, 'tipo' => 'empaque'],
            ['nombre' => 'Film Stretch 20 micras', 'presentacion' => 'Rollos 50cmx300m', 'unidad' => 'pzas', 'stock' => 85, 'reorden' => 30, 'tipo' => 'empaque'],
            ['nombre' => 'Etiqueta Adhesiva Blanca 4x6', 'presentacion' => 'Paquete 500 pzas', 'unidad' => 'pzas', 'stock' => 15, 'reorden' => 20, 'tipo' => 'empaque'],
        ];

        foreach ($materiales as $m) {
            $provId = $provIds[array_rand($provIds)];
            $lote = 'LOTE-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $m['nombre']), 0, 8)) . '-' . date('Y') . '-' . str_pad((string)rand(1, 99), 2, '0', STR_PAD_LEFT);

            $this->insert('materiales', [
                'id_proveedor' => $provId,
                'tipo' => $m['tipo'],
                'nombre' => $m['nombre'],
                'presentacion' => $m['presentacion'],
                'unidad_medida' => $m['unidad'],
                'stock_actual_kg' => $m['stock'],
                'punto_reorden_kg' => $m['reorden'],
                'lote_recepcion' => $lote,
            ]);
        }
    }
}

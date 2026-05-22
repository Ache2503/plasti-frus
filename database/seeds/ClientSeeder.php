<?php
namespace Database\Seeds;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $vendedores = $this->db->fetchAll("SELECT id_usuario FROM usuarios WHERE id_rol = 4");
        $vendedorIds = array_column($vendedores, 'id_usuario');
        if (empty($vendedorIds)) $vendedorIds = [6, 7];

        $clients = [
            [
                'razon_social' => 'Plásticos del Norte S.A. de C.V.',
                'rfc' => 'PNE-123456-ABC', 'ciudad' => 'Monterrey',
                'estado' => 'Nuevo León', 'sector' => 'Industrial',
                'contacto_nombre' => 'Roberto Garza', 'contacto_cargo' => 'Gerente de Compras',
            ],
            [
                'razon_social' => 'Envases Mexicanos S.A.',
                'rfc' => 'EMX-234567-DEF', 'ciudad' => 'CDMX',
                'estado' => 'CDMX', 'sector' => 'Alimentos',
                'contacto_nombre' => 'Mariana Soto', 'contacto_cargo' => 'Directora de Operaciones',
            ],
            [
                'razon_social' => 'Autopartes del Centro S.A.',
                'rfc' => 'ACE-345678-GHI', 'ciudad' => 'Querétaro',
                'estado' => 'Querétaro', 'sector' => 'Automotriz',
                'contacto_nombre' => 'Jorge Medina', 'contacto_cargo' => 'Ingeniero de Materiales',
            ],
            [
                'razon_social' => 'Empaques Universales S.A. de C.V.',
                'rfc' => 'EUN-456789-JKL', 'ciudad' => 'Guadalajara',
                'estado' => 'Jalisco', 'sector' => 'Empaques',
                'contacto_nombre' => 'Leticia Rangel', 'contacto_cargo' => 'Coordinadora de Compras',
            ],
            [
                'razon_social' => 'Botellas y Envases del Golfo S.A.',
                'rfc' => 'BEG-567890-MNO', 'ciudad' => 'Veracruz',
                'estado' => 'Veracruz', 'sector' => 'Bebidas',
                'contacto_nombre' => 'Fernando León', 'contacto_cargo' => 'Gerente de Planta',
            ],
            [
                'razon_social' => 'Plastiformas Industriales S.A.',
                'rfc' => 'PFI-678901-PQR', 'ciudad' => 'Puebla',
                'estado' => 'Puebla', 'sector' => 'Industrial',
                'contacto_nombre' => 'Diana Osorio', 'contacto_cargo' => 'Analista de Proveedores',
            ],
            [
                'razon_social' => 'Productos de Higiene del Hogar S.A.',
                'rfc' => 'PHH-789012-STU', 'ciudad' => 'Toluca',
                'estado' => 'Estado de México', 'sector' => 'Hogar',
                'contacto_nombre' => 'Raúl Méndez', 'contacto_cargo' => 'Comprador Senior',
            ],
            [
                'razon_social' => 'Farmacéutica del Valle S.A.',
                'rfc' => 'FDV-890123-VWX', 'ciudad' => 'Tlalnepantla',
                'estado' => 'Estado de México', 'sector' => 'Farmacéutico',
                'contacto_nombre' => 'Cristina Rojas', 'contacto_cargo' => 'Directora de Suministros',
            ],
            [
                'razon_social' => 'Agroenvases del Bajío S.A.',
                'rfc' => 'AGB-901234-YZA', 'ciudad' => 'Irapuato',
                'estado' => 'Guanajuato', 'sector' => 'Agroindustrial',
                'contacto_nombre' => 'Héctor Pacheco', 'contacto_cargo' => 'Gerente de Logística',
            ],
            [
                'razon_social' => 'Maquinados y Plásticos Especializados S.A.',
                'rfc' => 'MPE-012345-BCD', 'ciudad' => 'San Luis Potosí',
                'estado' => 'San Luis Potosí', 'sector' => 'Maquinaria',
                'contacto_nombre' => 'Adriana Flores', 'contacto_cargo' => 'Dueña',
            ],
            [
                'razon_social' => 'Electrodomésticos del Hogar S.A.',
                'rfc' => 'EDH-112233-EFG', 'ciudad' => 'CDMX',
                'estado' => 'CDMX', 'sector' => 'Hogar',
                'contacto_nombre' => 'Miguel Ángel Peña', 'contacto_cargo' => 'Coordinador de Proyectos',
            ],
            [
                'razon_social' => 'Conservera del Pacífico S.A.',
                'rfc' => 'CPA-223344-HIJ', 'ciudad' => 'Mazatlán',
                'estado' => 'Sinaloa', 'sector' => 'Alimentos',
                'contacto_nombre' => 'Silvia Carrillo', 'contacto_cargo' => 'Jefa de Calidad',
            ],
            [
                'razon_social' => 'Sistemas de Empaque Inteligente S.A.',
                'rfc' => 'SEI-334455-KLM', 'ciudad' => 'León',
                'estado' => 'Guanajuato', 'sector' => 'Tecnología',
                'contacto_nombre' => 'Óscar Villanueva', 'contacto_cargo' => 'CEO',
            ],
            [
                'razon_social' => 'Construplast Materiales S.A.',
                'rfc' => 'CMP-445566-NOP', 'ciudad' => 'Monterrey',
                'estado' => 'Nuevo León', 'sector' => 'Construcción',
                'contacto_nombre' => 'Patricio Solís', 'contacto_cargo' => 'Ingeniero Residente',
            ],
            [
                'razon_social' => 'Lubricantes y Aditivos del Norte S.A.',
                'rfc' => 'LAN-556677-QRS', 'ciudad' => 'Saltillo',
                'estado' => 'Coahuila', 'sector' => 'Automotriz',
                'contacto_nombre' => 'Teresa de la Garza', 'contacto_cargo' => 'Gerente de Adquisiciones',
            ],
        ];

        foreach ($clients as $c) {
            $domNum = rand(100, 999);
            $cp = str_pad((string)rand(10000, 99999), 5, '0', STR_PAD_LEFT);
            $phone = '555-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $email = strtolower(str_replace([' ', '.', ',', 'á', 'é', 'í', 'ó', 'ú'], ['', '', '', 'a', 'e', 'i', 'o', 'u'], $c['contacto_nombre'])) . '@' . strtolower(str_replace(' ', '', $c['razon_social'])) . '.com';
            $email = preg_replace('/[^a-z0-9@.]/', '', $email);
            $correoEmpresa = 'compras@' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $c['razon_social'])[0])) . '.com';

            $regimenes = ['601', '603', '605', '606', '608'];
            $usos = ['G01', 'G03', 'I01', 'I03', 'D01'];

            $this->insert('clientes', [
                'razon_social' => $c['razon_social'],
                'rfc' => $c['rfc'],
                'ciudad' => $c['ciudad'],
                'estado' => $c['estado'],
                'domicilio' => 'Calle ' . $c['sector'] . ' #' . $domNum . ', Col. Industrial',
                'codigo_postal' => $cp,
                'contacto_nombre' => $c['contacto_nombre'],
                'contacto_cargo' => $c['contacto_cargo'],
                'contacto_telefono' => $phone,
                'contacto_correo' => $email,
                'regimen_fiscal' => $regimenes[array_rand($regimenes)],
                'uso_cfdi' => $usos[array_rand($usos)],
                'telefono' => $phone,
                'correo' => $correoEmpresa,
                'correo_fiscal' => 'facturas@' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $c['razon_social'])[0])) . '.com',
                'id_vendedor' => $vendedorIds[array_rand($vendedorIds)],
                'sector' => $c['sector'],
                'activo' => 1,
            ]);
        }
    }
}

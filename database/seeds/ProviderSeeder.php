<?php
namespace Database\Seeds;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'razon_social' => 'Petroquímica Nacional S.A.',
                'rfc' => 'PQA-111111-AAA', 'tipo_material' => 'Polipropileno',
                'ciudad' => 'Monterrey', 'estado' => 'Nuevo León', 'pais' => 'México',
            ],
            [
                'razon_social' => 'Polímeros de México S.A. de C.V.',
                'rfc' => 'PMX-222222-BBB', 'tipo_material' => 'Polietileno',
                'ciudad' => 'Altamira', 'estado' => 'Tamaulipas', 'pais' => 'México',
            ],
            [
                'razon_social' => 'Resinas y Aditivos S.A.',
                'rfc' => 'RYA-333333-CCC', 'tipo_material' => 'Resinas',
                'ciudad' => 'Querétaro', 'estado' => 'Querétaro', 'pais' => 'México',
            ],
            [
                'razon_social' => 'Masterbatches de Occidente S.A.',
                'rfc' => 'MDO-444444-DDD', 'tipo_material' => 'Masterbatch',
                'ciudad' => 'Guadalajara', 'estado' => 'Jalisco', 'pais' => 'México',
            ],
            [
                'razon_social' => 'Química Básica Industrial S.A.',
                'rfc' => 'QBI-555555-EEE', 'tipo_material' => 'Aditivos',
                'ciudad' => 'Tlaxcala', 'estado' => 'Tlaxcala', 'pais' => 'México',
            ],
            [
                'razon_social' => 'Embalajes y Empaques del Norte S.A.',
                'rfc' => 'EEN-666666-FFF', 'tipo_material' => 'Empaques',
                'ciudad' => 'Chihuahua', 'estado' => 'Chihuahua', 'pais' => 'México',
            ],
            [
                'razon_social' => 'Suministros Plásticos Especializados S.A.',
                'rfc' => 'SPE-777777-GGG', 'tipo_material' => 'Policarbonato',
                'ciudad' => 'Tijuana', 'estado' => 'Baja California', 'pais' => 'México',
            ],
            [
                'razon_social' => 'Nylon y Poliamidas de México S.A.',
                'rfc' => 'NPM-888888-HHH', 'tipo_material' => 'Nylon',
                'ciudad' => 'Celaya', 'estado' => 'Guanajuato', 'pais' => 'México',
            ],
            [
                'razon_social' => 'ABS y Termoplásticos S.A.',
                'rfc' => 'AYT-999999-III', 'tipo_material' => 'ABS',
                'ciudad' => 'San Luis Potosí', 'estado' => 'San Luis Potosí', 'pais' => 'México',
            ],
            [
                'razon_social' => 'Cartones y Embalajes Industriales S.A.',
                'rfc' => 'CEI-000000-JJJ', 'tipo_material' => 'Empaques',
                'ciudad' => 'CDMX', 'estado' => 'CDMX', 'pais' => 'México',
            ],
        ];

        foreach ($providers as $p) {
            $phone = '555-' . str_pad((string)rand(2000, 2999), 4, '0', STR_PAD_LEFT);
            $correo = 'ventas@' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', $p['razon_social'])[0])) . '.com';

            $this->insert('proveedores', [
                'razon_social' => $p['razon_social'],
                'rfc' => $p['rfc'],
                'tipo_material' => $p['tipo_material'],
                'telefono' => $phone,
                'correo' => $correo,
                'ciudad' => $p['ciudad'],
                'estado' => $p['estado'],
                'pais' => $p['pais'],
                'sector' => 'Plásticos',
                'estatus' => 'activo',
            ]);
        }
    }
}

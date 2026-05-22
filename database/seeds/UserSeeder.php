<?php
namespace Database\Seeds;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = $this->db->fetchAll("SELECT id_rol, nombre FROM roles");
        $rol = [];
        foreach ($roles as $r) {
            $rol[$r['nombre']] = $r['id_rol'];
        }

        $existingUsers = $this->db->fetchAll("SELECT nombre_usuario FROM usuarios");
        $existingUsernames = array_column($existingUsers, 'nombre_usuario');

        $internalUsers = [
            [
                'username' => 'operador1',
                'nombre' => 'Luis',
                'apellido_paterno' => 'Torres',
                'apellido_materno' => 'Ramírez',
                'puesto' => 'Operador de Inyección',
                'departamento' => 'Producción',
                'telefono' => '555-1010',
                'correo' => 'luis.torres@plastifrus.com',
                'id_rol' => $rol['Operador'],
            ],
            [
                'username' => 'operador2',
                'nombre' => 'Carmen',
                'apellido_paterno' => 'Ruiz',
                'apellido_materno' => 'Morales',
                'puesto' => 'Operador de Inyección',
                'departamento' => 'Producción',
                'telefono' => '555-1011',
                'correo' => 'carmen.ruiz@plastifrus.com',
                'id_rol' => $rol['Operador'],
            ],
            [
                'username' => 'operador3',
                'nombre' => 'José',
                'apellido_paterno' => 'Hernández',
                'apellido_materno' => 'Vega',
                'puesto' => 'Operador de Inyección',
                'departamento' => 'Producción',
                'telefono' => '555-1012',
                'correo' => 'jose.hernandez@plastifrus.com',
                'id_rol' => $rol['Operador'],
            ],
            [
                'username' => 'calidad1',
                'nombre' => 'Sofía',
                'apellido_paterno' => 'Mendoza',
                'apellido_materno' => 'Ríos',
                'puesto' => 'Inspector de Calidad',
                'departamento' => 'Calidad',
                'telefono' => '555-1013',
                'correo' => 'sofia.mendoza@plastifrus.com',
                'id_rol' => $rol['Supervisor'],
            ],
            [
                'username' => 'vendedor1',
                'nombre' => 'Gabriela',
                'apellido_paterno' => 'Navarro',
                'apellido_materno' => 'Luna',
                'puesto' => 'Ejecutiva de Ventas',
                'departamento' => 'Ventas',
                'telefono' => '555-1014',
                'correo' => 'gabriela.navarro@plastifrus.com',
                'id_rol' => $rol['Vendedor'],
            ],
            [
                'username' => 'vendedor2',
                'nombre' => 'Ricardo',
                'apellido_paterno' => 'Mata',
                'apellido_materno' => 'Cruz',
                'puesto' => 'Ejecutivo de Ventas',
                'departamento' => 'Ventas',
                'telefono' => '555-1015',
                'correo' => 'ricardo.mata@plastifrus.com',
                'id_rol' => $rol['Vendedor'],
            ],
        ];

        foreach ($internalUsers as $u) {
            if (in_array($u['username'], $existingUsernames)) {
                continue;
            }

            $empId = $this->insert('empleados', [
                'nombre' => $u['nombre'],
                'apellido_paterno' => $u['apellido_paterno'],
                'apellido_materno' => $u['apellido_materno'],
                'puesto' => $u['puesto'],
                'departamento' => $u['departamento'],
                'telefono' => $u['telefono'],
                'correo' => $u['correo'],
                'fecha_contratacion' => date('Y-m-d', strtotime('-'.rand(1, 365).' days')),
                'estatus' => 'activo',
            ]);

            $this->insert('usuarios', [
                'id_empleado' => $empId,
                'id_cliente' => null,
                'nombre_usuario' => $u['username'],
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'id_rol' => $u['id_rol'],
                'activo' => 1,
            ]);
        }

        $clientUsers = [
            ['username' => 'cliente1', 'razon_social' => 'Industrias Metálicas del Bajío S.A. de C.V.', 'rfc' => 'IMB-890123-XYZ', 'ciudad' => 'León', 'estado' => 'Guanajuato'],
            ['username' => 'cliente2', 'razon_social' => 'Comercializadora de Plásticos del Sur S.A.', 'rfc' => 'CPS-456789-MNO', 'ciudad' => 'Villahermosa', 'estado' => 'Tabasco'],
            ['username' => 'cliente3', 'razon_social' => 'Empaques y Envases del Pacífico S.A.', 'rfc' => 'EEP-345678-QRS', 'ciudad' => 'Guadalajara', 'estado' => 'Jalisco'],
        ];

        foreach ($clientUsers as $cu) {
            if (in_array($cu['username'], $existingUsernames)) {
                continue;
            }

            $clienteId = $this->insert('clientes', [
                'razon_social' => $cu['razon_social'],
                'rfc' => $cu['rfc'],
                'ciudad' => $cu['ciudad'],
                'estado' => $cu['estado'],
                'domicilio' => 'Av. Principal ' . rand(100, 999) . ', Col. Centro',
                'codigo_postal' => str_pad((string)rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                'contacto_nombre' => 'Contacto ' . $cu['username'],
                'contacto_telefono' => '555-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'contacto_correo' => $cu['username'] . '@correo.com',
                'telefono' => '555-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'correo' => $cu['username'] . '@empresa.com',
                'id_vendedor' => rand(6, 7),
                'sector' => 'Industrial',
                'activo' => 1,
            ]);

            $this->insert('usuarios', [
                'id_empleado' => null,
                'id_cliente' => $clienteId,
                'nombre_usuario' => $cu['username'],
                'password_hash' => password_hash('password', PASSWORD_DEFAULT),
                'id_rol' => 5,
                'activo' => 1,
            ]);
        }
    }
}

<?php
namespace Database\Seeds;

class DatabaseSeeder
{
    private array $seeders = [];
    private bool $fresh;

    public function __construct(bool $fresh = false)
    {
        $this->fresh = $fresh;
        $this->register();
    }

    protected function register(): void
    {
        $this->seeders = [
            UserSeeder::class,
            ClientSeeder::class,
            ProviderSeeder::class,
            MaterialSeeder::class,
            ProductSeeder::class,
            ProductionOrderSeeder::class,
            InventoryMovementSeeder::class,
            AuditLogSeeder::class,
            SaleSeeder::class,
        ];
    }

    public function run(): void
    {
        if ($this->fresh) {
            $this->truncateAll();
        }

        echo "Iniciando carga de datos de demostracion...\n\n";
        foreach ($this->seeders as $seederClass) {
            $shortName = (new \ReflectionClass($seederClass))->getShortName();
            echo "  -> {$shortName}... ";
            try {
                $seeder = new $seederClass();
                $seeder->run();
                echo "OK\n";
            } catch (\Throwable $e) {
                echo "Error: {$e->getMessage()}\n";
            }
        }
        echo "\nCarga de datos de demostracion completada.\n";
    }

    private function truncateAll(): void
    {
        $tables = [
            'audit_log', 'kardex_materiales', 'ordenes_cabecera',
            'ventas', 'bitacora_produccion', 'asignar_operador',
            'productos', 'materiales', 'proveedores', 'clientes',
            'usuarios', 'empleados',
        ];

        $conn = \App\Core\Database::getInstance()->getConnection();
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

        foreach ($tables as $table) {
            try {
                $conn->exec("TRUNCATE TABLE {$table}");
            } catch (\Throwable $e) {
                echo "  [!] No se pudo truncar {$table}: {$e->getMessage()}\n";
            }
        }

        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
}

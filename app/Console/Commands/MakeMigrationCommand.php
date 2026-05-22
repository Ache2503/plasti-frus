<?php
namespace App\Console\Commands;

class MakeMigrationCommand extends BaseCommand {
    public function execute(array $args): void {
        if (empty($args[0])) {
            $this->error('Uso: php plasti make:migration <MigrationName>');
            return;
        }
        
        $name = $args[0];
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $className = '';
        foreach (explode('_', $name) as $part) {
            $className .= ucfirst($part);
        }
        
        $migrationsDir = __DIR__ . '/../../../database/migrations';
        if (!is_dir($migrationsDir)) mkdir($migrationsDir, 0755, true);
        $filePath = $migrationsDir . '/' . $filename;
        
        $stub = <<<PHP
<?php
namespace App\Database\Migrations;

use App\Core\Migration;

class {$className} extends Migration
{
    public function up(): void
    {
        // \$this->schema("CREATE TABLE ...");
    }
    
    public function down(): void
    {
        // \$this->schema("DROP TABLE IF EXISTS ...");
    }
}

PHP;
        
        file_put_contents($filePath, $stub);
        $this->info("Migration created: {$filePath}");
    }
}

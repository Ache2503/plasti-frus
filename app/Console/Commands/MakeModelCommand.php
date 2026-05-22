<?php
namespace App\Console\Commands;

class MakeModelCommand extends BaseCommand {
    public function execute(array $args): void {
        if (empty($args[0])) {
            $this->error('Uso: php plasti make:model <ModelName>');
            return;
        }
        
        $name = $args[0];
        $table = $args[1] ?? strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name)) . 's';
        $filePath = __DIR__ . '/../../Models/' . $name . '.php';
        
        $stub = <<<PHP
<?php
namespace App\Models;

use App\Core\Model;

class {$name} extends Model
{
    protected \$table = '{$table}';
    protected \$primaryKey = 'id';
}

PHP;
        
        file_put_contents($filePath, $stub);
        $this->info("Model created: {$filePath}");
    }
}

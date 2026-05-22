<?php
namespace App\Console\Commands;

class MakeControllerCommand extends BaseCommand {
    public function execute(array $args): void {
        if (empty($args[0])) {
            $this->error('Uso: php plasti make:controller <Module/ControllerName>');
            return;
        }
        
        $name = $args[0];
        $parts = explode('/', $name);
        $className = array_pop($parts) . 'Controller';
        $module = implode('\\', $parts);
        $namespace = $module ? "App\\Http\\Controllers\\{$module}" : "App\\Http\\Controllers";
        $dir = __DIR__ . '/../../Http/Controllers/' . str_replace('\\', '/', $module);
        $filePath = $dir . '/' . $className . '.php';
        
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        
        $stub = <<<PHP
<?php
namespace {$namespace};

use App\Core\Controller;

class {$className} extends Controller
{
    public function index(): void
    {
        \$this->view('{$module}.index', ['pageTitle' => '{$className}']);
    }
}

PHP;
        
        file_put_contents($filePath, $stub);
        $this->info("Controller created: {$filePath}");
    }
}

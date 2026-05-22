<?php
namespace App\Console\Commands;

class CommandRegistry {
    private array $commands = [];
    
    public function __construct() {
        $this->register('list', ListCommand::class);
        $this->register('make:controller', MakeControllerCommand::class);
        $this->register('make:model', MakeModelCommand::class);
        $this->register('make:migration', MakeMigrationCommand::class);
        $this->register('migrate', MigrateCommand::class);
        $this->register('migrate:rollback', MigrateRollbackCommand::class);
        $this->register('migrate:reset', MigrateResetCommand::class);
        $this->register('cache:config', CacheConfigCommand::class);
    }
    
    public function register(string $name, string $class): void {
        $this->commands[$name] = $class;
    }
    
    public function run(array $argv): void {
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }
        
        $command = $argv[1];
        $args = array_slice($argv, 2);
        
        if ($command === 'list' || $command === 'help') {
            $this->showHelp();
            return;
        }
        
        if (!isset($this->commands[$command])) {
            echo "Comando no encontrado: {$command}\n";
            $this->showHelp();
            return;
        }
        
        $class = $this->commands[$command];
        $instance = new $class();
        $instance->execute($args);
    }
    
    private function showHelp(): void {
        echo "PlastiFrus CLI Tool\n";
        echo "==================\n\n";
        echo "Comandos disponibles:\n";
        foreach ($this->commands as $name => $class) {
            echo "  php plasti {$name}\n";
        }
        echo "\n";
    }
}

<?php
namespace App\Console\Commands;

class ListCommand extends BaseCommand {
    public function execute(array $args): void {
        echo implode('', array_slice(file(__DIR__ . '/CommandRegistry.php'), -2, 1));
        $this->info('Lista de comandos disponible via: php plasti list');
    }
}

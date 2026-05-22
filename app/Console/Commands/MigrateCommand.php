<?php
namespace App\Console\Commands;

use App\Console\Migrator;

class MigrateCommand extends BaseCommand {
    public function execute(array $args): void {
        try {
            $migrator = new Migrator();
            $migrator->run();
            $this->info('Migrations completed.');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}

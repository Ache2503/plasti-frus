<?php
namespace App\Console\Commands;

use App\Console\Migrator;

class MigrateResetCommand extends BaseCommand {
    public function execute(array $args): void {
        try {
            $migrator = new Migrator();
            $migrator->reset();
            $this->info('All migrations rolled back.');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}

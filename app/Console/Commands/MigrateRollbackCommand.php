<?php
namespace App\Console\Commands;

use App\Console\Migrator;

class MigrateRollbackCommand extends BaseCommand {
    public function execute(array $args): void {
        try {
            $migrator = new Migrator();
            $migrator->rollback();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}

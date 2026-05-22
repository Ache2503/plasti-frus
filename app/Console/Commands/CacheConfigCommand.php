<?php
namespace App\Console\Commands;

class CacheConfigCommand extends BaseCommand {
    public function execute(array $args): void {
        $configDir = __DIR__ . '/../../../config';
        $cacheDir = __DIR__ . '/../../../storage/cache';
        
        if (!is_dir($cacheDir)) mkdir($cacheDir, 0775, true);
        
        $config = [];
        foreach (glob($configDir . '/*.php') as $file) {
            $key = basename($file, '.php');
            $config[$key] = require $file;
        }
        
        $content = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        file_put_contents($cacheDir . '/config.php', $content);
        $this->info('Configuration cached successfully.');
    }
}

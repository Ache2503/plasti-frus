<?php
namespace App\Providers;

use App\Core\Database;

class AppServiceProvider
{
    private static array $instances = [];

    public static function register(): void
    {
        self::$instances['db'] = Database::getInstance();
    }

    public static function get(string $abstract)
    {
        return self::$instances[$abstract] ?? null;
    }

    public static function singleton(string $abstract, callable $factory): void
    {
        if (!isset(self::$instances[$abstract])) {
            self::$instances[$abstract] = $factory();
        }
    }
}

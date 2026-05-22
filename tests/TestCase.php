<?php
namespace Tests;

use App\Core\Database;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static ?Database $db = null;

    protected function setUp(): void
    {
        parent::setUp();
        if (!self::$db) {
            self::$db = Database::getInstance();
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        self::$db->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (self::$db) {
            self::$db->rollback();
        }
        parent::tearDown();
    }

    protected function db(): Database
    {
        return self::$db;
    }

    protected function createTestUser(array $overrides = []): array
    {
        $data = array_merge([
            'nombre_usuario' => 'test_' . uniqid(),
            'password_hash' => password_hash('test123456', PASSWORD_DEFAULT),
            'id_rol' => 1,
            'activo' => 1,
        ], $overrides);
        $data['id_usuario'] = $this->db()->insert('usuarios', $data);
        return $data;
    }
}

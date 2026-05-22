<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AuthService;

class RegisterTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_can_register_client_user(): void
    {
        $idCliente = $this->db()->insert('clientes', [
            'razon_social' => 'Test Client ' . uniqid(),
            'rfc' => 'XAXX010101000',
            'activo' => 1,
        ]);

        $username = 'client_' . uniqid();
        $userId = $this->authService->registerCliente($username, 'securepass123', $idCliente);

        $this->assertIsInt($userId);
        $this->assertGreaterThan(0, $userId);

        $user = $this->db()->fetchOne(
            "SELECT * FROM usuarios WHERE id_usuario = :id",
            ['id' => $userId]
        );
        $this->assertNotNull($user);
        $this->assertEquals($username, $user['nombre_usuario']);
        $this->assertEquals(5, $user['id_rol']);
        $this->assertEquals($idCliente, $user['id_cliente']);
        $this->assertTrue(password_verify('securepass123', $user['password_hash']));
    }

    public function test_duplicate_username_fails(): void
    {
        $username = 'duplicate_' . uniqid();
        $idCliente = $this->db()->insert('clientes', [
            'razon_social' => 'Test Client ' . uniqid(),
            'rfc' => 'XAXX010101000',
            'activo' => 1,
        ]);

        $this->authService->registerCliente($username, 'password123', $idCliente);

        $this->expectException(\PDOException::class);
        $this->authService->registerCliente($username, 'otherpass456', $idCliente);
    }

    public function test_password_is_hashed(): void
    {
        $idCliente = $this->db()->insert('clientes', [
            'razon_social' => 'Hash Test ' . uniqid(),
            'rfc' => 'XAXX010101000',
            'activo' => 1,
        ]);

        $userId = $this->authService->registerCliente('hash_' . uniqid(), 'plaintext123', $idCliente);
        $user = $this->db()->fetchOne("SELECT * FROM usuarios WHERE id_usuario = :id", ['id' => $userId]);

        $this->assertNotEquals('plaintext123', $user['password_hash']);
        $this->assertStringStartsWith('$2y$', $user['password_hash']);
    }
}

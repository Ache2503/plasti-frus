<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AuthService;

class LoginTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_valid_user_can_login(): void
    {
        $user = $this->createTestUser([
            'nombre_usuario' => 'testuser_' . uniqid(),
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'id_rol' => 1,
        ]);

        $result = $this->authService->login($user['nombre_usuario'], 'password123');

        $this->assertNotNull($result);
        $this->assertEquals($user['id_usuario'], $result['id_usuario']);
        $this->assertEquals(1, $result['id_rol']);
    }

    public function test_invalid_user_fails(): void
    {
        $result = $this->authService->login('nonexistent_user_' . uniqid(), 'password123');
        $this->assertNull($result);
    }

    public function test_wrong_password_fails(): void
    {
        $user = $this->createTestUser([
            'nombre_usuario' => 'testuser_' . uniqid(),
            'password_hash' => password_hash('correctpassword', PASSWORD_DEFAULT),
            'id_rol' => 1,
        ]);

        $result = $this->authService->login($user['nombre_usuario'], 'wrongpassword');
        $this->assertNull($result);
    }

    public function test_session_set_after_login(): void
    {
        $user = $this->createTestUser([
            'nombre_usuario' => 'testsess_' . uniqid(),
            'password_hash' => password_hash('session123', PASSWORD_DEFAULT),
            'id_rol' => 1,
        ]);

        $this->authService->login($user['nombre_usuario'], 'session123');

        $this->assertEquals($user['id_usuario'], $_SESSION['user_id']);
        $this->assertEquals($user['nombre_usuario'], $_SESSION['user_name']);
        $this->assertEquals(1, $_SESSION['user_rol']);
    }

    public function test_logout_clears_session(): void
    {
        $user = $this->createTestUser([
            'nombre_usuario' => 'logouttest_' . uniqid(),
            'password_hash' => password_hash('logout123', PASSWORD_DEFAULT),
            'id_rol' => 1,
        ]);

        $this->authService->login($user['nombre_usuario'], 'logout123');
        $this->assertNotNull($_SESSION['user_id'] ?? null);

        $this->authService->logout();
        $this->assertEmpty($_SESSION);
    }
}

<?php
namespace Tests\Unit\Services;

use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->authService = new AuthService();
    }

    public function testLogoutClearsSession(): void
    {
        $_SESSION['user_id'] = 1;
        $this->authService->logout();
        $this->assertEmpty($_SESSION);
    }
}

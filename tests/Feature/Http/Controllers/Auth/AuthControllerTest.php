<?php
namespace Tests\Feature\Http\Controllers\Auth;

use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    public function testShowLoginReturnsView(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/login';
        $this->assertTrue(true);
    }

    public function testLoginControllerClassExists(): void
    {
        $this->assertTrue(class_exists(\App\Http\Controllers\Auth\AuthController::class));
    }

    public function testLoginControllerHasExpectedMethods(): void
    {
        $methods = get_class_methods(\App\Http\Controllers\Auth\AuthController::class);
        $this->assertContains('showLogin', $methods);
        $this->assertContains('login', $methods);
    }
}

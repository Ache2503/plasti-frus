<?php
namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(\App\Services\AuthService::class));
    }

    public function testHasExpectedMethods(): void
    {
        $methods = get_class_methods(\App\Services\AuthService::class);
        $this->assertContains('login', $methods);
        $this->assertContains('logout', $methods);
    }
}

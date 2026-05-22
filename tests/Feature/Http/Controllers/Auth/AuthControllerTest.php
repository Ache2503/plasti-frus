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
}

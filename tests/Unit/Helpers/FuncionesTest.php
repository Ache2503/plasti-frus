<?php
namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

class FuncionesTest extends TestCase
{
    public function testFormatMoney(): void
    {
        $result = format_money(100.50);
        $this->assertStringContainsString('100.50', $result);
        $this->assertStringContainsString('MXN', $result);
    }

    public function testGenerateFolio(): void
    {
        $folio = generate_folio('TEST');
        $this->assertStringStartsWith('TEST-', $folio);
    }

    public function testSafeString(): void
    {
        $this->assertEquals('&lt;script&gt;', safe_string('<script>'));
    }

    public function testSafeStringWithNull(): void
    {
        $this->assertEquals('', safe_string(null));
    }

    public function testTruncate(): void
    {
        $text = 'Este es un texto largo para truncar';
        $result = truncate($text, 10);
        $this->assertEquals(13, strlen($result));
        $this->assertStringEndsWith('...', $result);
    }

    public function testFormatDate(): void
    {
        $this->assertEquals('01/01/2024', format_date('2024-01-01'));
        $this->assertEquals('', format_date(null));
    }

    public function testFormatDatetime(): void
    {
        $this->assertEquals('01/01/2024 10:30', format_datetime('2024-01-01 10:30:00'));
        $this->assertEquals('', format_datetime(null));
    }

    public function testTimeAgo(): void
    {
        $this->assertEquals('hace unos segundos', time_ago(date('Y-m-d H:i:s')));
        $this->assertEquals('', time_ago(null));
    }

    public function testAsset(): void
    {
        $result = asset('css/app.css');
        $this->assertStringContainsString('css/app.css', $result);
    }

    public function testUrl(): void
    {
        $result = url('test/path');
        $this->assertStringContainsString('test/path', $result);
    }

    public function testCsrfToken(): void
    {
        $token = csrf_token();
        $this->assertNotEmpty($token);
        $this->assertTrue(verify_csrf($token));
    }

    public function testFlashMessage(): void
    {
        set_flash('success', 'Test message');
        $flash = flash_message();
        $this->assertIsArray($flash);
        $this->assertEquals('success', $flash['type']);
        $this->assertEquals('Test message', $flash['message']);
        $this->assertNull(flash_message());
    }
}

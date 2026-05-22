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
}

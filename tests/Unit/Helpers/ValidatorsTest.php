<?php
namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;

class ValidatorsTest extends TestCase
{
    public function testValidateEmail(): void
    {
        $this->assertTrue(validate_email('test@example.com'));
        $this->assertFalse(validate_email('not-an-email'));
    }

    public function testValidateRequired(): void
    {
        $this->assertTrue(validate_required('hello'));
        $this->assertFalse(validate_required(''));
    }

    public function testValidateRfc(): void
    {
        $this->assertTrue(validate_rfc('XAXX010101000'));
        $this->assertFalse(validate_rfc('invalid'));
    }

    public function testValidatePhone(): void
    {
        $this->assertTrue(validate_phone('+525511223344'));
        $this->assertFalse(validate_phone('12'));
    }

    public function testValidatePositive(): void
    {
        $this->assertTrue(validate_positive(10));
        $this->assertFalse(validate_positive(-1));
        $this->assertFalse(validate_positive(0));
    }
}

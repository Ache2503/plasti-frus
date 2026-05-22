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

    public function testValidateNumeric(): void
    {
        $this->assertTrue(validate_numeric(42));
        $this->assertTrue(validate_numeric('3.14'));
        $this->assertFalse(validate_numeric('not-a-number'));
    }

    public function testValidateMinLength(): void
    {
        $this->assertTrue(validate_min_length('hello', 3));
        $this->assertFalse(validate_min_length('hi', 3));
    }

    public function testValidateMaxLength(): void
    {
        $this->assertTrue(validate_max_length('hi', 5));
        $this->assertFalse(validate_max_length('hello world', 5));
    }

    public function testValidateRange(): void
    {
        $this->assertTrue(validate_range(5, 1, 10));
        $this->assertFalse(validate_range(15, 1, 10));
    }

    public function testValidateUrl(): void
    {
        $this->assertTrue(validate_url('https://example.com'));
        $this->assertFalse(validate_url('not-a-url'));
    }

    public function testValidateDate(): void
    {
        $this->assertTrue(validate_date('2024-01-01'));
        $this->assertFalse(validate_date('not-a-date'));
    }

    public function testValidateAlpha(): void
    {
        $this->assertTrue(validate_alpha('Sólo letras'));
        $this->assertFalse(validate_alpha('123'));
    }

    public function testValidateAlphanumeric(): void
    {
        $this->assertTrue(validate_alphanumeric('Letras y 123'));
        $this->assertFalse(validate_alphanumeric('@#$'));
    }

    public function testValidateDecimal(): void
    {
        $this->assertTrue(validate_decimal(10.5));
        $this->assertTrue(validate_decimal(10));
        $this->assertFalse(validate_decimal(10.555));
    }

    public function testValidateLength(): void
    {
        $this->assertTrue(validate_length('abc', 3));
        $this->assertFalse(validate_length('abcd', 3));
    }

    public function testValidateEstatus(): void
    {
        $this->assertTrue(validate_estatus('activo'));
        $this->assertTrue(validate_estatus('pendiente'));
        $this->assertFalse(validate_estatus('inexistente'));
    }

    public function testValidate(): void
    {
        $errors = validate(['email' => 'invalid'], ['email' => 'email']);
        $this->assertNotEmpty($errors);

        $errors = validate(['name' => 'John'], ['name' => 'required']);
        $this->assertEmpty($errors);
    }
}

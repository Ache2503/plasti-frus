<?php
namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class MailServiceTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(\App\Services\MailService::class));
    }

    public function testHasExpectedMethods(): void
    {
        $methods = get_class_methods(\App\Services\MailService::class);
        $this->assertContains('send', $methods);
    }
}

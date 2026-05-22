<?php
namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class AuditServiceTest extends TestCase
{
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(\App\Services\AuditService::class));
    }

    public function testHasExpectedMethods(): void
    {
        $methods = get_class_methods(\App\Services\AuditService::class);
        $this->assertContains('log', $methods);
        $this->assertContains('getAll', $methods);
    }
}

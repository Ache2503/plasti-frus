<?php
namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class ExportServiceTest extends TestCase
{
    public function testConstructor(): void
    {
        $service = new \App\Services\ExportService('Test', ['Col1', 'Col2'], [['a', 'b']]);
        $this->assertInstanceOf(\App\Services\ExportService::class, $service);
    }
}

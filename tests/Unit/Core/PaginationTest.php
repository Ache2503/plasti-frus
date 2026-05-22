<?php
namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    public function testSinglePage(): void
    {
        $p = new \App\Core\Pagination([1, 2, 3], 3, 1, 10);
        $this->assertFalse($p->hasPages());
        $this->assertFalse($p->hasPrevious());
        $this->assertFalse($p->hasNext());
    }

    public function testMultiplePages(): void
    {
        $p = new \App\Core\Pagination(range(1, 10), 25, 1, 10);
        $this->assertTrue($p->hasPages());
        $this->assertFalse($p->hasPrevious());
        $this->assertTrue($p->hasNext());
        $this->assertEquals(3, $p->lastPage);
    }

    public function testMiddlePage(): void
    {
        $p = new \App\Core\Pagination(range(1, 10), 25, 2, 10);
        $this->assertTrue($p->hasPrevious());
        $this->assertTrue($p->hasNext());
    }

    public function testLastPage(): void
    {
        $p = new \App\Core\Pagination(range(1, 5), 25, 3, 10);
        $this->assertTrue($p->hasPrevious());
        $this->assertFalse($p->hasNext());
    }

    public function testRender(): void
    {
        $p = new \App\Core\Pagination(range(1, 10), 25, 2, 10);
        $html = $p->render();
        $this->assertStringContainsString('nav', $html);
        $this->assertStringContainsString('Siguiente', $html);
        $this->assertStringContainsString('Anterior', $html);
    }

    public function testEmptyWhenNoPages(): void
    {
        $p = new \App\Core\Pagination([1], 1, 1, 10);
        $this->assertEquals('', $p->render());
    }

    public function testFromAndTo(): void
    {
        $p = new \App\Core\Pagination(range(1, 10), 25, 2, 10);
        $this->assertEquals(11, $p->from);
        $this->assertEquals(20, $p->to);
    }
}

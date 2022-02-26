<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use PHPUnit\Framework\TestCase;

final class AbstractModuleTest extends TestCase
{
    /**
     * @var DummyModule
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new DummyModule();
    }

    /**
     * @test
     */
    public function getPageDataInitiallyReturnsEmptyArray(): void
    {
        self::assertSame([], $this->subject->getPageData());
    }

    /**
     * @test
     */
    public function getPageDataReturnsCompleteDataSetViaSetPageData(): void
    {
        $this->subject->setPageData(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $this->subject->getPageData());
    }
}

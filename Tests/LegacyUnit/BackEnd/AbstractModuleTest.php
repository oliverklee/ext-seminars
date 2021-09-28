<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;

final class AbstractModuleTest extends TestCase
{
    /**
     * @var DummyModule
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new DummyModule();
    }

    /**
     * @test
     */
    public function getPageDataInitiallyReturnsEmptyArray()
    {
        self::assertSame([], $this->subject->getPageData());
    }

    /**
     * @test
     */
    public function getPageDataReturnsCompleteDataSetViaSetPageData()
    {
        $this->subject->setPageData(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $this->subject->getPageData());
    }
}

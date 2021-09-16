<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BackEnd;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
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
        /** @var ConfigurationProxy $configuration */
        $configuration = ConfigurationProxy::getInstance('seminars');
        $configuration->setAsBoolean('enableConfigCheck', false);

        $this->subject = new DummyModule();
    }

    public function testGetPageDataInitiallyReturnsEmptyArray()
    {
        self::assertSame([], $this->subject->getPageData());
    }

    public function testGetPageDataReturnsCompleteDataSetViaSetPageData()
    {
        $this->subject->setPageData(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $this->subject->getPageData());
    }
}

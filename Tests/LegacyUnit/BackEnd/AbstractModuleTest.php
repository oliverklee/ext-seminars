<?php

use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_AbstractModuleTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var DummyModule
     */
    private $subject = null;

    protected function setUp()
    {
        \Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);

        $this->subject = new DummyModule();
    }

    public function testGetPageDataInitiallyReturnsEmptyArray()
    {
        static::assertSame([], $this->subject->getPageData());
    }

    public function testGetPageDataReturnsCompleteDataSetViaSetPageData()
    {
        $this->subject->setPageData(['foo' => 'bar']);

        static::assertSame(['foo' => 'bar'], $this->subject->getPageData());
    }
}

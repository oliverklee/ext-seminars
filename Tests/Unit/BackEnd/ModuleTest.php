<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_BackEnd_ModuleTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_BackEnd_Module
     */
    private $fixture;

    protected function setUp()
    {
        \Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', false);

        $this->fixture = new Tx_Seminars_BackEnd_Module();
    }

    ////////////////////////////////////////////////
    // Tests for getting and setting the page data
    ////////////////////////////////////////////////

    public function testGetPageDataInitiallyReturnsEmptyArray()
    {
        self::assertEquals(
            [],
            $this->fixture->getPageData()
        );
    }

    public function testGetPageDataReturnsCompleteDataSetViaSetPageData()
    {
        $this->fixture->setPageData(['foo' => 'bar']);

        self::assertEquals(
            ['foo' => 'bar'],
            $this->fixture->getPageData()
        );
    }
}

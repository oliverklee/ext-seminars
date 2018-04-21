<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_ConfigCheckTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_ConfigCheck
     */
    private $fixture;

    /**
     * @var Tx_Oelib_Tests_Unit_Fixtures_DummyObjectToCheck
     */
    private $objectToCheck = null;

    protected function setUp()
    {
        Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', true);

        $this->objectToCheck = new Tx_Oelib_Tests_Unit_Fixtures_DummyObjectToCheck([]);
        $this->fixture = new Tx_Seminars_ConfigCheck($this->objectToCheck);
    }

    //////////////////////////////////////
    // Tests concerning checkCurrency().
    //////////////////////////////////////

    /**
     * @test
     */
    public function checkCurrencyWithEmptyStringResultsInConfigCheckMessage()
    {
        $this->objectToCheck->setConfigurationValue('currency', '');
        $this->fixture->checkCurrency();

        self::assertContains(
            'The specified currency setting is either empty or not a valid ' .
                'ISO 4217 alpha 3 code.',
            $this->fixture->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkCurrencyWithInvalidIsoAlpha3CodeResultsInConfigCheckMessage()
    {
        $this->objectToCheck->setConfigurationValue('currency', 'XYZ');
        $this->fixture->checkCurrency();

        self::assertContains(
            'The specified currency setting is either empty or not a valid ' .
                'ISO 4217 alpha 3 code.',
            $this->fixture->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkCurrencyWithValidIsoAlpha3CodeResultsInEmptyConfigCheckMessage()
    {
        $this->objectToCheck->setConfigurationValue('currency', 'EUR');
        $this->fixture->checkCurrency();

        self::assertTrue(
            $this->fixture->getRawMessage() == ''
        );
    }
}

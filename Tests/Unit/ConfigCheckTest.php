<?php
namespace OliverKlee\Seminars\Tests\Unit;

use OliverKlee\Seminars\Tests\Unit\Fixtures\DummyObjectToCheck;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class ConfigCheckTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_ConfigCheck
     */
    private $subject;

    /**
     * @var DummyObjectToCheck
     */
    private $objectToCheck = null;

    protected function setUp()
    {
        \Tx_Oelib_ConfigurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', true);

        $this->objectToCheck = new DummyObjectToCheck([]);
        $this->subject = new \Tx_Seminars_ConfigCheck($this->objectToCheck);
    }

    /*
     * Tests concerning checkCurrency
     */

    /**
     * @test
     */
    public function checkCurrencyWithEmptyStringResultsInConfigCheckMessage()
    {
        $this->objectToCheck->setConfigurationValue('currency', '');

        $this->subject->checkCurrency();

        self::assertContains(
            'The specified currency setting is either empty or not a valid ISO 4217 alpha 3 code.',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkCurrencyWithInvalidIsoAlpha3CodeResultsInConfigCheckMessage()
    {
        $this->objectToCheck->setConfigurationValue('currency', 'XYZ');

        $this->subject->checkCurrency();

        self::assertContains(
            'The specified currency setting is either empty or not a valid ISO 4217 alpha 3 code.',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkCurrencyWithValidIsoAlpha3CodeResultsInEmptyConfigCheckMessage()
    {
        $this->objectToCheck->setConfigurationValue('currency', 'EUR');

        $this->subject->checkCurrency();

        self::assertSame('', $this->subject->getRawMessage());
    }
}

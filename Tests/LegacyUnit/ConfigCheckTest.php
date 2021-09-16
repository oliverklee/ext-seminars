<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\DummyObjectToCheck;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class ConfigCheckTest extends TestCase
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
        /** @var ConfigurationProxy $configuration */
        $configuration = ConfigurationProxy::getInstance('seminars');
        $configuration->setAsBoolean('enableConfigCheck', true);

        $this->objectToCheck = new DummyObjectToCheck([]);
        $this->subject = new \Tx_Seminars_ConfigCheck($this->objectToCheck);
    }

    // Tests concerning checkCurrency

    /**
     * @test
     */
    public function checkCurrencyWithEmptyStringResultsInConfigCheckMessage()
    {
        $this->objectToCheck->setConfigurationValue('currency', '');

        $this->subject->checkCurrency();

        self::assertStringContainsString(
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

        self::assertStringContainsString(
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

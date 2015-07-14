<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_ConfigCheckTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_configcheck
	 */
	private $fixture;

	/**
	 * @var Tx_Oelib_Tests_Unit_Fixtures_DummyObjectToCheck
	 */
	private $objectToCheck = NULL;

	protected function setUp() {
		tx_oelib_configurationProxy::getInstance('seminars')->setAsBoolean('enableConfigCheck', TRUE);

		$this->objectToCheck = new Tx_Oelib_Tests_Unit_Fixtures_DummyObjectToCheck(array());
		$this->fixture = new tx_seminars_configcheck($this->objectToCheck);
	}

	//////////////////////////////////////
	// Tests concerning checkCurrency().
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function checkCurrencyWithEmptyStringResultsInConfigCheckMessage() {
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
	public function checkCurrencyWithInvalidIsoAlpha3CodeResultsInConfigCheckMessage() {
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
	public function checkCurrencyWithValidIsoAlpha3CodeResultsInEmptyConfigCheckMessage() {
		$this->objectToCheck->setConfigurationValue('currency', 'EUR');
		$this->fixture->checkCurrency();

		self::assertTrue(
			$this->fixture->getRawMessage() == ''
		);
	}
}
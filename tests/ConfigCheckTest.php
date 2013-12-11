<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Niels Pardon (mail@niels-pardon.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

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
	 * @var tx_oelib_dummyObjectToCheck dummy object to be checked by the
	 *                                  configuration check object
	 */
	private $objectToCheck;

	public function setUp() {
		$this->objectToCheck = new tx_oelib_dummyObjectToCheck(array());
		$this->fixture = new tx_seminars_configcheck($this->objectToCheck);
	}

	public function tearDown() {
		$this->fixture->__destruct();
		$this->objectToCheck->__destruct();
		unset($this->fixture);
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

		$this->assertContains(
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

		$this->assertContains(
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

		$this->assertTrue(
			$this->fixture->getRawMessage() == ''
		);
	}
}
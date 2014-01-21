<?php
/***************************************************************
 * Copyright notice
*
* (c) 2012 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the tx_seminars_ViewHelper_Countdown class.
*
* @package TYPO3
* @subpackage tx_seminars
*
* @author Niels Pardon <mail@niels-pardon.de>
*/
class tx_seminars_ViewHelper_CountdownTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_ViewHelper_Countdown
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_oelib_Translator
	 */
	private $translator;

	public function setUp() {
		$this->testingFramework	= new tx_oelib_testingFramework('tx_seminars');

		$this->translator = tx_oelib_TranslatorRegistry::getInstance()->get('seminars');

		$this->fixture = new tx_seminars_ViewHelper_Countdown();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework, $this->translator);
	}

	/**
	 * @test
	 */
	public function renderWithBeginDateInThirtySecondsReturnsThirtySecondsLeft() {
		$offset = 30;

		$this->assertSame(
			sprintf(
				$this->translator->translate('message_countdown'),
				$offset,
				$this->translator->translate('countdown_seconds_plural')
			),
			$this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
		);
	}

	/**
	 * @test
	 */
	public function renderWithBeginDateInOneMinuteReturnsOneMinuteLeft() {
		$offset = 60;

		$this->assertSame(
			sprintf(
				$this->translator->translate('message_countdown'),
				$offset,
				$this->translator->translate('countdown_minutes_singular')
			),
			$this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
		);
	}

	/**
	 * @test
	 */
	public function renderWithBeginDateInTwoMinutesReturnsTwoMinutesLeft() {
		$offset = 120;

		$this->assertSame(
			sprintf(
				$this->translator->translate('message_countdown'),
				$offset,
				$this->translator->translate('countdown_minutes_plural')
			),
			$this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
		);
	}

	/**
	 * @test
	 */
	public function renderWithBeginDateInOneHourReturnsOneHourLeft() {
		$offset = 3600;

		$this->assertSame(
			sprintf(
				$this->translator->translate('message_countdown'),
				$offset,
				$this->translator->translate('countdown_hours_singular')
			),
			$this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
		);
	}

	/**
	 * @test
	 */
	public function renderWithBeginDateInTwoHoursReturnsTwoHoursLeft() {
		$offset = 7200;

		$this->assertSame(
			sprintf(
				$this->translator->translate('message_countdown'),
				$offset,
				$this->translator->translate('countdown_hours_plural')
			),
			$this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
		);
	}

	/**
	 * @test
	 */
	public function renderWithBeginDateInOneDayReturnsOneDayLeft() {
		$offset = 86400;

		$this->assertSame(
			sprintf(
				$this->translator->translate('message_countdown'),
				$offset,
				$this->translator->translate('countdown_days_singular')
			),
			$this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
		);
	}

	/**
	 * @test
	 */
	public function renderWithBeginDateInTwoDaysReturnsTwoDaysLeft() {
		$offset = 2*86400;

		$this->assertSame(
			sprintf(
				$this->translator->translate('message_countdown'),
				$offset,
				$this->translator->translate('countdown_days_plural')
			),
			$this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
		);
	}
}
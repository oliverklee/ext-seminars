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

	protected function setUp() {
		$this->testingFramework	= new tx_oelib_testingFramework('tx_seminars');

		$this->translator = tx_oelib_TranslatorRegistry::getInstance()->get('seminars');

		$this->fixture = new tx_seminars_ViewHelper_Countdown();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}

	/**
	 * @test
	 */
	public function renderWithBeginDateInThirtySecondsReturnsThirtySecondsLeft() {
		$offset = 30;

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
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

		self::assertSame(
			sprintf(
				$this->translator->translate('message_countdown'),
				$offset,
				$this->translator->translate('countdown_days_plural')
			),
			$this->fixture->render($GLOBALS['SIM_ACCESS_TIME'] + $offset)
		);
	}
}
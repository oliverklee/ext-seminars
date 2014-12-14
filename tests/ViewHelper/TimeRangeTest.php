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
 * Testcase for the tx_seminars_ViewHelper_TimeRange class.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_ViewHelper_TimeRangeTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_ViewHelper_TimeRange
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_oelib_Configuration
	 */
	private $configuration;

	/**
	 * @var tx_oelib_Translator
	 */
	private $translator;

	/**
	 * @var int some random date (2001-01-01 00:00:00)
	 */
	const BEGIN_DATE = 978303600;

	/**
	 * @var string
	 */
	const TIME_FORMAT = '%H:%M';

	public function setUp() {
		$this->testingFramework	= new tx_oelib_testingFramework('tx_seminars');

		$this->configuration = new tx_oelib_Configuration();
		$this->configuration->setAsString('timeFormat', self::TIME_FORMAT);

		tx_oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

		$this->translator = tx_oelib_TranslatorRegistry::getInstance()->get('seminars');

		$this->fixture = new tx_seminars_ViewHelper_TimeRange();
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework, $this->configuration, $this->translator);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithNoDatesReturnMessageWillBeAnnounced() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setData(array());

		$this->assertSame(
			$this->translator->translate('message_willBeAnnounced'),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginDateWithZeroHoursReturnsMessageWillBeAnnounced() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);

		$this->assertSame(
			$this->translator->translate('message_willBeAnnounced'),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginDateOnlyReturnsTimePortionOfBeginDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);

		$this->assertSame(
			strftime(self::TIME_FORMAT, self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithEqualBeginAndEndTimestampsReturnsOnlyTimePortionOfBeginDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);
		$timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);

		$this->assertSame(
			strftime(self::TIME_FORMAT, self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateReturnsTimePortionsOfBeginDateAndEndDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);
		$timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 2 * tx_oelib_Time::SECONDS_PER_HOUR);

		$this->assertSame(
			strftime(self::TIME_FORMAT, self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR) . '&#8211;' .
				strftime(self::TIME_FORMAT, self::BEGIN_DATE + 2 * tx_oelib_Time::SECONDS_PER_HOUR),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateReturnsTimePortionsOfBeginDateAndEndDateSeparatedBySpecifiedDash() {
		$dash = '#DASH#';
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);
		$timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 2 * tx_oelib_Time::SECONDS_PER_HOUR);

		$this->assertSame(
			strftime(self::TIME_FORMAT, self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR) . $dash .
				strftime(self::TIME_FORMAT, self::BEGIN_DATE + 2 * tx_oelib_Time::SECONDS_PER_HOUR),
			$this->fixture->render($timeSpan, $dash)
		);
	}
}
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
class Tx_Seminars_Tests_Unit_ViewHelper_DateRangeTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Seminars_ViewHelper_DateRange
	 */
	private $fixture;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework;

	/**
	 * @var Tx_Oelib_Configuration
	 */
	private $configuration;

	/**
	 * @var Tx_Oelib_Translator
	 */
	private $translator;

	/**
	 * @var int some random date (2001-01-01 00:00:00)
	 */
	const BEGIN_DATE = 978303600;

	/**
	 * @var string
	 */
	const DATE_FORMAT_YMD = '%d.%m.%Y',
		DATE_FORMAT_Y = '%Y',
		DATE_FORMAT_M = '%m.',
		DATE_FORMAT_MD = '%d.%m.',
		DATE_FORMAT_D = '%d.';

	protected function setUp() {
		$this->testingFramework	= new Tx_Oelib_TestingFramework('tx_seminars');

		$this->configuration = new Tx_Oelib_Configuration();
		$this->configuration->setAsString('dateFormatYMD', self::DATE_FORMAT_YMD);
		$this->configuration->setAsString('dateFormatY', self::DATE_FORMAT_Y);
		$this->configuration->setAsString('dateFormatM', self::DATE_FORMAT_M);
		$this->configuration->setAsString('dateFormatMD', self::DATE_FORMAT_MD);
		$this->configuration->setAsString('dateFormatD', self::DATE_FORMAT_D);

		Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

		$this->translator = Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars');

		$this->fixture = new Tx_Seminars_ViewHelper_DateRange();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithNoDatesReturnMessageWillBeAnnounced() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setData(array());

		self::assertSame(
			$this->translator->translate('message_willBeAnnounced'),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginDateOnlyRendersOnlyBeginDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);

		self::assertSame(
			strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithEqualBeginAndEndDateReturnsOnlyBeginDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
		$timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE);

		self::assertSame(
			strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateOnSameDayReturnsOnlyBeginDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
		$timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 3600);

		self::assertEquals(
			strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateOnDifferentDaysWithAbbreviateDateRangeFalseReturnsBothFullDatesSeparatedByDash() {
		$this->configuration->setAsBoolean('abbreviateDateRanges', FALSE);

		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
		$endDate = self::BEGIN_DATE + (2 * 86400);
		$timeSpan->setEndDateAsUnixTimeStamp($endDate);

		self::assertEquals(
			strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE) . '&#8211;' . strftime(self::DATE_FORMAT_YMD, $endDate),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateOnDifferentDaysButSameMonthWithAbbreviateDateRangeTrueReturnsOnlyDayOfBeginDateAndFullEndDateSeparatedByDash() {
		$this->configuration->setAsBoolean('abbreviateDateRanges', TRUE);

		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
		$endDate = self::BEGIN_DATE + (2 * 86400);
		$timeSpan->setEndDateAsUnixTimeStamp($endDate);

		self::assertEquals(
			strftime(self::DATE_FORMAT_D, self::BEGIN_DATE) . '&#8211;' . strftime(self::DATE_FORMAT_YMD, $endDate),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateOnDifferentMonthWithAbbreviateDateRangeTrueReturnsDayAndMonthOfBeginDateAndFullEndDateSeparatedByDash() {
		$this->configuration->setAsBoolean('abbreviateDateRanges', TRUE);

		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
		$endDate = self::BEGIN_DATE + (32 * 86400);
		$timeSpan->setEndDateAsUnixTimeStamp($endDate);

		self::assertEquals(
			strftime(self::DATE_FORMAT_MD, self::BEGIN_DATE) . '&#8211;' . strftime(self::DATE_FORMAT_YMD, $endDate),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateOnDifferentYearsWithAbbreviateDateRangeTrueReturnsFullBeginDateAndFullEndDateSeparatedByDash() {
		$this->configuration->setAsBoolean('abbreviateDateRanges', TRUE);

		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
		$endDate = self::BEGIN_DATE + (366 * 86400);
		$timeSpan->setEndDateAsUnixTimeStamp($endDate);

		self::assertEquals(
			strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE) . '&#8211;' . strftime(self::DATE_FORMAT_YMD, $endDate),
			$this->fixture->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateOnDifferentDaysWithAbbreviateDateRangeFalseReturnsBothFullDatesSeparatedBySpecifiedDash() {
		$this->configuration->setAsBoolean('abbreviateDateRanges', FALSE);
		$dash = '#DASH#';

		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);
		$endDate = self::BEGIN_DATE + (2 * 86400);
		$timeSpan->setEndDateAsUnixTimeStamp($endDate);

		self::assertEquals(
			strftime(self::DATE_FORMAT_YMD, self::BEGIN_DATE) . $dash . strftime(self::DATE_FORMAT_YMD, $endDate),
			$this->fixture->render($timeSpan, $dash)
		);
	}
}
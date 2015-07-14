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
class tx_seminars_ViewHelper_TimeRangeTest extends Tx_Phpunit_TestCase {
	/**
	 * some random date (2001-01-01 00:00:00)
	 *
	 * @var int
	 */
	const BEGIN_DATE = 978303600;

	/**
	 * @var string
	 */
	const TIME_FORMAT = '%H:%M';

	/**
	 * @var tx_seminars_ViewHelper_TimeRange
	 */
	private $subject = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	private $testingFramework = NULL;

	/**
	 * @var Tx_Oelib_Configuration
	 */
	private $configuration = NULL;

	/**
	 * @var Tx_Oelib_Translator
	 */
	private $translator = NULL;

	/**
	 * @var string
	 */
	private $translatedHours = '';

	protected function setUp() {
		$this->testingFramework	= new Tx_Oelib_TestingFramework('tx_seminars');

		$this->configuration = new Tx_Oelib_Configuration();
		$this->configuration->setAsString('timeFormat', self::TIME_FORMAT);

		Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

		$this->translator = Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars');
		$this->translatedHours = ' ' . $this->translator->translate('label_hours');

		$this->subject = new tx_seminars_ViewHelper_TimeRange();
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
			$this->subject->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginDateWithZeroHoursReturnsMessageWillBeAnnounced() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE);

		self::assertSame(
			$this->translator->translate('message_willBeAnnounced'),
			$this->subject->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginDateOnlyReturnsTimePortionOfBeginDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);

		self::assertSame(
			strftime(self::TIME_FORMAT, self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR) . $this->translatedHours,
			$this->subject->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithEqualBeginAndEndTimestampsReturnsOnlyTimePortionOfBeginDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);
		$timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);

		self::assertSame(
			strftime(self::TIME_FORMAT, self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR) . $this->translatedHours,
			$this->subject->render($timeSpan)
		);
	}

	/**
	 * @test
	 */
	public function renderWithTimeSpanWithBeginAndEndDateReturnsTimePortionsOfBeginDateAndEndDate() {
		$timeSpan = new tx_seminars_tests_fixtures_TestingTimeSpan();
		$timeSpan->setBeginDateAsUnixTimeStamp(self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR);
		$timeSpan->setEndDateAsUnixTimeStamp(self::BEGIN_DATE + 2 * tx_oelib_Time::SECONDS_PER_HOUR);

		self::assertSame(
			strftime(self::TIME_FORMAT, self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR) . '&#8211;' .
				strftime(self::TIME_FORMAT, self::BEGIN_DATE + 2 * tx_oelib_Time::SECONDS_PER_HOUR) . $this->translatedHours,
			$this->subject->render($timeSpan)
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

		self::assertSame(
			strftime(self::TIME_FORMAT, self::BEGIN_DATE + tx_oelib_Time::SECONDS_PER_HOUR) . $dash .
				strftime(self::TIME_FORMAT, self::BEGIN_DATE + 2 * tx_oelib_Time::SECONDS_PER_HOUR) . $this->translatedHours,
			$this->subject->render($timeSpan, $dash)
		);
	}
}
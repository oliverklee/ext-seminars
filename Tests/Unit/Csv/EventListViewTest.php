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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Csv_EventListViewTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Seminars_Csv_EventListView
	 */
	protected $subject = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	/**
	 * PID of the system folder in which we store our test data
	 *
	 * @var int
	 */
	protected $pageUid = 0;

	protected function setUp() {
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('seminars') . 'locallang_db.xml');
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('lang') . 'locallang_general.xml');

		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('plugin', new Tx_Oelib_Configuration());
		$this->configuration = new Tx_Oelib_Configuration();
		$this->configuration->setData(array('charsetForCsv' => 'utf-8'));
		$configurationRegistry->set('plugin.tx_seminars', $this->configuration);

		$this->subject = new Tx_Seminars_Csv_EventListView();
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
	}

	/**
	 * Retrieves the localization for the given locallang key and then strips the trailing colon from the localization.
	 *
	 * @param string $locallangKey
	 *        the locallang key with the localization to remove the trailing colon from, must not be empty and the localization
	 *        must have a trailing colon
	 *
	 * @return string locallang string with the removed trailing colon, will not be empty
	 */
	protected function localizeAndRemoveColon($locallangKey) {
		return rtrim($GLOBALS['LANG']->getLL($locallangKey), ':');
	}

	/**
	 * Creates a folder and an event record in that folder and returns the event UID.
	 *
	 * The PID and begin_date will be set automatically.
	 *
	 * @param array $eventData optional data for the event record
	 *
	 * @return int the UID of the created event record
	 */
	protected function createEventInFolderAndSetPageUid(array $eventData = array()) {
		$this->pageUid = $this->testingFramework->createSystemFolder();
		$this->subject->setPageUid($this->pageUid);

		$eventData['pid'] = $this->pageUid;
		$eventData['begin_date'] = $GLOBALS['SIM_EXEC_TIME'];

		return $this->testingFramework->createRecord('tx_seminars_seminars', $eventData);
	}

	/**
	 * @test
	 */
	public function setPageUidWithPositivePageUidNotThrowsException() {
		$this->subject->setPageUid($this->testingFramework->createSystemFolder());
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setPageUidWithZeroPageUidThrowsException() {
		$this->subject->setPageUid(0);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setPageUidWithNegativePageUidThrowsException() {
		$this->subject->setPageUid(-1);
	}

	/**
	 * @test
	 */
	public function renderIsEmptyForNoPageUid() {
		self::assertSame(
			'',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForZeroRecordsAndSeparatorDisabledReturnsOnlyHeader() {
		$pageUid = $this->testingFramework->createSystemFolder();
		$this->subject->setPageUid($pageUid);

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'uid,title');
		$this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', FALSE);

		self::assertSame(
			$this->localizeAndRemoveColon('tx_seminars_seminars.uid') . ';' .
				$this->localizeAndRemoveColon('tx_seminars_seminars.title') . CRLF,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForZeroRecordsAndSeparatorEnabledReturnsOnlySeparatorSpecificationAndHeader() {
		$pageUid = $this->testingFramework->createSystemFolder();
		$this->subject->setPageUid($pageUid);

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'uid,title');
		$this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', TRUE);

		self::assertSame(
			'sep=;' . CRLF .
				$this->localizeAndRemoveColon('tx_seminars_seminars.uid') . ';' .
				$this->localizeAndRemoveColon('tx_seminars_seminars.title') . CRLF,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderCanContainOneEventUid() {
		$eventUid = $this->createEventInFolderAndSetPageUid();

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

		self::assertContains(
			(string) $eventUid,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderCanContainEventFromSubFolder() {
		$pageUid = $this->testingFramework->createSystemFolder();
		$this->subject->setPageUid($pageUid);

		$subFolderPid = $this->testingFramework->createSystemFolder($pageUid);
		$this->testingFramework->createRecord('tx_seminars_seminars', array('pid' => $subFolderPid, 'title' => 'another event'));

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

		self::assertContains(
			'another event',
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderCanContainTwoEventUids() {
		$this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

		$firstEventUid = $this->createEventInFolderAndSetPageUid();
		$secondEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('pid' => $this->pageUid, 'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600)
		);

		$eventList = $this->subject->render($this->pageUid);

		self::assertContains(
			(string) $firstEventUid,
			$eventList
		);
		self::assertContains(
			(string) $secondEventUid,
			$eventList
		);
	}

	/**
	 * @test
	 */
	public function renderSeparatesLinesWithCarriageReturnsAndLineFeeds() {
		$this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

		$firstEventUid = $this->createEventInFolderAndSetPageUid();
		$secondEventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars', array('pid' => $this->pageUid, 'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600)
		);

		self::assertContains(
			$this->localizeAndRemoveColon('tx_seminars_seminars.uid') . CRLF . $firstEventUid . CRLF . $secondEventUid . CRLF,
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderHasResultEndingWithCarriageReturnAndLineFeed() {
		$this->configuration->setAsString('fieldsFromEventsForCsv', 'uid');

		$this->createEventInFolderAndSetPageUid();
		$this->testingFramework->createRecord(
			'tx_seminars_seminars', array('pid' => $this->pageUid, 'begin_date' => $GLOBALS['SIM_EXEC_TIME'] - 3600)
		);

		self::assertRegExp(
			'/\r\n$/',
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderNotWrapsRegularValuesWithDoubleQuotes() {
		$this->createEventInFolderAndSetPageUid(array('title' => 'bar'));

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

		self::assertNotContains(
			'"bar"',
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderEscapesDoubleQuotes() {
		$this->createEventInFolderAndSetPageUid(array('description' => 'foo " bar'));

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'description');

		self::assertContains(
			'foo "" bar',
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderWrapsValuesWithLineFeedsInDoubleQuotes() {
		$this->createEventInFolderAndSetPageUid(array('title' => 'foo' . LF . 'bar'));

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

		self::assertContains(
			'"foo' . LF . 'bar"',
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderWrapsValuesWithDoubleQuotesInDoubleQuotes() {
		$this->createEventInFolderAndSetPageUid(array('title' => 'foo " bar'));

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

		self::assertContains(
			'"foo "" bar"',
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderWrapsValuesWithSemicolonsInDoubleQuotes() {
		$this->createEventInFolderAndSetPageUid(array('title' => 'foo ; bar'));

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'title');

		self::assertContains(
			'"foo ; bar"',
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderSeparatesValuesWithSemicolons() {
		$this->createEventInFolderAndSetPageUid(array('description' => 'foo', 'title' => 'bar'));

		$this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');

		self::assertContains(
			'foo;bar',
			$this->subject->render($this->pageUid)
		);
	}

	/**
	 * @test
	 */
	public function renderNotWrapsHeadlineFieldsInDoubleQuotes() {
		$this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');
		$this->createEventInFolderAndSetPageUid();

		$eventList = $this->subject->render($this->pageUid);
		$description = $this->localizeAndRemoveColon('tx_seminars_seminars.description');

		self::assertContains(
			$description,
			$eventList
		);
		self::assertNotContains(
			'"' . $description . '"',
			$eventList
		);
	}

	/**
	 * @test
	 */
	public function renderSeparatesHeadlineFieldsWithSemicolons() {
		$this->configuration->setAsString('fieldsFromEventsForCsv', 'description,title');
		$this->createEventInFolderAndSetPageUid();

		self::assertContains(
			$this->localizeAndRemoveColon('tx_seminars_seminars.description') .
			';' . $this->localizeAndRemoveColon('tx_seminars_seminars.title'),
			$this->subject->render($this->pageUid)
		);
	}
}